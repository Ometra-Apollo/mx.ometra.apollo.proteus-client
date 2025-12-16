<?php
namespace Ometra\Apollo\Proteus\Facades;

use Illuminate\Support\Facades\Facade;
use Ometra\Apollo\Proteus\Proteus as ProteusClass;

/**
 * Facade estática para acceder al cliente de Proteus.
 *
 * Permite utilizar llamadas del tipo `Proteus::mediaIndex()` en
 * cualquier parte de la aplicación Laravel.
 */
class Proteus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProteusClass::class;
    }
}
