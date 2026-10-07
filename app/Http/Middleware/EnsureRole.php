<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /** يسمح بالدخول للمدير أو لأي دور من الأدوار المحددة */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        abort_unless($request->user()?->can_access(...$roles), 403, 'مش مسموحلك تدخل الصفحة دي.');

        return $next($request);
    }
}
