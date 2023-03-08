<?php

namespace App\Nova\Filters\Sport;

use App\Domain\Cache\SportDataRetriever;
use App\Utilities\EloquentExtension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class MarketRollingTypeFilter
 *
 * @package App\Nova\Filters\Sport
 */
class MarketRollingTypeFilter extends Filter
{
    use EloquentExtension;

    /**
     * @var string $name
     */
    public $name = 'Rolling Type Filter';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * @var SportDataRetriever $sportDataRetriever
     */
    protected $sportDataRetriever;

    /**
     * MarketEventFilter constructor.
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
            if (!$this->isJoined($query, 'sport_market_types')) {
                $query->select('sport_markets.*', 'sport_market_types.id as mtid');
                $query->join('sport_market_types', 'sport_market_types.id', '=', 'type_id');
            }

            $query->where('sport_market_types.rolling_type_id', $value);
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
        $rollingTypes = $this->sportDataRetriever
            ->getRollingTypes();

        foreach ($rollingTypes as $rollingType) {
            $options[$rollingType['name']] = (int) $rollingType['id'];
        }

        return $options;
    }
}
