<?php

namespace App\Nova;

use App\Nova\Filters\PushingJobSportFilter;
use App\Nova\Filters\PushingJobSubscriberFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class PushingJobError
 *
 * @package App\Nova
 */
class PushingJobError extends Resource
{
    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Subscription';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\PushingJobError::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'pushing_job_id',
        'message'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID', 'id')
                ->sortable(),

            Text::make('Pushing Job ID', 'pushing_job_id'),

            Text::make('Job Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route(
                        'pushing.job.error.data',
                        ['resourceId' => $this->id]
                    )
                ])->render();
            })
                ->showOnDetail()
                ->showOnIndex()
                ->asHtml(),

            Text::make('Message', 'message'),

            DateTime::make('Created AT', 'created_at')
                ->format('DD MMM YYYY H:mm a')
                ->sortable()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
