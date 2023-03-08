<?php

namespace App\Nova;

use App\Events\SubscriberUpdated;
use App\Nova\Filters\SubscriberStatusFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Subscriber
 *
 * @package App\Nova
 */
class Subscriber extends Resource
{
    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Subscription';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Subscriber::class;

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
    public static $with = ['subscriptions'];

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
        $validateParam = $resourceId ? ',' . $resourceId : '';

        return [
            ID::make('Subscriber ID', 'id')
                ->sortable(),

            Text::make('Subscriber Name', 'name')
                ->rules('required', 'unique:subscribers,name' . $validateParam)
                ->sortable(),

            Text::make('Endpoint', 'endpoint')
                ->hideFromIndex()
                ->hideFromDetail()
                ->rules('required', 'url'),

            Text::make('Endpoint', 'endpoint', function () {
                return '<a target="_blank" href="' . $this->endpoint . '">' . $this->endpoint . '</a>';
            })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->asHtml(),

            Boolean::make('Active', 'active')
                ->sortable(),

            HasMany::make('Subscriptions', 'subscriptions', Subscription::class),

            DateTime::make('Last Updated', 'updated_at')
                ->format('DD MMM YYYY H:mm a')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable()
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
            resolve(SubscriberStatusFilter::class)
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
        event(new SubscriberUpdated());
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
        event(new SubscriberUpdated());
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }
}
