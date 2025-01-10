<?php

namespace Hyvor\LaravelPlaywright\Services;

use Illuminate\Support\Facades\Config as LaravelConfig;

class Config
{

    public static function prefix(): string
    {
        /** @var string $prefix */
        $prefix = LaravelConfig::get('app.e2e.prefix', 'playwright');
        return (string) $prefix;
    }

    /**
     * @return string[]
     */
    public static function envs(): array
    {
        /** @var string[] $envs */
        $envs = LaravelConfig::get('app.e2e.environments', ['local', 'testing']);

        return $envs;
    }

}