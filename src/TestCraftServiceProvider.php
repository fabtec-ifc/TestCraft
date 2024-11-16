<?php

namespace FabTec\TestCraft;

use FabTec\TestCraft\Commands\CreateTestCommand;
use Illuminate\Support\ServiceProvider;

class TestCraftServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            CreateTestCommand::class
        ]);
    }

    public function register()
    {

    }
}
