<?php

namespace App\Nova\Filters\Sport;

use App\Domain\Cache\SportDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class EventStatusFilter
 *
 * @package App\Nova\Filters\Sport
 */
class EventStatusFilter extends BooleanFilter
{
    /**
     * @var string $name
     */
    public $name = 'Status Filter';

    /**
     * @var SportDataRetriever $sportDataRetriever
     */
    protected $sportDataRetriever;

    /**
     * EventStatusFilter constructor.
     *
     * @param SportDataRetriever $sportDataRetriever
     */
    public function __construct(SportDataRetriever $sportDataRetriever)
    {
        $this->sportDataRetriever = $sportDataRetriever;
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
            $query->whereIn('status_id', array_keys($value));
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
        $statuses = $this->sportDataRetriever
            ->getEventStatuses();

        foreach ($statuses as $status) {
            $options[$status['name']] = $status['id'];
        }

        return $options;
    }
}
