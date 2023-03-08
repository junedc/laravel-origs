<?php

namespace App\Nova\Tools;

use App\Nova\Permission;
use App\Nova\Role;

/**
 * To use custom Role and Permission resources
 * Class NovaPermissionTool
 * @package App\Nova\Tools
 */
class NovaPermissionTool extends \Vyuldashev\NovaPermission\NovaPermissionTool
{
    public $roleResource = Role::class;
    public $permissionResource = Permission::class;
}