<?php

namespace App\Nova;

/**
 * To use custom Permission model
 * Class Permission
 * @package App\Nova
 */
class Permission extends \Vyuldashev\NovaPermission\Permission
{
    /**
     * @var string
     */
    public static $model = Models\Permission::class;
}