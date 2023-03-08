<?php

namespace App\Nova;

use App\Nova\Actions\Sport\CompetitionImportAction;
use App\Nova\Filters\Sport\CompetitionGenderFilter;
use App\Nova\Filters\Sport\CompetitionSportFilter;
use App\Repositories\MySQL\ProviderSportsRepository;
use App\Utilities\Arr;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Competition
 *
 * @package App\Nova
 */
class Competition extends Resource
{
    use SearchesRelations;

    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Sports';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Sport\Competition::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'jurisdiction',
        'tournament',
        'gender'
    ];

    /**
     * Determine if relations should be searched globally.
     *
     * @var array
     */
    public static $searchRelationsGlobally = false;

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'sport' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['sport', 'events'];

    /**
     * Default order for the resource
     *
     * @var array $orderBy
     */
    public static $orderBy = [
        'name' => 'asc'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        // If update and the value is un-touched
        // add another qualification for uniqueness
        $resourceId = $request->resourceId;
        $validateParam = $resourceId ? ',' . $resourceId : '';

        return [
            ID::make('Competition ID', 'id')
                ->sortable(),

            Text::make('Competition Name', 'name')
                ->rules('required', 'unique:sport_competitions,name' . $validateParam)
                ->sortable(),

            BelongsTo::make('Sport', 'sport', Sport::class),

            Text::make('Jurisdiction', 'jurisdiction'),

            Text::make('Gender', 'gender')
                ->sortable(),

            BelongsTo::make('Provider', 'provider', Provider::class)
                ->onlyOnDetail()
                ->viewable(false),

            Text::make('Ext ID', 'external_id')
                ->onlyOnDetail(),

            new Panel('SportCast Competition Mapping', $this->sportCastFields($request)),

            HasMany::make('Events', 'events', SportEvent::class),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function sportCastFields(Request $request): array
    {

        if ($request instanceof ResourceIndexRequest) {
            //page is inside indexRequest
            return [];
        }

        if ($this->resource['provider_id'] != \App\Models\Provider::SPORTING_SOLUTIONS) {
            //hide the panel area if event is not from sporting solutions
            return [];
        }

        if (strpos($_SERVER['REQUEST_URI'], '/nova-api/sport-events') !== false) {
            //currently in sport events
            return [];
        }

        $competitionId = null;
        $competitionName = null;

        $providerSportRepository = resolve(ProviderSportsRepository::class);
        $providerCompetition = $providerSportRepository->getProviderSportCompetition(\App\Models\Provider::SPORT_CAST, $this->resource->id);
        if ($providerCompetition) {
            $competitionId = $providerCompetition->externalId;
            $competitionName = $providerCompetition->name;
        }

        $providerCompetitions = $providerSportRepository->getProviderSportCompetitions(\App\Models\Provider::SPORT_CAST, null, $this->resource['sport_id']);
        $options = Arr::pluck($providerCompetitions, 'name', 'external_id');
        asort($options);

        return [
            Text::make('Competition Name', 'sport_provider_competition.name')
                ->withMeta(["value" => $competitionName])
                ->hideFromIndex()
                ->hideWhenUpdating(),

            Select::make('Competition Name', 'sport_provider_competition.competition_id')
                ->options($options)
                ->withMeta(["value" => $competitionId])
                ->onlyOnForms()
                ->readonly(isset($competitionId)),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            resolve(CompetitionSportFilter::class),
            resolve(CompetitionGenderFilter::class)
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            resolve(CompetitionImportAction::class),
        ];
    }

    /**
     * Redirect to model details page
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    /**
     * Return to view model after updating
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {

        $competitionId = $request->get('sport_provider_competition_competition_id');
        $sportId = $request->get('sport');
        if ($competitionId) {
            $providerSportRepository = new ProviderSportsRepository();
            $providerSportRepository->updateProviderCompetition(
                [
                    "providerId" => \App\Models\Provider::SPORT_CAST,
                    "competitionId" => $resource->getKey(),
                    "sportId" => $sportId,
                    "externalId" => $competitionId
                ]

            );
        }

        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }
}
