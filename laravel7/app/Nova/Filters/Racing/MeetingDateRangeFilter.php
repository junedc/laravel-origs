<?php

namespace App\Nova\Filters\Racing;

use Ampeco\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Class MeetingDateRangeFilter
 *
 * @package App\Nova\Filters\Racing
 */
class MeetingDateRangeFilter extends DateRangeFilter
{
    /**
     * Filter name
     *
     * @var string $name
     */
    public $name = 'Date Filter';

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
        if (isset($value[1])) {
            $query->whereBetween('date', $value);
        } else {
            $query->where('date', '>=', $value);
        }

        return $query;
    }

    /**
     * Default filter
     *
     * @return array|string
     */
    public function default()
    {
        $today = Carbon::now()
            ->toDateString();

        return [$today];
    }
}
