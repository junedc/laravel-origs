<?php

namespace App\Nova\Filters\Racing;

use App\Domain\Cache\RacingDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class RaceResultBetTypeFilter
 *
 * @package App\Nova\Filters\Racing
 */
class RaceResultBetTypeFilter extends BooleanFilter
{
    /**
     * @var RacingDataRetriever $racingDataRetriever
     */
    protected $racingDataRetriever;

    /**
     * RaceTypeFilter constructor.
     *
     * @param RacingDataRetriever $racingDataRetriever
     */
    public function __construct(RacingDataRetriever $racingDataRetriever)
    {
        $this->racingDataRetriever = $racingDataRetriever;
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
            $query->whereIn('bet_type_id', array_keys($value));
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
        $types = $this->racingDataRetriever
            ->getBetTypes();

        foreach ($types as $type) {
            $options[$type['name']] = $type['id'];
        }

        return $options;
    }
}
