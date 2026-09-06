<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $userRole = is_string($user->role) ? $user->role : ($user->role->name ?? '');

        if (!$user || !in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki hak akses yang sesuai.'
            ], 403);
        }

        return $next($request);
    }
}
