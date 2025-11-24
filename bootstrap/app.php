<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\IdentifyTenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render JSON responses for API requests
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = 500;
                $response = [
                    'message' => 'Server Error',
                ];

                // Validation errors
                if ($e instanceof ValidationException) {
                    $status = 422;
                    $response = [
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ];
                }
                // HTTP exceptions (404, 403, etc.)
                elseif ($e instanceof HttpException) {
                    $status = $e->getStatusCode();
                    $response['message'] = $e->getMessage() ?: 'HTTP Error';
                }
                // All other exceptions
                else {
                    $response['message'] = $e->getMessage() ?: 'Server Error';
                }

                // Include debug info when APP_DEBUG is true
                if (env('APP_DEBUG')) {
                    $response['debug'] = [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->take(5)->map(fn ($t) => [
                            'file' => $t['file'] ?? null,
                            'line' => $t['line'] ?? null,
                            'function' => ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''),
                        ])->toArray(),
                    ];
                }

                return response()->json($response, $status);
            }
        });
    })->create();
