<?php

namespace App\Nova;

use App\Events\Racing\ToteUpdated;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Tote
 *
 * @package App\Nova
 */
class Tote extends Resource
{
    /**
     * Resource group
     *
     * @var string $group
     */
    public static $group = 'Racing';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Racing\Tote';

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
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        // If update and the value is un-touched
        // add another qualification for uniqueness
        $resourceId = $request->resourceId;
        $validateParam = $resourceId ? ',' . $resourceId : '';

        return [
            ID::make('Tote ID', 'id')
                ->sortable(),

            Text::make('Tote Name', 'name')
                ->rules('required', 'unique:racing_totes,name' . $validateParam)
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
     * Get the actions available for the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function actions(Request $request)
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
        event(new ToteUpdated());
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
        event(new ToteUpdated());
        return '/resources/' . static::uriKey();
    }
}
