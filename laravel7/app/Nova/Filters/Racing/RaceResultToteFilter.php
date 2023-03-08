<?php

namespace App\Nova\Filters\Racing;

use App\Domain\Cache\RacingDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class RaceResultToteFilter
 *
 * @package App\Nova\Filters\Racing
 */
class RaceResultToteFilter extends BooleanFilter
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
            $query->whereIn('tote_id', array_keys($value));
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
        $totes = $this->racingDataRetriever
            ->getTotes();

        foreach ($totes as $tote) {
            $options[$tote['name']] = $tote['id'];
        }

        return $options;
    }
}
