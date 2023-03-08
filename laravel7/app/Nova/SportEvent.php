<?php

namespace App\Nova;

use App\Entities\FeedType;
use App\Nova\Actions\Sport\EventImportAction;
use App\Nova\Actions\Sport\EventPushAction;
use App\Nova\Actions\Sport\SportCastEventImportAction;
use App\Nova\Filters\Sport\EventCompetitionFilter;
use App\Nova\Filters\Sport\EventDateRangeFilter;
use App\Nova\Filters\Sport\EventSportFilter;
use App\Nova\Filters\Sport\EventStatusFilter;
use App\Nova\Tools\ResourceTools;
use App\Repositories\MySQL\ProviderSportsRepository;
use App\Repositories\MySQL\SportEventsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use Laravel\Nova\Panel;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class SportEvent
 *
 * @package App\Nova
 */
class SportEvent extends Resource
{
    use SearchesRelations;

    /**
     * @var int
     */
    private int $startTimeMargin = 120;

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
    public static $model = \App\Models\Sport\SportEvent::class;

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
        'external_id'
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
        'status' => ['name'],
        'competition' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['status', 'competition', 'markets'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $resourceId = $request->resourceId;
        $validateParam = $resourceId ? ',' . $resourceId : '';

        $fields = [
            ID::make('ID', 'id')
                ->sortable(),

            Text::make('Name', 'name')
                ->creationRules('required', 'unique:sport_events,name' . $validateParam)
                ->sortable(),

            BelongsTo::make('Status', 'status', EventStatus::class)
                ->viewable(false),

            DateTime::make('Start Time', 'start_time')
                ->format('DD MMM YYYY H:mm a')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('Competition', 'competition', Competition::class),

            Text::make('Round', 'round'),

            Text::make('External ID', 'external_id')
                ->onlyOnDetail(),

            DateTime::make('Last Modified', 'updated_at')
                ->format('DD MMM YYYY H:mm a')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->onlyOnDetail(),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route('pushing_data.event', ['id' => $this->id])
                ])
                    ->render();
            })
                ->onlyOnDetail()
                ->asHtml(),

            new Panel('SportCast Event Mapping',$this->sportCastFields($request)),

            HasMany::make('Markets', 'markets', Market::class),
        ];

        if ($this->resource->providerId == \App\Models\Provider::SPORTING_SOLUTIONS) {
            $fields[] = ResourceTools::make()
                ->build('feed_manager', [
                    'provider' => FeedType::SSLN,
                    'feedTypes' => [
                        FeedType::SSLN_SNAPSHOT,
                        FeedType::SSLN_STREAM
                    ],
                    'firstLevel' => $this->externalId
                ])
                ->isViaRelationship($request->get('viaRelationship'));

        } elseif ($this->resource->providerId == \App\Models\Provider::LSPORTS) {

            $fields[] = ResourceTools::make()
                ->build('feed_manager', [
                    'provider' => FeedType::LSPORTS,
                    'feedTypes' => [
                        FeedType::LSPORTS_STREAM
                    ],
                    'firstLevel' => $this->externalId
                ])
                ->isViaRelationship($request->get('viaRelationship'));
        }

        return $fields;
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

        if ($this->resource['provider_id'] != \App\Models\Provider::SPORTING_SOLUTIONS){
            //hide the panel area if event is not from sporting solutions
            return [];
        }

        $sportEventExternalId = null;
        $eventName = null;
        $options = [];

        $providerSportRepository = resolve(ProviderSportsRepository::class);

        //get the current providerEvent
        $providerEvent = $providerSportRepository->getProviderEventByEventId($this->resource->id);
        if ($providerEvent) {
            $sportEventExternalId = (string)$providerEvent->externalId;
            $eventName = $providerEvent->name;

            //add the current value to the dropdown
            $options[$providerEvent->externalId] = $providerEvent->name . ' (' . Carbon::createFromTimeString($providerEvent->start_time)->tz(config('app.local_timezone'))->toDateTimeString() . ')';
        }

        $competition = $this->resource["competition"];
        $startTime = Carbon::parse($this->resource["start_time"])
            ->subMinutes($this->startTimeMargin)
            ->toDateTimeString();

        $endTime = Carbon::parse($this->resource["start_time"])
            ->addMinutes($this->startTimeMargin)
            ->toDateTimeString();

        if ($competition) {
            $providerEvents = $providerSportRepository->getProviderEvents($competition->id, [$startTime, $endTime]);
            foreach ($providerEvents as $providerEvent) {
                $externalId = $providerEvent->external_id;
                $options[$externalId] = $providerEvent->name . ' (' . Carbon::createFromTimeString($providerEvent->start_time)->tz(config('app.local_timezone'))->toDateTimeString() . ')';
            }
        }

        return [
            Select::make('Game', 'sport_provider_event.external_id')
                ->options($options)
                ->withMeta(["value" => $sportEventExternalId])
                ->onlyOnForms()
                ->readonly(isset($eventName)),

            Text::make('Game', 'sport_provider_event.external_id')
                ->withMeta(["value" => $eventName])
                ->hideFromIndex()
                ->hideWhenUpdating()
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
            resolve(EventDateRangeFilter::class),
            resolve(EventSportFilter::class),
            resolve(EventCompetitionFilter::class),
            resolve(EventStatusFilter::class)
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
        if (config('sportcast.enabled') && $request->get('resourceId')) {
            $sportEventsRepository = resolve(SportEventsRepository::class);
            $sportEvent = $sportEventsRepository->getById($request->get('resourceId'));
            if ($sportEvent->providerId == \App\Models\Provider::SPORTING_SOLUTIONS) {
                return [
                    resolve(EventPushAction::class),
                    resolve(EventImportAction::class),
                    resolve(SportCastEventImportAction::class),
                ];
            }
        }

        return [
            resolve(EventPushAction::class),
            resolve(EventImportAction::class)
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
        $externalId = $request->get('sport_provider_event_external_id');
        if ($externalId) {
            $providerSportRepository = new ProviderSportsRepository();
            $providerSportRepository->updateProviderEvent(
                [
                    "providerId" => \App\Models\Provider::SPORT_CAST,
                    "eventId" => $resource->getKey(),
                    "externalId" => $externalId
                ]
            );
        }

        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    /**
     * Format linked events data
     *
     * @param Collection $linkedEvents
     * @return array
     */
    private function formatLinkedEvents(Collection $linkedEvents)
    {
        $data = [];

        foreach ($linkedEvents as $linkedEvent) {
            $data['Events'][] = [
                'name' => $linkedEvent->name
            ];
        }

        return $data;
    }
}
