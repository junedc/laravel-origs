<?php

namespace App\Nova;

use App\Domain\Cache\RacingDataRetriever;
use App\Jobs\Racing\PushSelection;
use App\Models\Racing\Selection;
use App\Nova\Actions\Racing\SelectionPushAction;
use App\Nova\Fields\LinkModal;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;
use App\Models\Racing\RunnerStatus as SelectionStatus;

/**
 * Class RunnerSelection
 *
 * @package App\Nova
 */
class RunnerSelection extends Resource
{
    use SearchesRelations;

    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Racing';

    /**
     * Remove from navigation
     *
     * @var bool $displayInNavigation
     */
    public static $displayInNavigation = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Selection::class;

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
        'id',
        'name'
    ];

    /**
     * Default order for the resource
     *
     * @var array $orderBy
     */
    public static $orderBy = [
        'number' => 'asc'
    ];

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
        'race' => ['name'],
        'runner' => ['name'],
        'status' => ['name'],
        'jockey' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['race', 'runner', 'status', 'jockey', 'prices'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $raceField = [
            // Nova tries to load all races in form for the relation
            // So only display as text in form
            BelongsTo::make('Race', 'race')
                ->exceptOnForms()
                ->readonly(),

            Text::make('Race', 'race', function ($race) {
                return $race->name;
            })
                ->onlyOnForms()
                ->readonly(),
        ];

        $runnerField = [
            BelongsTo::make('Runner', 'runner')
                ->exceptOnForms()
                ->readonly(),

            Text::make('Runner', 'runner', function ($runner) {
                return $runner->name;
            })
                ->onlyOnForms()
                ->readonly(),
        ];

        $statusField = [
            BelongsTo::make('Status', 'status', RunnerStatus::class)
                ->onlyOnForms(),

            Status::make('Status', 'status', function ($value) {
                return $value->name;
            })
                ->failedWhen(['Scratched'])
                ->loadingWhen([])
                ->exceptOnForms(),
        ];

        $fields = [
            ID::make('ID', 'id')
                ->sortable(),

            Number::make('No.', 'number')
                ->readonly(),
        ];

        $fields = array_merge($fields, $raceField, $runnerField, $statusField);
        $fields = array_merge($fields, [

            DateTime::make('Scratched', 'scratched_time')
                ->format('DD MMM YYYY H:mm a')
                ->nullable()
                ->rules('required_if:status,' . SelectionStatus::SCRATCHED . ',' . SelectionStatus::LATE_SCRATCHING),


            LinkModal::make('Prices', function () {
                return $this->formatPrices(
                    $this->prices->all(),
                    $this->winFixedPrice ?? null,
                    $this->placeFixedPrice ?? null
                );
            })
                ->build(($this->runner->name ?? '') . ' Prices', true)
                ->isViaRelationship($request->get('viaRelationship')),

            Text::make('SP', 'starting_price', function () {
                return $this->starting_price ? $this->starting_price / 100 : null;
            }),
            Text::make('TF', 'top_fluc', function () {
                return $this->top_fluc ? $this->top_fluc / 100 : null;
            }),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route(
                        'racing.transformer.viewer',
                        [
                            'resource' => 'selection',
                            'resourceId' => $this->id
                        ]
                    )
                ])->render();
            })
                ->onlyOnDetail()
                ->asHtml(),
        ]);

        return $fields;
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
        return [
            resolve(SelectionPushAction::class)->canRun(function () {
                return true;
            }),
        ];
    }

    /**
     * Redirect to index after model creation
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey();
    }

    /**
     * Return to view model after updating
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        PushSelection::dispatch([$resource->getKey()]);
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    /**
     * Format prices before displaying it
     *
     * @param array $prices
     * @param $winFixedPrice
     * @param $placeFixedPrice
     * @return array|null
     */
    private function formatPrices(array $prices, $winFixedPrice, $placeFixedPrice)
    {
        if (empty($prices) && empty($winFixedPrice) && empty($placeFixedPrice)) {
            return null;
        }

        $data = [];
        $racingDataRetriever = app(RacingDataRetriever::class);

        $betTypes = $racingDataRetriever->getBetTypes();
        $racingTotes = $racingDataRetriever->getTotes();

        foreach ($prices as $price) {
            $tote = $racingTotes[$price->toteId]['name'];
            $betType = $betTypes[$price->betTypeId]['name'];
            $data[$betType][$price->betTypeId][$tote] = bcdiv($price->price, 100, 2);
        }

        $win = \App\Models\Racing\BetType::WIN;
        $data[$betTypes[$win]['name']][$win]['FIX'] = bcdiv($winFixedPrice, 100, 2);

        $place = \App\Models\Racing\BetType::PLACE;
        $data[$betTypes[$place]['name']][$place]['FIX'] = bcdiv($placeFixedPrice, 100, 2);

        return $data;
    }
}
