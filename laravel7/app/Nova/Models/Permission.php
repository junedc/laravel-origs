<?php

namespace App\Nova\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    /**
     * Add guard_name here so that the laravel-permission package won't
     * search columns in information_schema.
     *
     * @var string
     */
    protected $fillable = ['guard_name'];
}