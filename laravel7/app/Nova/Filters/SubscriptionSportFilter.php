<?php

namespace App\Nova\Filters;

use App\Domain\Cache\AppDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

/**
 * Class SubscriptionSportFilter
 *
 * @package App\Nova\Filters
 */
class SubscriptionSportFilter extends BooleanFilter
{
    /**
     * @var string $name
     */
    public $name = 'Sport Filter';

    /**
     * @var AppDataRetriever $appDataRetriever
     */
    protected $appDataRetriever;

    /**
     * CompetitionSportFilter constructor.
     *
     * @param AppDataRetriever $appDataRetriever
     */
    public function __construct(AppDataRetriever $appDataRetriever)
    {
        $this->appDataRetriever = $appDataRetriever;
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
        $sports = $this->appDataRetriever
            ->getSports();

        foreach ($sports as $sport) {
            $options[$sport['name']] = $sport['id'];
        }

        return $options;
    }
}
