<?php

namespace App\Nova;

use App\Nova\Filters\SubscriptionSportFilter;
use App\Nova\Filters\SubscriptionStatusFilter;
use App\Nova\Filters\SubscriptionSubscriberFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Subscription
 *
 * @package App\Nova
 */
class Subscription extends Resource
{
    use SearchesRelations;

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
    public static $model = \App\Models\Subscription::class;

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
        'subscriber' => ['name'],
        'sport' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['subscriber', 'sport'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Subscription ID', 'id')
                ->sortable(),

            BelongsTo::make('Subscriber', 'subscriber', Subscriber::class)
                ->readonly(function () {
                    return $this->resource->id ? true : false;
                })
                ->rules('required')
                ->sortable()
                ->viewable(false),

            BelongsTo::make('Sport', 'sport', Sport::class)
                ->rules('required')
                ->sortable()
                ->viewable(false),

            Boolean::make('Active', 'active')
                ->sortable(),

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
            resolve(SubscriptionSubscriberFilter::class),
            resolve(SubscriptionSportFilter::class),
            resolve(SubscriptionStatusFilter::class)
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

    /**
     * Custom validation for subscription creation
     * Prevent subscriber to subscribe to the same sport
     *
     * @param NovaRequest $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validatorForCreation(NovaRequest $request)
    {
        $messages = [
            'unique' => 'The subscriber already subscribed to this sport.'
        ];

        return Validator::make($request->all(), self::rulesForCreation($request), $messages)
            ->after(function ($validator) use ($request) {
                self::afterValidation($request, $validator);
                self::afterCreationValidation($request, $validator);
            });
    }
}
