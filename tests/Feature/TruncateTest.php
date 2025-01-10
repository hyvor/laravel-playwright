<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\Helpers\UserModel;
use Hyvor\LaravelPlaywright\Tests\TestCase;

class TruncateTest extends TestCase
{
    public function testTruncates(): void
    {
        UserModel::factory()->count(3)->create();
        $this->assertCount(3, UserModel::all());

        $this->postJson('/playwright/truncate');

        $this->assertCount(0, UserModel::all());
    }
}