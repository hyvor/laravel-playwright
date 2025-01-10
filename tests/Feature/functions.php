<?php

if (!function_exists('testFunction1')) {
    function testFunction1() : string {
        return 'Hello';
    }
}

if (!function_exists('testFunction2')) {
    function testFunction2(string $name) : string {
        return 'Hello ' . $name;
    }
}

if (!function_exists('testFunction3')) {
    function testFunction3(string $name, int $age): string {
        return 'Hello ' . $name . '. You are ' . $age;
    }
}

if (!class_exists('testStaticMethodClass1')) {

    class testStaticMethodClass1 {
        static function sayHello(string $name) : string {
            return "Says Hello to " . $name;
        }
    }

}