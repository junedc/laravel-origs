<?php

namespace App\Nova;

use App\Nova\Filters\PushingJobSportFilter;
use App\Nova\Filters\PushingJobSubscriberFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class PushingJob
 *
 * @package App\Nova
 */
class PushingJob extends Resource
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
    public static $model = \App\Models\PushingJob::class;

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
        'job_key'
    ];

    /**
     * @var array $orderBy
     */
    public static $orderBy = [
        'updated_at' => 'desc'
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
        'subscription.subscriber' => ['name'],
        'subscription.sport' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['subscription'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Push Job ID', 'id')
                ->sortable(),

            BelongsTo::make('Subscriber', 'subscription', Subscription::class)
                ->displayUsing(function ($subscription) {
                    return $subscription->subscriber->name;
                })
                ->viewable(false),

            BelongsTo::make('Sport', 'subscription', Subscription::class)
                ->displayUsing(function ($subscription) {
                    return $subscription->sport->name;
                })
                ->viewable(false),

            Text::make('Key', 'job_key')
                ->sortable(),

            Number::make('Successes', 'success_count')
                ->sortable(),

            DateTime::make('Last Success', 'last_success_at')
                ->format('DD MMM YYYY H:mm a')
                ->sortable(),

            Number::make('Failures', 'failure_count')
                ->sortable(),

            DateTime::make('Last Failure', 'last_failure_at')
                ->format('DD MMM YYYY H:mm a')
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
            resolve(PushingJobSubscriberFilter::class),
            resolve(PushingJobSportFilter::class)
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
