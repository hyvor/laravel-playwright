<?php

namespace Hyvor\LaravelPlaywright\Tests\Feature;

use Hyvor\LaravelPlaywright\Tests\TestCase;

class FunctionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        include __DIR__ . '/functions.php';
    }

    public function testCallsAFunction() : void
    {
        $this->postJson('/playwright/function', [
            'function' => 'testFunction1'
        ])
            ->assertOk()
            ->assertSee('Hello');
    }

    public function testCallsAFunctionWithArgs() : void
    {
        $this->postJson('/playwright/function', [
            'function' => 'testFunction2',
            'args' => ['Supun']
        ])
            ->assertOk()
            ->assertSee('Hello Supun');
    }

    public function testCallsAFunctionWithNamedArgs() : void
    {
        $this->postJson('/playwright/function', [
            'function' => 'testFunction3',
            'args' => [
                'age' => 24,
                'name' => 'Supun'
            ]
        ])
            ->assertOk()
            ->assertSee('Hello Supun. You are 24');
    }

    public function testCallsAStaticMethod() : void
    {
        $this->postJson('/playwright/function', [
            'function' => 'testStaticMethodClass1::sayHello',
            'args' => ['me']
        ])
            ->assertOk()
            ->assertSee('Says Hello to me');
    }

    public function testCallsAStaticMethodWithNamespace() : void
    {
        $this->postJson('/playwright/function', [
            'function' => 'Hyvor\LaravelPlaywright\Tests\Helpers\StaticMethodTest::ping',
        ])
            ->assertOk()
            ->assertSee('pong');

    }

}