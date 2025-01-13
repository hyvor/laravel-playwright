# Laravel Playwright

This repository contains a Laravel and a Playwright library to help you write E2E tests for your Laravel application using [Playwright](https://playwright.dev/). It adds a set of endpoints to your Laravel application to allow Playwright to interact with it. You can do the following from your Playwright tests:

- Run artisan commands
- Create models using factories
- Run database queries
- Run PHP functions
- Update Laravel config while a test is running (until the test ends and calls `tearDown`).
- Registering a boot function to run on each Laravel request. You can use this feature to mock a service dependency, for example.
- Traveling to a specific time in the application during the test

## Installation

On Laravel side, install the package via composer:

```bash
composer require --dev hyvor/laravel-playwright
```

On Playwright side, install the package via npm:

```bash
npm install @hyvor/laravel-playwright
```

## Laravel Config

You can configure the routes by adding an `e2e` key to your `config/app.php` file. The following options are available (default values are shown):

```php
return [
    // ...

    'e2e' => [
        /**
        * The prefix for the testing endpoints that are used to interact with Playwright
        * Make sure to change `use.laravelBaseUrl` in playwright.config.ts if you change this
        */
        'prefix' => 'playwright',
        
        /**
        * The environments in which the testing endpoints are enabled
        * CAUTION: Enabling the testing endpoints in production can be a critical security issue
        */
        'environments' => ['local', 'testing'],
    ],
];
```

## Playwright Config

Set `use.laravelBaseUrl` in your `playwright.config.ts` file to the base URL of your testing endpoints. This is the URL of your application + the `prefix` you set in Laravel config.

```ts
export default defineConfig({
    // ...other
    use: {
        laravelBaseUrl: 'http://localhost/playwright',
    },
});
```

If you use Typescript, include the `LaravelOptions` type in the `defineConfig` function.

```ts
import type { LaravelOptions } from '@hyvor/laravel-playwright';

export default defineConfig<LaravelOptions>({
    use: {
        laravelBaseUrl: 'http://localhost/playwright',
    },
});
```

## Setting up tests

In your Playwright tests, swap the `test` import from `@playwright/test` to `@hyvor/laravel-playwright`.

```diff
- import { test } from '@playwright/test';
+ import { test } from '@hyvor/laravel-playwright';

test('example', async ({ laravel }) => {
    laravel.artisan('migrate:fresh');
});
``` 

> **Note**: In practise, it is not recommended to import from `@hyvor/laravel-playwright` directly on every test file, if you have many. Instead, create your own [test fixture](https://playwright.dev/docs/test-fixtures) extending `test` from `@hyvor/laravel-playwright` and import that fixture in your tests.

## Basic Usage

```ts
import { test } from '@hyvor/laravel-playwright';

test('example', async ({ laravel }) => {

    // RUN ARTISAN COMMANDS
    // ====================
    const output = await laravel.artisan('migrate:fresh');
    // output.code: number - The exit code of the command
    // output.output: string - The output of the command
    // with parameters
    await laravel.artisan('db:seed', ['--class', 'DatabaseSeeder']);
    
    
    // TRUNCATE TABLES
    // ===============
    await laravel.truncate();
    // in specific DB connections
    await laravel.truncate(['connection1', 'connection2']);
    
    
    // CREATE MODELS FROM FACTORIES
    // ============================
    // Create a App\Models\User model
    // user will be an object of the model
    const user = await laravel.factory('User');
    // Create a App\Models\User model with attributes
    await laravel.factory('User', { name: 'John Doe' });
    // Create 5 App\Models\User models
    // users will be an array of the models
    const users = await laravel.factory('User', {}, 5);
    // Create a CustomModel model
    await laravel.factory('CustomModel');
    
    
    // RUN A DATABASE QUERY
    // ====================
    // Run a query
    await laravel.query('DELETE FROM users');
    // Run a query with bindings
    await laravel.query('DELETE FROM users WHERE id = ?', [1]);
    // Run a query on a specific connection
    await laravel.query('DELETE FROM users', [], { connection: 'connection1' });
    // Run a unprepared statement
    await laravel.query(`
        DROP SCHEMA public CASCADE;
        CREATE SCHEMA public;
        GRANT ALL ON SCHEMA public TO public;
    `, [], { unprepared: true });
    
    
    // RUN A SELECT QUERY
    // ==================
    // Run a select query
    // Returns an array of objects
    const blogs = await laravel.select('SELECT * FROM blogs');
    // Run a select query with bindings
    await laravel.select('SELECT * FROM blogs WHERE id = ?', [1]);
    // Run a select query on a specific connection
    await laravel.select('SELECT * FROM blogs', [], { connection: 'connection1' });
    
    
    // RUN A PHP FUNCTION
    // ==================
    // Run a PHP function
    // Returns the output of the function
    // Output is JSON encoded in Laravel and decoded in Playwright
    // The following examples call this function:
    // function sayHello($name) { return "Hello, $name!"; }
    const funcOutput = await laravel.callFunction('sayHello');
    // Run a PHP function with parameters
    await laravel.callFunction('sayHello', ['John']);
    // Run a PHP function with named parameters
    await laravel.callFunction('sayHello', { name: 'John' });
    // Run a static class method
    await laravel.callFunction("App\\MyAwesomeClass::method");
    
});


```

## Dynamic Configuration

You can update Laravel config for **ALL** subsequent requests until the test ends.

```ts
import { test } from '@hyvor/laravel-playwright';

test('example', async ({ laravel }) => {

    // SET DYNAMIC CONFIG
    // ==================
    // Update a config value
    // This value will be used in all subsequent requests sent to Laravel
    // until the test ends and calls `tearDown` (which is done automatically)
    await laravel.config('app.timezone', 'Europe/Paris');
    
    // TRAVEL TO A TIME
    // =================
    // Travel to a specific time in the application
    // This is similar to Laravel's `travelTo` method
    await laravel.travel('2022-01-01 12:00:00');
    
    // REGISTER A BOOT FUNCTION
    // ========================
    // Register a function to run while Laravel is booting
    // This is useful to mock a service dependency, for example
    await laravel.registerBootFunction('App\\E2EHelper::swapPaymentService');

});
```