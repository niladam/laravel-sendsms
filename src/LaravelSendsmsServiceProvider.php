<?php

namespace Niladam\LaravelSendsms;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Niladam\LaravelSendsms\Commands\LaravelSendsmsCommand;

class LaravelSendsmsServiceProvider extends ServiceProvider implements
    DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(
            LaravelSendsms::class,
            fn () => new LaravelSendsms(config('laravel-sendsms'))
        );

        $this->app->bind('laravel-sendsms', LaravelSendsms::class);

        $this->app->singleton("command.sendsms", function () {
            return new LaravelSendsmsCommand();
        });

        $this->commands(["command.sendsms"]);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__.'/../config/sendsms.php' => config_path(
                        'laravel-sendsms.php'
                    ),
                ],
                'config'
            );
            //            $this->commands([LaravelSendsmsCommand::class]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ["command.tinker"];
    }
}
