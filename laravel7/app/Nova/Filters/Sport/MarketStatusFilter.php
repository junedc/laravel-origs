<?php

namespace App\Nova\Filters\Sport;

use App\Domain\Cache\SportDataRetriever;
use App\Models\Sport\MarketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class MarketStatusFilter
 *
 * @package App\Nova\Filters\Sport
 */
class MarketStatusFilter extends Filter
{
    /**
     * @var string $name
     */
    public $name = 'Status Filter';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * @var SportDataRetriever $sportDataRetriver
     */
    protected $sportDataRetriever;

    /**
     * MarketStatusFilter constructor.
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
        if (!is_null($value)) {
            $query->where('status_id', $value);
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
        $filters = MarketStatus::allIds();

        $statuses = $this->sportDataRetriever
            ->getMarketStatuses();

        foreach ($statuses as $status) {
            if (in_array($status['id'], $filters)) {
                $options[$status['name']] = (int) $status['id'];
            }
        }

        return $options;
    }
}
