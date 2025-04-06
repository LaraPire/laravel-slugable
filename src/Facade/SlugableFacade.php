<?php

namespace Rayiumir\Slugable\Facade;

use Illuminate\Support\Facades\Facade;

class SlugableFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'slugable';
    }
}
