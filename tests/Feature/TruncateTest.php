<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\Helpers\UserModel;
use Hyvor\LaravelPlaywright\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TruncateTest extends TestCase
{

    protected function tearDown(): void
    {
        DB::statement('drop table if exists countries');
        parent::tearDown();
    }

    private function seedCountries(): void
    {
        DB::statement('drop table if exists countries');
        DB::statement('create table countries (id bigserial, name varchar(255))');
        DB::table('countries')->insert([['name' => 'Sri Lanka'], ['name' => 'Germany']]);
    }

    public function testTruncates(): void
    {
        UserModel::factory()->count(3)->create();
        $this->assertCount(3, UserModel::all());

        $this->postJson('/playwright/truncate');

        $this->assertCount(0, UserModel::all());
    }

    public function testKeepsExceptTables(): void
    {
        $this->seedCountries();
        UserModel::factory()->count(3)->create();

        $this->postJson('/playwright/truncate', [
            'except' => ['countries'],
        ])->assertOk();

        $this->assertCount(0, UserModel::all());
        $this->assertSame(2, DB::table('countries')->count());
    }

    public function testFailsOnUnknownTableWithoutTruncating(): void
    {
        UserModel::factory()->count(3)->create();

        $this->postJson('/playwright/truncate', [
            'except' => ['does_not_exist'],
        ])->assertStatus(422);

        $this->assertCount(3, UserModel::all());
    }

    public function testEmptyExceptTruncatesEverything(): void
    {
        UserModel::factory()->count(3)->create();

        $this->postJson('/playwright/truncate', [
            'except' => [],
        ])->assertOk();

        $this->assertCount(0, UserModel::all());
    }

}
