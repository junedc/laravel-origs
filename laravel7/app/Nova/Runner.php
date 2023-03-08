<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Runner
 *
 * @package App\Nova
 */
class Runner extends Resource
{
    use SearchesRelations;

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
    public static $model = \App\Models\Racing\Runner::class;

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
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'raceType' => ['name'],
        'trainer' => ['name']
    ];

    /**
     * Model relationship
     *
     * @var array $with
     */
    public static $with = ['raceType', 'trainer'];

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
            ID::make('Runner ID', 'id')
                ->sortable(),

            Text::make('Runner Name', 'name')
                ->rules('required', 'unique:racing_runners,name' . $validateParam),

            Text::make('Race Type', 'raceType', function ($raceType) {
                return $raceType->name;
            })
                ->readonly(),

            Text::make('Trainer', 'trainer', function ($raceType) {
                return $raceType->name;
            })
                ->readonly(),

            Number::make('Last Ten Starts', 'last_ten_starts')
                ->nullable()
                ->readonly(),

            Textarea::make('Owner', 'owners')
                ->onlyOnDetail()
                ->readonly(),

            Text::make('Color', 'color')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Sex', 'sex')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Age', 'age')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Foaling Date', 'foaling_date')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Sex', 'sex')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Dam', 'dam')
                ->readonly()
                ->onlyOnDetail(),

            Text::make('Pushing Data', function () {
                return view('nova::common.link', [
                    'name' => 'View',
                    'url' => route(
                        'racing.transformer.viewer',
                        [
                            'resource' => 'runner',
                            'resourceId' => $this->id
                        ]
                    )
                ])->render();
            })
                ->onlyOnDetail()
                ->asHtml(),

            DateTime::make('Last Modified', 'updated_at')
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
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

}
