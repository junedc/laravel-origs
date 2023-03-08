<?php

namespace App\Nova\Filters\Sport;

use App\Domain\Cache\AppDataRetriever;
use App\Utilities\EloquentExtension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class EventSportFilter
 *
 * @package App\Nova\Filters\Sport
 */
class EventSportFilter extends Filter
{
    use EloquentExtension;

    /**
     * @var string $name
     */
    public $name = 'Sport Filter';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * @var AppDataRetriever $appDataRetriever
     */
    protected $appDataRetriever;

    /**
     * PushingJobSportFilter constructor.
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
        if ($value) {
            if (!$this->isJoined($query, 'sport_competitions')) {
                $query->select('sport_events.*', 'sport_competitions.id as cid');
                $query->join('sport_competitions', 'sport_competitions.id', '=', 'competition_id');
            }

            $query->where('sport_competitions.sport_id', '=', $value);
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
            $options[$sport['name']] = (int) $sport['id'];
        }

        return $options;
    }
}
