<?php

namespace Six\GitServer;

use Illuminate\Support\ServiceProvider;

class GitServerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->make(GitController::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadRoutesFrom(__DIR__ . "/routes.php");
        $this->commands(GitServerCommand::class);
    }
}
