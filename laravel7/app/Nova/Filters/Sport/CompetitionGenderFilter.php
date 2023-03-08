<?php

namespace App\Nova\Filters\Sport;

use App\Repositories\CompetitionsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class CompetitionGenderFilter
 *
 * @package App\Nova\Filters\Sport
 */
class CompetitionGenderFilter extends BooleanFilter
{
    /**
     * @var string $name
     */
    public $name = 'Gender Filter';

    /**
     * @var string $field
     */
    protected $field = 'gender';

    /**
     * @var CompetitionsRepository $competitionsRepository
     */
    protected $competitionsRepository;

    /**
     * CompetitionGender constructor.
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
        $value = collect($value)->filter(function ($value) {
            return $value == 1;
        })->toArray();

        if ($value) {
            $query->whereIn($this->field, array_keys($value));
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
        $values = $this->competitionsRepository
            ->getFieldDistinctValue($this->field);

        foreach ($values as $value) {
            $gender = $value[$this->field];
            $options[$gender] = $gender;
        }

        return $options;
    }
}
