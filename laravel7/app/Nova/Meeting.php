<?php

namespace App\Nova;

use App\Entities\FeedType;
use App\Nova\Actions\Racing\MeetingPushAction;
use App\Nova\Filters\Racing\MeetingCountryFilter;
use App\Nova\Filters\Racing\MeetingDateRangeFilter;
use App\Nova\Filters\Racing\RaceTypeFilter;
use App\Nova\Tools\ResourceTools;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

/**
 * Class Meeting
 *
 * @package App\Nova
 */
class Meeting extends Resource
{
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
    public static $model = \App\Models\Racing\Meeting::class;

    /**
     * Default order for the resource
     *
     * @var array $orderBy
     */
    public static $orderBy = [
        'date' => 'asc'
    ];

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
        'external_id',
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['track', 'raceType', 'races'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $resourceId = $request->resourceId;
        $viaRelationship = $request->viaRelationship;
        $validateParam = $resourceId ? ',' . $resourceId : '';
        $allowImport = !$resourceId || $viaRelationship ? false : $this->track->active;

        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->rules('required', 'unique:racing_tracks,name' . $validateParam)
                ->sortable(),

            Text::make('External ID', 'external_id')
                ->readonly(),

            Text::make('Race Type', 'raceType', function ($raceType) {
                return $raceType->name;
            })
                ->readonly(),

            BelongsTo::make('Track', 'track', Track::class)
                ->exceptOnForms(),

            Text::make('Track', 'track', function () {
                return $this->track->name;
            })
                ->readonly()
                ->onlyOnForms(),

            Text::make('Country', function () {
                return $this->track->country;
            }),

            Text::make('State', function () {
                return $this->track->state;
            }),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route(
                        'racing.transformer.viewer',
                        [
                            'resource' => 'meeting',
                            'resourceId' => $this->id
                        ]
                    )
                ])->render();
            })
                ->onlyOnDetail()
                ->asHtml(),

            HasMany::make('Races', 'races', Race::class),

            ResourceTools::make()
                ->build('feed_manager', [
                    'provider' => FeedType::GBS,
                    'feedTypes' => [
                        FeedType::ACCEPTANCES,
                        FeedType::FORM,
                        FeedType::DERIVATIVES
                    ],
                    'firstLevel' => $this->externalId ?? null,
                    'allowImport' => $allowImport
                ])
                ->isViaRelationship($request->get('viaRelationship')),

            Date::make('Date')
                ->format('DD MMM YYYY')
                ->sortable(),
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
            resolve(MeetingDateRangeFilter::class),
            resolve(RaceTypeFilter::class),
            resolve(MeetingCountryFilter::class)
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
            (resolve(MeetingPushAction::class))->canRun(function () {
                return true;
            }),
        ];
    }
}
