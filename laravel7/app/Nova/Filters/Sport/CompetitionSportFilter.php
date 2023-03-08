<?php

namespace App\Nova\Filters\Sport;

use App\Repositories\SportsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class CompetitionSportFilter
 *
 * @package App\Nova\Filters\Sport
 */
class CompetitionSportFilter extends BooleanFilter
{
    /**
     * @var string $name
     */
    public $name = 'Sport Filter';

    /**
     * @var SportsRepository $sportsRepository
     */
    protected $sportsRepository;

    /**
     * CompetitionSportFilter constructor.
     *
     * @param SportsRepository $sportsRepository
     */
    public function __construct(SportsRepository $sportsRepository)
    {
        $this->sportsRepository = $sportsRepository;
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
            $query->whereIn('sport_id', array_keys($value));
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
        $sports = $this->sportsRepository
            ->getAllSports(['id', 'name']);

        foreach ($sports as $sport) {
            $options[$sport['name']] = $sport['id'];
        }

        return $options;
    }
}
