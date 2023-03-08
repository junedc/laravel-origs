<?php

namespace App\Nova;

use App\Nova\Actions\Racing\RaceResultPushAction;
use App\Nova\Filters\Racing\RaceResultBetTypeFilter;
use App\Nova\Filters\Racing\RaceResultStatusFilter;
use App\Nova\Filters\Racing\RaceResultToteFilter;
use App\Repositories\RunnersRepository;
use App\Utilities\LocalCache;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class RaceResult
 *
 * @package App\Nova
 */
class RaceResult extends Resource
{
    use SearchesRelations;

    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Racing';

    /**
     * Resource visibility on sidebar
     *
     * @var bool $displayInNavigation
     */
    public static $displayInNavigation = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Racing\RaceResult::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine if relations should be searched globally.
     *
     * @var array
     */
    public static $searchRelationsGlobally = false;

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'tote' => ['name'],
        'betType' => ['name'],
        'runner' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['race', 'tote', 'betType'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('ID'),

            BelongsTo::make('Race', 'race', Race::class)
                ->sortable()
                ->viewable(false),

            BelongsTo::make('Tote', 'tote', Tote::class)
                ->sortable()
                ->viewable(false),

            BelongsTo::make('Bet Type', 'betType', BetType::class)
                ->sortable()
                ->viewable(false),

            Text::make('Result', 'result', function ($data) {
                return $this->formatResult($data);
            }),

            Text::make('Dividend', 'dividend', function ($value) {
                return number_format(round($value * 0.01, 1), 1);
            })
                ->sortable(),

            Text::make('Status', 'is_final', function ($value) {
                return \App\Entities\Racing\RaceResult::STATUSES[$value];
            }),

            DateTime::make('Updated Time', 'updated_at')
                ->format('DD MMM YYYY H:mm a')
                ->hideWhenCreating()
                ->hideWhenUpdating()
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
        return [
            resolve(RaceResultToteFilter::class),
            resolve(RaceResultBetTypeFilter::class),
            resolve(RaceResultStatusFilter::class)
        ];
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
        return [
            resolve(RaceResultPushAction::class)->canRun(function () {
                return true;
            }),
        ];
    }

    /**
     * Format result before displaying it
     *
     * @param array $data
     * @return array|string
     */
    private function formatResult(array $data)
    {
        $result = Arr::first($data);
        $data = '';

        if (isset($result['runner_id'])) {
            $runner = LocalCache::get(__class__ . ':runners', $result['runner_id'], function ($runnerId) {
                $runnersRepository = app(RunnersRepository::class);
                return $runnersRepository->getRunnerById($runnerId);
            });

            $data = $runner->name . ' - ' . $result['runner_number'] . ' - ' . $result['position'];
        }

        return $data;
    }

    public function authorizedToUpdate(Request $request)
    {
        if ($request->action && $request->action === 'push-to-subscribers') {
            return true;
        }

        return parent::authorizedTo($request, 'update');
    }

    public function authorizedToDelete(Request $request)
    {
        if ($request->action && $request->action === 'push-to-subscribers') {
            return true;
        }

        return parent::authorizedTo($request, 'delete');
    }
}
