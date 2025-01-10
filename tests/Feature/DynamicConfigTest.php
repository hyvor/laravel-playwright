<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\TestCase;

class DynamicConfigTest extends TestCase
{

    public function testCreateDynamicConfig(): void
    {

        $this->postJson('/playwright/dynamicConfig', [
            'key' => 'test_key',
            'value' => 'test_value',
        ])->assertOk();

        $file = storage_path('laravel-playwright-config.json');
        $this->assertTrue(file_exists($file));
        $content= (string) file_get_contents($file);

        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('test_key', $json);
        $this->assertEquals('test_value', $json['test_key']);

    }

}