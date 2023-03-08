<?php

namespace App\Nova;

/**
 * To use custom Role model
 * Class Role
 * @package App\Nova
 */
class Role extends \Vyuldashev\NovaPermission\Role
{
    /**
     * @var string
     */
    public static $model = Models\Role::class;
}