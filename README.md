# Laravel Playwright

This package contains a Laravel library and a Typescript library to help you write E2E tests for your Laravel application using [Playwright](https://playwright.dev/).

It adds the following endpoints to your application in `local` and `testing` environments:

* `POST /playwright/artisan` - Run an artisan command
* `POST /playwright/truncate` - Truncate all tables
* `POST /playwright/factory` - Create a model using factories
* `POST /playwright/query` - Run a database query
* `POST /playwright/select` - Run a database select query
* `POST /playwright/function` - Call a PHP function (or static class method)

## Installation

You can install the package via composer:

```bash
composer require --dev hyvor/laravel-e2e
```

## Configuration

You can configure the package by adding an `e2e` key to your `config/app.php` file. The following options are available (default values are shown):

```php
return [
    // ...

    'e2e' => [
        /**
        * The prefix for the testing endpoints
        */
        'prefix' => 'playwright',
        
        /**
        * The environments in which the testing endpoints are enabled
        */
        'environments' => ['local', 'testing'],
    ],
];
```

## Usage

### Example Usage with Javascript

```js
fetch('/playwright/artisan', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify(INPUT_DATA),
})
```

### Run artisan commands

`POST playwright/artisan` endpoint allows you to run artisan commands from your tests. For example, you can run `php artisan migrate:fresh` before each test. It accepts two parameters:

- `command` - The artisan command to run
- `parameters` - The parameters to pass to the command (array/object, optional)

```json
{
    "command": "migrate:fresh",
    "parameters": ["--seed"]
}
```

This endpoint returns the exit code and the output of the command in JSON format:

```json
{
    "code": 0,
    "output": ""
}
```

### Truncate all tables

Truncating tables is faster than running `migrate:fresh` command in small sized databases. You can use `POST /playwright/truncate` endpoint to truncate all tables. It accepts an optional `connections` parameter to truncate tables in specific connections. If the `connections` parameter is not set, it truncates tables in the default connection.

```jsonc
{
    "connections": [] // optional
}
```

### Create a model using factories

You can use `POST /playwright/factory` endpoint to create a model using factories. It accepts the following parameters:

- `model` - The model class name (if the model class name starts with `App\Models\`, you can omit it)
- `count` - The number of models to create (optional, default: 1). If count is set (even if it's 1), it returns an array of models. Otherwise, it returns a single model.
- `attributes` - The attributes to set (optional)

The following example creates a single `App\Models\User` model with the `name` attribute set to `John Doe`, and returns it in JSON format:

```json
{
    "model": "User",
    "attributes": {
        "name": "John Doe"
    }
}
```

The following example creates 5 `App\Database\Models\User` models and returns them as an array in JSON format:

```json
{
    "model": "App\\Database\\Models\\User",
    "count": 5
}
```

### Run a database query

You can use `POST /playwright/query` endpoint to run a database query. It accepts the following parameters:

- `query` - The query to run
- `connection` - The database connection to use (optional)

```json
{
    "query": "UPDATE users SET name = 'John Doe' WHERE id = 1",
    "connection": "mysql"
}
```

### Run a database select query

You can use `POST /playwright/select` endpoint to run a database select query. It accepts the following parameters:

- `query` - The query to run
- `connection` - The database connection to use (optional)

It returns the result as an array of objects in JSON format.

```json
{
    "query": "SELECT * FROM users WHERE id = 1",
    "connection": "mysql"
}
```

### Call a PHP function or static class method

Use the `POST /playwright/function` endpoint to call a PHP function or a static class method. It accepts the following parameters:

- `function` - Function name to call
- `args` - Array of arguments to send to the function

Function's return value will be returned (JSON-encoded) from this endpoint.

Examples:

Arguments as an array:

```json
{
    "function": "fullName",
    "args": ["Supun", "Wimalasena"]
}
```

This calls a function named `fullName` with two arguments.

Named arguments:

```json
{
    "function": "fullName",
    "args": {
        "first": "Supun",
        "last": "Wimalasena"
    }
}
```

Static methods:

```json
{
    "function": "namespace\\class::method",
    "args": []
}
```