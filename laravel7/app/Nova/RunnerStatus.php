<?php

namespace App\Nova;

use Illuminate\Http\Request;

/**
 * Class RunnerStatus
 *
 * @package App\Nova
 */
class RunnerStatus extends Resource
{
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
    public static $model = 'App\Models\Racing\RunnerStatus';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [

        ];
    }
}
