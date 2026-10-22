<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest('/login');
        }

        if (!in_array($user->role, ['admin', 'moderator'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bạn không có quyền truy cập khu vực kiểm duyệt.'], 403);
            }
            abort(403, 'Bạn không có quyền truy cập khu vực kiểm duyệt.');
        }

        return $next($request);
    }
}
