<?php

namespace App\Nova;

use App\Models\Sport\Selection;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class SportSelection
 *
 * @package App\Nova
 */
class SportSelection extends Resource
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
    public static $model = Selection::class;

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
        'market' => ['name'],
        'status' => ['name']
    ];

    public static $with = ['market', 'status'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Selection ID', 'id')
                ->sortable(),

            Text::make('Selection Name', 'name')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('Market Name', 'market', Market::class),

            BelongsTo::make('Status', 'status', SportSelectionStatus::class)
                ->viewable(false),

            Number::make('Price', 'decimal_price'),

            Number::make('Override Price', 'override_price'),

            Number::make('Line', 'line')
                ->sortable(),

            Boolean::make('Tradable', 'tradable'),

            BelongsTo::make('Result', 'result', SportSelectionResult::class)
                ->rules('required')
                ->viewable(false),

            Text::make('External ID', 'external_id')
                ->onlyOnDetail(),

            DateTime::make('Last Modified', 'updated_at')
                ->format('DD MMM YYYY H:mm a')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable(),
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
     * Redirect to index after model creation
     *
     * @param NovaRequest $request
     * @param Resource $resource
     * @return string
     */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        $url = static::uriKey();

        if ($request->get('viaResource')) {
            $url = $request->get('viaResource') . '/' . $request->get('viaResourceId');
        }

        return '/resources/' . $url;
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
        $url = static::uriKey() . '/' . $resource->getKey();

        if ($request->get('viaResource')) {
            $url = $request->get('viaResource') . '/' . $request->get('viaResourceId');
        }

        return '/resources/' . $url;
    }
}
