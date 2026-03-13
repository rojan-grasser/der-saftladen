<?php

use App\Http\Middleware\EnsureInstructorHasAccess;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleRequestLogging;
use App\Http\Middleware\RoleMiddleware;
use Dotenv\Dotenv;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpFoundation\Request;

if (file_exists(dirname(__DIR__).'/.env.s3')) {
    Dotenv::createMutable(dirname(__DIR__), '.env.s3')->load();
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'request-logging' => HandleRequestLogging::class,
            'instructor-has-access' => EnsureInstructorHasAccess::class,
            'role' => RoleMiddleware::class,
        ]);
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
