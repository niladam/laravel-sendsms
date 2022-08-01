<?php

namespace Niladam\LaravelSendsms;

use Illuminate\Support\ServiceProvider;
use Niladam\LaravelSendsms\Commands\LaravelSendsmsCommand;

class LaravelSendsmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            LaravelSendsms::class,
            fn() => new LaravelSendsms(config("laravel-sendsms"))
        );

        $this->app->bind("laravel-sendsms", LaravelSendsms::class);

        $this->app->singleton("command.sendsms", fn() => new LaravelSendsmsCommand());

        $this->commands(["command.sendsms"]);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__."/../config/sendsms.php" => config_path(
                        "laravel-sendsms.php"
                    ),
                ],
                "config"
            );
        }
    }
}
