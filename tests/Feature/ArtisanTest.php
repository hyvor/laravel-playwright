<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\TestCase;

class ArtisanTest extends TestCase
{

    public function testRunsArtisanCommand(): void
    {

        /** @var array<string|int> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'list',
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        $this->assertStringContainsString('cache:clear', (string) $json['output']);

    }

    public function testAddsArgs(): void
    {

        /** @var array<string|int> $json */
        $json = $this->post('playwright/artisan', [
            'command' => 'list',
            'parameters' => [
                '--format' => 'json',
            ],
        ])
            ->assertOk()
            ->json();

        $this->assertEquals(0, $json['code']);
        /** @var array<mixed, array<mixed>> $output */
        $output = json_decode((string) $json['output'], true);
        $this->assertSame('Laravel Framework', $output['application']['name']);
        $this->assertStringContainsString('php artisan list --format=json', (string) $json['command']);

    }

}