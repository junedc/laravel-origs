<?php

namespace App\Nova\Filters\Racing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class RaceResultStatusFilter
 *
 * @package App\Nova\Filters\Racing
 */
class RaceResultStatusFilter extends Filter
{
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
            $query->where('is_final', $value);
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
        return [
            'Interim' => 0,
            'Final' => 1
        ];
    }
}
