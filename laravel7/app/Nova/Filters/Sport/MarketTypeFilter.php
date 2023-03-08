<?php

namespace App\Nova\Filters\Sport;

use App\Domain\Cache\SportDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class MarketTypeFilter
 *
 * @package App\Nova\Filters\Sport
 */
class MarketTypeFilter extends BooleanFilter
{
    /**
     * @var string $name
     */
    public $name = 'Type Filter';

    /**
     * @var SportDataRetriever $sportDataRetriever
     */
    protected $sportDataRetriever;

    /**
     * MarketTypeFilter constructor.
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
            $query->whereIn('type_id', array_keys($value));
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
        $types = $this->sportDataRetriever
            ->getMarketTypes();

        foreach ($types as $type) {
            $options[$type['name']] = $type['id'];
        }

        return $options;
    }
}
