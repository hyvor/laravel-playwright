<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ArtisanTest extends TestCase
{

    public function testRunsArtisanCommand(): void
    {

        /** @var array<string|int> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'route:list'
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('playwright/artisan', (string) $json['output']);

    }

    public function testRunsArtisanCommandWithCliStyleParameters(): void
    {
        Artisan::command('test:params {--name=}', function () {
            /** @var \Illuminate\Console\Command $this */
            $this->info('Name: ' . $this->option('name'));
        });

        /** @var array<string, mixed> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'test:params',
            'parameters' => ['--name=John']
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('Name: John', (string) $json['output']);
    }

    public function testRunsArtisanCommandWithAssociativeParameters(): void
    {
        Artisan::command('test:assoc {--name=}', function () {
            /** @var \Illuminate\Console\Command $this */
            $this->info('Name: ' . $this->option('name'));
        });

        /** @var array<string, mixed> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'test:assoc',
            'parameters' => ['--name' => 'Jane']
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('Name: Jane', (string) $json['output']);
    }

    public function testRunsArtisanCommandWithBooleanFlags(): void
    {
        Artisan::command('test:flags {--force}', function () {
            /** @var \Illuminate\Console\Command $this */
            $this->info('Force: ' . ($this->option('force') ? 'yes' : 'no'));
        });

        /** @var array<string, mixed> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'test:flags',
            'parameters' => ['--force']
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('Force: yes', (string) $json['output']);
    }

    public function testRunsArtisanCommandWithShortOptionFlags(): void
    {
        Artisan::command('test:short {--Q|--quick}', function () {
            /** @var \Illuminate\Console\Command $this */
            $this->info('Quick: ' . ($this->option('quick') ? 'yes' : 'no'));
        });

        /** @var array<string, mixed> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'test:short',
            'parameters' => ['-Q']
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('Quick: yes', (string) $json['output']);
    }

    public function testRunsArtisanCommandWithPositionalArguments(): void
    {
        Artisan::command('test:positional {name}', function () {
            /** @var \Illuminate\Console\Command $this */
            $this->info('Name: ' . $this->argument('name'));
        });

        /** @var array<string, mixed> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'test:positional',
            'parameters' => ['name' => 'Alice']
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('Name: Alice', (string) $json['output']);
    }

}