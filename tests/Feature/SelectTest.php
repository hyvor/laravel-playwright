<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\Helpers\UserModel;
use Hyvor\LaravelPlaywright\Tests\TestCase;

class SelectTest extends TestCase
{
    public function testSelectsAUser(): void
    {
        $user = UserModel::factory()->create();

        $response = $this->postJson('/playwright/select', [
            'query' => 'select * from users where id = ' . $user->id
        ]);

        $response->assertOk();
        $response->assertJsonPath('0.id', $user->id);
    }
}