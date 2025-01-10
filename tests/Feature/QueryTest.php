<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\Helpers\UserModel;
use Hyvor\LaravelPlaywright\Tests\TestCase;

class QueryTest extends TestCase
{

    public function testRunsAQuery() : void
    {
        $users = UserModel::factory()
            ->count(3)
            ->create();

        $this->postJson('/playwright/query', [
            'query' => "update users set name = 'John Doe' where id = " . $users[0]?->id
        ])->assertOk();

        $this->assertEquals('John Doe', $users[0]?->refresh()->name);
    }

}