<?php

namespace Niladam\LaravelSendsms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Niladam\LaravelSendsms\LaravelSendsms
 */
class LaravelSendsms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-sendsms';
    }
}
