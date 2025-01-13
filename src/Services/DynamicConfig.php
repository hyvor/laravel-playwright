<?php

namespace Hyvor\LaravelPlaywright\Services;

use Carbon\Carbon;

/**
 * Usually, E2E tests calls multiple routes in a single test.
 * Sometimes, we need to change a config in the Laravel app for a single test.
 * This class handles that by creating a laravel-playwright-config.json file in the storage directory.
 * Which will be deleted when you call playwright/tearDown route
 * (which is called automatically after each test when using our Playwright library).
 */
class DynamicConfig
{

    /**
     * Used for time traveling
     */
    const KEY_TRAVEL = 'app.e2e.travel';

    /**
     * Used to run a function in the boot method of a service provider
     */
    const KEY_BOOT_FUNCTIONS = 'app.e2e.bootFunctions';

    public static function getFilePath(): string
    {
        return storage_path('laravel-playwright-config.json');
    }

    public static function load(): void
    {
        $file = self::getFilePath();
        $content = file_exists($file) ? (string) file_get_contents($file) : '{}';
        $data = @json_decode($content, true);

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                assert(is_string($key));
                config([$key => $value]);
            }

            self::loadTime();
            self::loadBootFunctions();
        }

    }

    private static function loadTime(): void
    {
        $time = self::get(self::KEY_TRAVEL);
        if (!is_string($time)) {
            return;
        }
        $time = Carbon::parse($time);
        \Carbon\Carbon::setTestNow($time);
        \Carbon\CarbonImmutable::setTestNow($time);
    }

    private static function loadBootFunctions(): void
    {

        $functions = self::get(self::KEY_BOOT_FUNCTIONS);
        if (!is_array($functions)) {
            return;
        }

        foreach ($functions as $function) {
            if (is_callable($function)) {
                $function();
            }
        }

    }

    public static function set(string $key, mixed $value): void
    {
        $file = self::getFilePath();
        $data = self::getAll();
        $data[$key] = $value;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $data = self::getAll();
        return $data[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $file = self::getFilePath();
        if (!file_exists($file)) {
            return [];
        }
        /** @var array<string, mixed> $json */
        $json = json_decode((string) file_get_contents($file), true);
        return $json;
    }

    public static function delete(): void
    {
        $file = self::getFilePath();
        if (file_exists($file)) {
            unlink($file);
        }
    }

}