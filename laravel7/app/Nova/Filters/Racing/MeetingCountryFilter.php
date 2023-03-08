<?php

namespace App\Nova\Filters\Racing;

use App\Domain\Cache\AppDataRetriever;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class MeetingCountryFilter
 *
 * @package App\Nova\Filters\Racing
 */
class MeetingCountryFilter extends Filter
{
    /**
     * @var string $name
     */
    public $name = 'Country Filter';

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
            $query->select('racing_meetings.*', 'tracks.id AS tid', 'tracks.name', 'tracks.country', 'tracks.state')
                ->join('racing_tracks AS tracks', 'tracks.id', '=', 'track_id')
                ->where('tracks.country', '=', $value);
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
        $countries = $this->appDataRetriever
            ->getTrackCountries();

        foreach ($countries as $country) {
            $options[$country['id']] = $country['name'];
        }

        return $options;
    }
}
