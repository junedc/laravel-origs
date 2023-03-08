<?php

namespace App\Nova\Filters;

use App\Domain\Cache\AppDataRetriever;
use App\Utilities\EloquentExtension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class PushingJobSubscriberFilter
 *
 * @package App\Nova\Filters
 */
class PushingJobSubscriberFilter extends BooleanFilter
{
    use EloquentExtension;

    /**
     * @var string $name
     */
    public $name = 'Subscriber Filter';

    /**
     * @var AppDataRetriever $appDataRetriever
     */
    protected $appDataRetriever;

    /**
     * SubscriptionSubscriberFilter constructor.
     *
     * @param AppDataRetriever $appDataRetriever
     */
    public function __construct(AppDataRetriever $appDataRetriever)
    {
        $this->appDataRetriever = $appDataRetriever;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  Request  $request
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $value = collect($value)->filter(function ($value) {
            return $value == 1;
        })->toArray();

        if ($value) {
            if (!$this->isJoined($query, 'subscriptions')) {
                $query->select('pushing_jobs.*', 'subscriptions.id as sid');
                $query->join('subscriptions', 'subscriptions.id', '=', 'subscription_id');
            }

            $query->whereIn('subscriptions.subscriber_id', array_keys($value));
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $options = [];
        $subscribers = $this->appDataRetriever
            ->getSubscribers();

        foreach ($subscribers as $subscriber) {
            $options[$subscriber['name']] = $subscriber['id'];
        }

        return $options;
    }
}
