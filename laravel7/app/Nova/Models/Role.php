<?php

namespace App\Nova\Models;

class Role extends \Spatie\Permission\Models\Role
{
    /**
     * Add guard_name here so that the laravel-permission package won't
     * search columns in information_schema.
     * @var string
     */
    protected $fillable = ['guard_name'];
}