<?php

namespace App\Nova\Filters\Sport;

use App\Repositories\CompetitionsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class EventCompetitionFilter
 *
 * @package App\Nova\Filters\Sport
 */
class EventCompetitionFilter extends Filter
{
    /**
     * @var string $name
     */
    public $name = 'Competition Filter';

    /**
     * @var CompetitionsRepository $competitionsRepository
     */
    protected $competitionsRepository;

    /**
     * EventCompetitionFilter constructor.
     *
     * @param CompetitionsRepository $competitionsRepository
     */
    public function __construct(CompetitionsRepository $competitionsRepository)
    {
        $this->competitionsRepository = $competitionsRepository;
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
        if ($value) {
            $query->where('competition_id', $value);
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
        $competitions = $this->competitionsRepository
            ->getAllCompetitions(['id', 'name']);

        foreach ($competitions as $competition) {
            $options[$competition['name']] = $competition['id'];
        }

        return $options;
    }
}
