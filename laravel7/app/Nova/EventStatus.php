<?php

namespace App\Nova;

use App\Events\Sport\EventStatusUpdated;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class EventStatus
 *
 * @package App\Nova
 */
class EventStatus extends Resource
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
    public static $model = \App\Models\Sport\EventStatus::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Hide this resource into sidebar
     *
     * @var bool $displayInNavigation
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [];
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
        return [];
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
        event(new EventStatusUpdated());
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
        event(new EventStatusUpdated());
        return '/resources/' . static::uriKey();
    }
}
