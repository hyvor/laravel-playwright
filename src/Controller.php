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
        $parameters = (array) $request->input('parameters');

        $exitCode = Artisan::call($command, $parameters);

        return Response::json([
            'code' => $exitCode,
            'output' => Artisan::output(),
        ]);

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