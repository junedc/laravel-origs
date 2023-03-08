<?php

namespace App\Nova;

use App\Entities\FeedType;
use App\Nova\Actions\Racing\RacePushAction;
use App\Nova\Actions\Racing\RaceResultPushAction;
use App\Nova\Filters\Racing\RaceDateRangeFilter;
use App\Nova\Filters\Racing\RaceStatusFilter;
use App\Nova\Filters\Racing\RaceTypeFilter;
use App\Nova\Tools\ResourceTools;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Race
 *
 * @package App\Nova
 */
class Race extends Resource
{
    use SearchesRelations;

    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Racing';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Racing\Race::class;

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
        'name'
    ];

    /**
     * Default order for the resource
     *
     * @var array $orderBy
     */
    public static $orderBy = [
        'number' => 'asc'
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
        'meeting' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['meeting', 'raceType', 'raceStatus', 'runnerSelections', 'raceResults'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $meeting = $this->meeting;
        return [
            ID::make('Race ID', 'id')
                ->sortable(),

            Text::make('Race Name', 'name')
                ->resolveUsing(function ($race) {
                    return substr($race, 0, 50) . (strlen($race) >= 50 ? '...' : '');
                }),

            BelongsTo::make('Meeting')
                ->exceptOnForms(),
            Text::make('Meeting', 'meeting', function ($meeting) {
                return $meeting->name;
            })
                ->readonly()
                ->onlyOnForms(),

            Text::make('Race Type', 'raceType', function ($raceType) {
                return $raceType->name;
            })
                ->readonly(),

            DateTime::make('Start Time')
                ->format('DD MMM YYYY H:mm a'),

            Text::make('Race Status', 'raceStatus', function ($status) {
                return $status->name;
            })
                ->exceptOnForms()
                ->readonly(),
            BelongsTo::make('Race Status', 'raceStatus')
                ->onlyOnForms(),

            Number::make('Race No.', 'number')
                ->readonly(),

            Number::make('Runners', 'number_of_runners')
                ->readonly(),

            HasMany::make('Runner Selection', 'runnerSelections', RunnerSelection::class),

            ResourceTools::make()
                ->build('race_results', [
                    'raceId' => $request->resourceId,
                    'actions' => [
                        resolve(RaceResultPushAction::class)->canRun(function () {
                            return false;
                        }),
                    ],
                    'transformerLink' => route(
                        'racing.transformer.viewer',
                        ['resource' => 'results', 'resourceId' => $this->id ?? 0]
                    )
                ])
                ->isViaRelationship($request->get('viaRelationship')),

            ResourceTools::make()
                ->build('feed_manager', [
                    'provider' => FeedType::GBS,
                    'feedTypes' => [
                        FeedType::CLOSING,
                        FeedType::PRICES,
                        FeedType::RESULTS,
                        FeedType::SCRATCHINGS,
                        FeedType::SUBS
                    ],
                    'firstLevel' => $meeting->externalId ?? null,
                    'secondLevel' => $this->number,
                ])
                ->isViaRelationship($request->get('viaRelationship')),

            Number::make('Places', 'number_of_places'),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route(
                        'racing.transformer.viewer',
                        [
                            'resource' => 'race',
                            'resourceId' => $this->id
                        ]
                    )
                ])->render();
            })
                ->onlyOnDetail()
                ->asHtml(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            resolve(RaceDateRangeFilter::class),
            resolve(RaceTypeFilter::class),
            resolve(RaceStatusFilter::class)
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            resolve(RacePushAction::class)->canRun(function () {
                return true;
            }),
        ];
    }
}
