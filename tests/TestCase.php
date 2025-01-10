<?php

namespace Hyvor\LaravelPlaywright\Tests;

use Hyvor\LaravelPlaywright\ServiceProvider;
use Hyvor\LaravelPlaywright\Services\DynamicConfig;
use Hyvor\LaravelPlaywright\Tests\Helpers\Migrations;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Migrations::run();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        DynamicConfig::delete();
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

}
