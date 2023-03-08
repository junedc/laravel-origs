<?php

namespace App\Nova\Filters\Racing;

use Ampeco\Filters\DateRangeFilter;
use App\Repositories\MeetingsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Class MeetingDateRangeFilter
 *
 * @package App\Nova\Filters\Racing
 */
class RaceDateRangeFilter extends DateRangeFilter
{
    /**
     * Filter name
     *
     * @var string $name
     */
    public $name = 'Date Filter';

    /**
     * @var MeetingsRepository $meetingsRepository
     */
    protected $meetingsRepository;

    /**
     * RaceDateRangeFilter constructor.
     *
     * @param MeetingsRepository $meetingsRepository
     */
    public function __construct(MeetingsRepository $meetingsRepository)
    {
        $this->meetingsRepository = $meetingsRepository;
        parent::__construct();
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
        if (isset($value[1])) {
            return $query->whereBetween('start_time', $value);
        }

        if ($request->get('viaResource') == 'meetings') {
            $meeting = $this->meetingsRepository
                ->getById($request->get('viaResourceId'));

            $value = $meeting->date;
        }

        return $query->where('start_time', '>=', $value);
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
