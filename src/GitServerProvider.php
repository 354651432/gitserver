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
        if (config("git.enable") === false) {
            return;
        }
        //
        $this->loadRoutesFrom(__DIR__ . "/routes.php");
        $this->commands(GitServerCommand::class);
        $this->commands(GitServerUserCommand::class);

        $guards = config("auth.guards");
        $guards["git"] = [
            'provider' => 'git',
            'driver' => 'session',
        ];
        config(["auth.guards" => $guards]);

        $providers = config("auth.providers");
        $providers["git"] = [
            'driver' => 'database',
            'table' => 'git_user',
        ];
        config(["auth.providers" => $providers]);
    }
}
