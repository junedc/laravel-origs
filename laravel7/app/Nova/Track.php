<?php

namespace App\Nova;

use App\Nova\Filters\Racing\TrackCountryFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Track
 *
 * @package App\Nova
 */
class Track extends Resource
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
    public static $model = \App\Models\Racing\Track::class;

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
        'country',
        'state'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        // If update and the value is un-touched
        // add another qualification for uniqueness
        $resourceId = $request->resourceId;

        return [
            ID::make('Track ID', 'id')
                ->sortable(),

            Text::make('Track Name', 'name')
                ->rules('required')
                ->sortable(),

            Text::make('Country', 'country')
                ->readonly(function () use ($resourceId) {
                    return !empty($resourceId);
                })
                ->rules('required', 'regex:/^[a-zA-Z]{1,3}$/'),

            Text::make('State', 'state')
                ->readonly(function () use ($resourceId) {
                    return !empty($resourceId);
                })
                ->rules('required', 'regex:/^[a-zA-Z]{1,3}$/'),

            Boolean::make('Active', 'active'),
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
            resolve(TrackCountryFilter::class)
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
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }
}
