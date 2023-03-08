<?php

namespace App\Nova;

use App\Entities\Role;
use App\Events\SportUpdated;
use App\Nova\Cards\SportEventImport;
use App\Nova\Filters\Sport\SportStatusFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Sport
 *
 * @package App\Nova
 */
class Sport extends Resource
{
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
    public static $model = \App\Models\Sport\Sport::class;

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
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['competitions', 'marketTypes'];

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
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $resourceId = $request->resourceId;
        $validateParam = $resourceId ? ',' . $resourceId : '';

        return [
            ID::make('Sport ID', 'id')
                ->sortable(),

            Text::make('Sport Name', 'name')
                ->rules('unique:sports,name' . $validateParam)
                ->sortable(),

            Text::make('Ext ID', 'external_id'),

            Boolean::make('Active', 'active'),

            HasMany::make('Competitions', 'competitions', Competition::class),
            HasMany::make('Market Types', 'marketTypes', MarketType::class),

            Text::make('Actions', function () {
                return view('nova::partials.button-link', [
                    'name' => 'Push parent markets',
                    'url' => route('sports.push_parent_markets', ['id' => $this->id])
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
        return [
            (resolve(SportEventImport::class))
                ->setSport((int) $request->resourceId)
                ->canSee(function () use ($request) {
                    return $request->user()->hasRole(Role::SUPER_ADMIN);
                })
        ];
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
            resolve(SportStatusFilter::class)
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
        return [];
    }

    /**
     * Redirect to index after model creation
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        event(new SportUpdated());
        return '/resources/' . static::uriKey();
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
        event(new SportUpdated());
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }
}
