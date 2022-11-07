<?php

namespace Easyagency\LaravelAdmin\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelAdmin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Easyagency\LaravelAdmin\LaravelAdmin::class;
    }
}