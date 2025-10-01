<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();


    Auth::viaRequest('username', function (Request $request) {
    $credentials = ['username' => $request->input('username'), 'password' => $request->input('password')];
    $user = Auth::getProvider()->retrieveByCredentials($credentials);

    if ($user && Auth::getProvider()->validateCredentials($user, $credentials)) {
        return $user;
    }

    return null;
});
