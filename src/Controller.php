<?php declare(strict_types=1);

namespace Hyvor\LaravelPlaywright;

use Carbon\Carbon;
use Hyvor\LaravelPlaywright\Services\DynamicConfig;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class Controller
{

    public function artisan(Request  $request) : JsonResponse
    {

        $command = (string) $request->string('command');
        $parameters = $this->parseArtisanParameters((array) $request->input('parameters', []));

        $exitCode = Artisan::call($command, $parameters);

        return Response::json([
            'code' => $exitCode,
            'output' => Artisan::output(),
        ]);

    }

    /**
     * Parse CLI-style parameters into the format expected by Artisan::call().
     *
     * Converts:
     *   ['--option=value', '--flag', 'argument']
     * Into:
     *   ['--option' => 'value', '--flag' => true, 'argument']
     *
     * Also supports associative arrays passed directly (for backwards compatibility).
     *
     * @param array<int|string, mixed> $parameters
     * @return array<int|string, mixed>
     */
    private function parseArtisanParameters(array $parameters): array
    {
        $parsed = [];

        foreach ($parameters as $key => $value) {
            // Already an associative array entry (e.g., ['--option' => 'value'])
            if (is_string($key)) {
                $parsed[$key] = $value;
                continue;
            }

            // CLI-style string parameter
            if (is_string($value)) {
                // Option with value: --option=value
                if (str_starts_with($value, '--') && str_contains($value, '=')) {
                    [$option, $optValue] = explode('=', $value, 2);
                    $parsed[$option] = $optValue;
                }
                // Short option with value: -o=value
                elseif (str_starts_with($value, '-') && !str_starts_with($value, '--') && str_contains($value, '=')) {
                    [$option, $optValue] = explode('=', $value, 2);
                    $parsed[$option] = $optValue;
                }
                // Flag (boolean option): --flag or -f
                elseif (str_starts_with($value, '-')) {
                    $parsed[$value] = true;
                }
                // Positional argument
                else {
                    $parsed[] = $value;
                }
            } else {
                // Non-string value, keep as-is
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }

    public function truncate(Request $request) : JsonResponse
    {

        $request->validate([
            'connections' => 'nullable|array',
            'connections.*' => 'nullable|string'
        ]);

        /** @var array<string|null> $connections */
        $connections = $request->input('connections') ?? [null];

        $truncate = new Services\Truncate();
        $truncate->truncate($connections);

        return Response::json();

    }

    public function factory(Request $request) : JsonResponse
    {

        $request->validate([
            'model' => 'string|required',
            'count' => 'nullable|integer',
            'attrs' => 'array',
        ]);

        $modelClass = (string) $request->string('model');
        $count = $request->has('count') ? $request->integer('count') : null;
        /** @var array<string, mixed> $attrs */
        $attrs = (array) $request->input('attrs');

        if (!class_exists($modelClass)) {
            $modelClass = 'App\\Models\\' . $modelClass;
        }

        if (!class_exists($modelClass)) {
            abort(422, 'Model not found');
        }

        $model = app($modelClass);

        if (!$model instanceof Model) {
            abort(422, 'Model not found');
        }

        if (!method_exists($model, 'factory')) {
            abort(422, 'Model factory not found');
        }

        /** @var Factory<Model> $modelFactory */
        $modelFactory = $model->factory();

        if ($count !== null) {
            $modelFactory = $modelFactory->count($count);
        }

        $models = $modelFactory->create($attrs);

        return Response::json($models);

    }

    public function query(Request $request) : JsonResponse
    {

        $request->validate([
            'connection' => 'nullable|string',
            'query' => 'string|required',
            'bindings' => 'array',
            'unprepared' => 'boolean'
        ]);

        $connection = $request->has('connection') ?
            (string) $request->string('connection') :
            null;
        $query = (string) $request->string('query');
        /** @var array<mixed> $bindings */
        $bindings = $request->input('bindings', []);
        $unprepared = $request->boolean('unprepared');

        $connection = DB::connection($connection);
        $success = $unprepared ?
            $connection->unprepared($query) :
            $connection->statement($query, $bindings);

        return Response::json([
            'success' => $success
        ]);

    }

    public function select(Request $request) : JsonResponse
    {

        $request->validate([
            'connection' => 'nullable|string',
            'query' => 'string|required',
            'bindings' => 'array',
        ]);

        $connection = $request->has('connection') ?
            (string) $request->string('connection') :
            null;
        $query = (string) $request->string('query');
        /** @var array<mixed> $bindings */
        $bindings = $request->input('bindings', []);

        $results = DB::connection($connection)->select($query, $bindings);

        return Response::json($results);

    }

    public function function(Request  $request) : JsonResponse
    {

        $request->validate([
            'function' => 'string|required',
            'args' => 'array'
        ]);

        $function = (string) $request->string('function');
        /** @var array<mixed> $args */
        $args = $request->input('args', []);

        if (!is_callable($function))
            abort(422, 'Function does not exist');

        $response = call_user_func_array($function, $args);
        return Response::json($response);

    }

    public function dynamicConfig(Request $request) : JsonResponse
    {

        $request->validate([
            'key' => 'string|required',
            'value' => 'required',
        ]);

        $key = (string) $request->string('key');
        $value = $request->input('value');

        DynamicConfig::set($key, $value);

        return Response::json();
    }

    public function registerBootFunction(Request $request) : JsonResponse
    {

        $request->validate([
            'function' => 'string|required',
        ]);

        $function = (string) $request->string('function');

        if (!is_callable($function))
            abort(422, 'Function is not callable');

        $currentBootFunctions = DynamicConfig::get(DynamicConfig::KEY_BOOT_FUNCTIONS, []);
        assert(is_array($currentBootFunctions));
        $currentBootFunctions[] = $function;

        DynamicConfig::set(DynamicConfig::KEY_BOOT_FUNCTIONS, $currentBootFunctions);

        return Response::json();

    }

    public function travel(Request $request, DynamicConfig $dynamicConfig) : JsonResponse
    {

        $request->validate([
            'to' => 'string|required',
        ]);

        $to = (string) $request->string('to');

        try {
            Carbon::parse($to);
        } catch (\Exception $e) {
            abort(422, 'Invalid date');
        }

        DynamicConfig::set(DynamicConfig::KEY_TRAVEL, $to);

        return Response::json();

    }

    public function tearDown(DynamicConfig $dynamicConfig) : JsonResponse
    {
        $dynamicConfig->delete();

        return Response::json();
    }


}