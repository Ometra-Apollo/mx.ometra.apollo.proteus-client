<?php
namespace Ometra\Apollo\Proteus\Facades;

use Illuminate\Support\Facades\Facade;
use Ometra\Apollo\Proteus\Proteus as ProteusClass;

class Proteus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProteusClass::class;
    }
}
