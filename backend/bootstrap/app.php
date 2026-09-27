<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: ['api/*']);
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // [CUMPLIMIENTO DE PRUEBA TÉCNICA - PASO 9]
        // Sobrescribimos el renderizador de excepciones para garantizar que la API
        // JAMÁS devuelva un stack trace en HTML. Todo error se transforma en JSON estricto.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Detección dinámica de idioma mediante la cabecera HTTP
                $idioma = 'es';
                if ($request->hasHeader('Accept-Language') && str_starts_with($request->header('Accept-Language'), 'de')) {
                    $idioma = 'de';
                }

                // Generamos un trace_id único (UUID) para facilitar la trazabilidad en logs
                $traceId = (string) Str::uuid();
                $statusCode = 500;
                $code = 'INTERNAL_ERROR';
                
                // Diccionarios de traducción para respuestas dinámicas
                $msgEs = [
                    'INTERNAL_ERROR' => 'Error interno del servidor',
                    'VALIDATION_ERROR' => 'Hay campos con errores.',
                    'NOT_FOUND' => 'El recurso solicitado no existe.',
                    'AUTH_TOKEN_MISSING' => 'Se requiere autenticación.',
                    'TOO_MANY_ATTEMPTS' => 'Has excedido el límite de peticiones.'
                ];
                
                $msgDe = [
                    'INTERNAL_ERROR' => 'Interner Serverfehler',
                    'VALIDATION_ERROR' => 'Es gibt Felder mit Fehlern.',
                    'NOT_FOUND' => 'Die angeforderte Ressource existiert nicht.',
                    'AUTH_TOKEN_MISSING' => 'Authentifizierung erforderlich.',
                    'TOO_MANY_ATTEMPTS' => 'Sie haben das Limit für Anfragen überschritten.'
                ];

                $msgs = $idioma === 'de' ? $msgDe : $msgEs;
                $message = $msgs['INTERNAL_ERROR'];
                $details = [];

                // Mapeo de Excepciones del Framework a Códigos de Negocio Estándar
                if ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $code = 'VALIDATION_ERROR';
                    $message = $msgs['VALIDATION_ERROR'];
                    foreach ($e->errors() as $field => $fieldMessages) {
                        $details[] = ['field' => $field, 'message' => $fieldMessages[0]];
                    }
                } elseif ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                    $code = 'NOT_FOUND';
                    $message = $msgs['NOT_FOUND'];
                } elseif ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $code = 'AUTH_TOKEN_MISSING';
                    $message = $msgs['AUTH_TOKEN_MISSING'];
                } elseif ($e instanceof ThrottleRequestsException) {
                    $statusCode = 429;
                    $code = 'TOO_MANY_ATTEMPTS';
                    $message = $msgs['TOO_MANY_ATTEMPTS'];
                } else {
                    $message = $e->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $code,
                        'message' => $message,
                        'details' => $details
                    ],
                    'trace_id' => $traceId
                ], $statusCode);
            }
        });
    })->create();