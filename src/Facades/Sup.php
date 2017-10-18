<?php

namespace SjorsO\Sup\Facades;

use Illuminate\Support\Facades\Facade;

class Sup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SjorsO\Sup\SupFile::class;
    }
}
