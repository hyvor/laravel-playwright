<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\ServiceProvider;
use Hyvor\LaravelPlaywright\Tests\TestCase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

class RouteEnvTest extends TestCase
{

    public function testDoesNotAddRoutesForProduction(): void
    {
        $this->post('playwright/truncate')->assertOk();

        assert($this->app !== null);

        $this->app->detectEnvironment(fn() => 'production');
        $this->app->register(ServiceProvider::class, true);
        $this->app['env'] = 'production'; // @phpstan-ignore-line

        Route::setRoutes(new RouteCollection());
        (new ServiceProvider($this->app))->boot();
        $this->post('playwright/artisan')->assertNotFound();
    }

    public function testSupportsCustomPrefix(): void
    {
        config(['app.e2e.prefix' => 'api/e2e']);

        assert($this->app !== null);

        Route::setRoutes(new RouteCollection());
        (new ServiceProvider($this->app))->boot();

        $this->post('playwright/truncate')->assertNotFound();
        $this->post('api/e2e/truncate')->assertOk();
    }

}