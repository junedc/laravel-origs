<?php

namespace App\Nova\Filters\Sport;

use Ampeco\Filters\DateRangeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Nova;

/**
 * Class EventDateRangeFilter
 *
 * @package App\Nova\Filters\Sport
 */
class EventDateRangeFilter extends DateRangeFilter
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
        if ($request->get('viaResource') == 'competitions') {
            return  $query;
        }

        if (!isset($value[1])) {
            return $query->where('start_time', '>=', $value);
        }

        $start = Carbon::parse($value[0])
            ->startOfDay();

        $end = Carbon::parse($value[1] ?? $value[0])
            ->endOfDay();


//        $userTimeZone = Nova::userTimezone(function (Request $request, $userTimezone) {
//            return $request->user()->timezone;
//        });
//
//        Log::info('timer is ');
//        Log::info($userTimeZone);

        $start = Carbon::createFromFormat('Y-m-d H:i:s', $start->toDateTimeString(),  config('app.local_timezone'));
        $start->setTimezone('UTC');

        $end = Carbon::createFromFormat('Y-m-d H:i:s', $end->toDateTimeString(),  config('app.local_timezone'));
        $end->setTimezone('UTC');

        return $query->whereBetween('start_time', [$start->toDateTimeString(), $end->toDateTimeString()]);
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
