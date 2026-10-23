<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthorVerified
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

        // Admin bypass
        if ($user->role === 'admin') {
            return $next($request);
        }

        $profile = $user->authorProfile;

        if ($user->role !== 'author' || !$profile || $profile->verification_status !== 'verified') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bạn cần hoàn tất xác thực định danh tác giả (e-KYC) theo Nghị định 147/2024/NĐ-CP để truy cập tính năng này.',
                    'verification_status' => $profile?->verification_status ?? 'unregistered',
                ], 403);
            }

            return redirect('/tac-gia/dang-ky-dinh-danh')
                ->with('warning', 'Hồ sơ tác giả của bạn chưa được phê duyệt định danh e-KYC. Vui lòng hoàn tất thủ tục.');
        }

        return $next($request);
    }
}
