<?php

namespace FluentPlugin\App\Hooks\Handlers;

use FluentPlugin\Framework\Foundation\Application;
use FluentPlugin\Database\DBMigrator;
use FluentPlugin\Database\DBSeeder;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle($network_wide = false)
    {
        DBMigrator::run($network_wide);
        DBSeeder::run();
    }
}
