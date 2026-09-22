<?php

use App\Http\Middleware\TransaccionPatrimonio;
use App\Support\PermisosAccesos;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [TransaccionPatrimonio::class]);
        $middleware->redirectUsersTo(fn () => route(PermisosAccesos::rutaInicial()));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() === 419 && $request->routeIs('login.store')) {
                return redirect()->route('login')->with(
                    'error',
                    'La sesión de acceso caducó. Actualizamos el formulario; ingresa nuevamente tus datos.'
                );
            }

            return null;
        });
    })->create();
