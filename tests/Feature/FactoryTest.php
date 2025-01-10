<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\Helpers\UserModel;
use Hyvor\LaravelPlaywright\Tests\TestCase;

class FactoryTest extends TestCase
{

    public function testCreatesModelFromFactory(): void
    {

        $this->postJson('playwright/factory', [
            'model' => '\Hyvor\LaravelPlaywright\Tests\Helpers\UserModel',
            'attrs' => [
                'name' => 'John Doe',
            ]
        ])
            ->assertOk()
            ->assertJsonPath('name', 'John Doe');

        $this->assertEquals(1, UserModel::count());

    }

    public function testCreatesModelFromFactoryWithCount(): void
    {

        $this->postJson('playwright/factory', [
            'model' => '\Hyvor\LaravelPlaywright\Tests\Helpers\UserModel',
            'count' => 3
        ])
            ->assertOk()
            ->assertJsonCount(3);

        $this->assertEquals(3, UserModel::count());

    }

}