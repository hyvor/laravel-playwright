<?php

namespace Hyvor\LaravelPlaywright\Tests\Helpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migrations
{

    public static function run(): void
    {

        DB::statement('drop table if exists users');

        DB::statement('
            create table users (
                id bigserial,
                name varchar(255),
                created_at timestamp,
                updated_at timestamp
            )
        ');

    }

}