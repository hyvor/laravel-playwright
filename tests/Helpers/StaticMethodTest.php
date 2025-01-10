<?php

namespace Hyvor\LaravelPlaywright\Tests\Helpers;

class StaticMethodTest
{

    public static function ping() : string
    {
        return 'pong';
    }

}