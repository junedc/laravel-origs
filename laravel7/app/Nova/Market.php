<?php

namespace App\Nova;

use App\Nova\Actions\Sport\MarketPushAction;
use App\Nova\Filters\Sport\MarketInPlayFilter;
use App\Nova\Filters\Sport\MarketPreMatchFilter;
use App\Nova\Filters\Sport\MarketStatusFilter;
use App\Repositories\MarketsRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Orlyapps\NovaBelongsToDepend\NovaBelongsToDepend;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Market
 *
 * @package App\Nova
 */
class Market extends Resource
{
    use SearchesRelations;

    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Sports';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Sport\Market::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
        'event' => ['name'],
        'status' => ['name'],
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['event', 'status', 'selections'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $pmField = $this->getParentMarketField($request);

        return [
            ID::make('Market ID', 'id')
                ->sortable(),

            Text::make('Market Name', 'name')
                ->rules('required')
                ->sortable(),

            Text::make('Feed Name', 'feed_name')
                ->onlyOnDetail(),

            BelongsTo::make('Event', 'event', SportEvent::class)
                ->onlyOnDetail(),

            BelongsTo::make('Status', 'status', MarketStatus::class)
                ->viewable(false),

            $pmField,

            Boolean::make('Traded Pre-Match', 'traded_pre_match'),

            Boolean::make('Traded In-Play', 'traded_in_play'),


            Text::make('External ID', 'external_id')
                ->onlyOnDetail(),

            Text::make('Sequence', 'sequence')
                ->onlyOnDetail(),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route('pushing_data.markets', ['id' => $this->id])
                ])->render();
            })
                ->onlyOnDetail()
                ->asHtml(),

            HasMany::make('Selections', 'selections', SportSelection::class),
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
            resolve(MarketPreMatchFilter::class),
            resolve(MarketInPlayFilter::class),
            resolve(MarketStatusFilter::class),
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
            resolve(MarketPushAction::class)
        ];
    }

    /**
     * Redirect to model details page
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
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
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    /**
     * @param NovaRequest $request
     * @param $query
     * @return Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        if ($request->get('current')) {
            /** @var MarketsRepository $marketsRepository */
            $marketsRepository = app(MarketsRepository::class);
            $market = $marketsRepository->getById($request->get('current'));
            $query->where('event_id', $market->eventId);
        }

        return $query;
    }

    /**
     * getParentMarketField method
     *
     * @param Request $request
     * @return BelongsTo|NovaBelongsToDepend
     */
    private function getParentMarketField(Request $request)
    {
        // Only for creating new market
        // Todo find a better solution
        if ($request->get('viaResource') == 'sport-events' && empty($request->get('relationshipType'))) {
            $sportEventId = $request->get('viaResourceId');
            $sportId = \App\Models\Sport\SportEvent::find($sportEventId)->competition->sportId;
            $parentMarketField = NovaBelongsToDepend::make('Parent market', 'parentMarket', ParentMarket::class)
                ->options(\App\Models\Sport\ParentMarket::join('sport_market_types', 'sport_market_types.id', '=', 'sport_parent_markets.market_type_id')
                    ->where('sport_market_types.sport_id', $sportId)
                    ->select('sport_parent_markets.*')
                    ->get());
        } else {
            $parentMarketField = BelongsTo::make('Parent market', 'parentMarket', ParentMarket::class)
                ->hideWhenUpdating()
                ->hideFromDetail()
                ->hideFromIndex();
        }

        $parentMarketField->hideWhenUpdating()
            ->hideFromDetail()
            ->hideFromIndex();

        return $parentMarketField;
    }
}
