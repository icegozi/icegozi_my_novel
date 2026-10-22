<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $bookmarksCount = $user->bookmarks()->count();
        $historiesCount = $user->readingHistories()->count();

        return view('reader.user.profile', compact('user', 'bookmarksCount', 'historiesCount'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công.');
    }

    public function settings()
    {
        $user = Auth::user();
        $preferences = session('reader_preferences', [
            'theme' => 'dark',
            'font_size' => 'base',
            'font_family' => 'serif',
            'mode' => '3d'
        ]);

        return view('reader.user.settings', compact('user', 'preferences'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:dark,sepia,light'],
            'font_size' => ['required', 'in:sm,base,lg,xl'],
            'font_family' => ['nullable', 'in:serif,sans'],
        ]);

        session(['reader_preferences' => $validated]);

        return back()->with('success', 'Cài đặt giao diện đọc đã được lưu thành công.');
    }

    public function security()
    {
        $user = Auth::user();
        return view('reader.user.security', compact('user'));
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Mật khẩu tài khoản đã được thay đổi an toàn.');
    }

    public function notifications()
    {
        $user = Auth::user();

        // Sample notifications for demonstration
        $notifications = collect([
            [
                'id' => 1,
                'title' => 'Chương mới vừa phát hành',
                'message' => 'Tác phẩm "Cửu Châu Thần Ma Lục 3D" vừa cập nhật Chương 3: Nhất Kiếm Định Phong Ba.',
                'link' => '/stories/cuu-chau-than-ma-luc-3d/read/3',
                'time' => '10 phút trước',
                'read' => false,
            ],
            [
                'id' => 2,
                'title' => 'Chào mừng đến với Novel Engine',
                'message' => 'Cảm ơn bạn đã tham gia nền tảng đọc truyện chuẩn 3D. Hãy khám phá tủ sách cá nhân và thử các chế độ đọc!',
                'link' => '/library',
                'time' => '2 ngày trước',
                'read' => true,
            ],
        ]);

        return view('reader.user.notifications', compact('user', 'notifications'));
    }
}
