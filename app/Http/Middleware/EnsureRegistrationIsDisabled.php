<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks all registration routes unconditionally.
 *
 * This portfolio is a single-admin application — the admin user is created
 * exclusively via `php artisan db:seed`. Public registration must never be
 * available, regardless of how many users exist in the database.
 */
class EnsureRegistrationIsDisabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort(403, 'Registration is disabled.');
    }
}
