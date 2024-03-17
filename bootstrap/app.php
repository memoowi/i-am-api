<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // $exceptions->shouldRenderJsonWhen(function (Request $request) {
        //     if ($request->is('api/*')) {
        //         return true;
        //     }

        //     return $request->expectsJson();
        // });
        $exceptions->render(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null
                ], 500);
            }
            // return response()->json([
            //     'status' => 'error',
            //     'message' => $e->getMessage(),
            //     'error_details' => env('APP_DEBUG') === true ? $e->getMessage() : null
            // ], 500);
        });
    })->create();
