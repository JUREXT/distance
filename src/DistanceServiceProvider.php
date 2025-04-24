<?php

declare(strict_types=1);

namespace TeamChallengeApps\Distance;

use Illuminate\Support\ServiceProvider;

class DistanceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->setupConfig();
    }

    /**
     * Setup the package configuration.
     */
    protected function setupConfig(): void
    {
        $configPath = realpath(__DIR__ . '/config/config.php');

        if ($configPath === false) {
            throw new \RuntimeException('Distance configuration file not found.');
        }

        $this->mergeConfigFrom($configPath, 'distance');

        $this->publishes([
            $configPath => config_path('distance.php'),
        ], 'config');
    }
}
