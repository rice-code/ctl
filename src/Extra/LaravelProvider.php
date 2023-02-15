<?php

namespace Rice\Ctl\Extra;

use Illuminate\Support\ServiceProvider;
use Rice\Ctl\Console\Command\I18nCommand;
use Rice\Ctl\Console\Command\AccessorCommand;
use Rice\Ctl\Console\Command\JsonToClassCommand;

class LaravelProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * 在注册后启动服务
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                I18nCommand::class,
                AccessorCommand::class,
                JsonToClassCommand::class,
            ]);
        }
    }
}
