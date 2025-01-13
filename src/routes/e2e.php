<?php

use Hyvor\LaravelPlaywright\Services\Config;
use Illuminate\Support\Facades\Route;
use Hyvor\LaravelPlaywright\Controller;


Route::prefix(Config::prefix())->group(function () {

    Route::post('artisan', [Controller::class, 'artisan']);
    Route::post('truncate', [Controller::class, 'truncate']);
    Route::post('factory', [Controller::class, 'factory']);
    Route::post('query', [Controller::class, 'query']);
    Route::post('select', [Controller::class, 'select']);
    Route::post('function', [Controller::class, 'function']);
    Route::post('dynamicConfig', [Controller::class, 'dynamicConfig']);
    Route::post('travel', [Controller::class, 'travel']);
    Route::post('registerBootFunction', [Controller::class, 'registerBootFunction']);

    Route::post('tearDown', [Controller::class, 'tearDown']);

});
