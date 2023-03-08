<?php

namespace App\Nova\Filters\Sport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class ParticipantTypeFilter
 *
 * @package App\Nova\Filters\Sport
 */
class ParticipantTypeFilter extends Filter
{
    /**
     * @var string $name
     */
    public $name = 'Type';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

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
            $query->where('is_team', $value);
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
            'Individual' => 0,
            'Team' => 1
        ];
    }
}
