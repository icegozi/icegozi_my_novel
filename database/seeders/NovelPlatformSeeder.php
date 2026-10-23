<?php

namespace Database\Seeders;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Chapter3dProjection;
use App\Models\Genre;
use App\Models\LegalConsent;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use App\Services\IdentityEncryptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NovelPlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cryptoService = new IdentityEncryptionService();

        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@novel3d.vn'],
            [
                'name' => 'Tổng Biên Tập & Quản Trị Viên',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 2. Verified Author Account
        $author = User::firstOrCreate(
            ['email' => 'author@novel3d.vn'],
            [
                'name' => 'Tác Giả Tiên Phong',
                'password' => Hash::make('password123'),
                'phone' => '0988888888',
                'phone_verified_at' => now(),
                'role' => 'author',
                'status' => 'active',
            ]
        );

        // Author Profile & e-KYC
        $cryptoData = $cryptoService->encryptAndHash('001099001234');
        AuthorProfile::firstOrCreate(
            ['user_id' => $author->id],
            [
                'pen_name' => 'Cổ Kim Mộng',
                'legal_full_name' => 'TRẦN VĂN MINH',
                'identity_number_encrypted' => $cryptoData['encrypted'],
                'identity_hash' => $cryptoData['hash'],
                'identity_issued_date' => '2022-05-20',
                'identity_front_image' => 'private/identity_documents/demo_front.webp',
                'identity_back_image' => 'private/identity_documents/demo_back.webp',
                'tax_code' => '8412345678',
                'bank_account_number' => '1029384756',
                'bank_name' => 'Vietcombank',
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]
        );

        // Digital Legal Consents
        LegalConsent::firstOrCreate(
            ['user_id' => $author->id, 'policy_type' => 'author_copyright_agreement'],
            [
                'policy_version' => 'v1.0-2026',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Seed Generator',
                'signed_at' => now(),
            ]
        );

        LegalConsent::firstOrCreate(
            ['user_id' => $author->id, 'policy_type' => 'terms_of_service'],
            [
                'policy_version' => 'v1.0-2026',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'System Seed Generator',
                'signed_at' => now(),
            ]
        );

        // 3. Core Genres
        $genresData = [
            ['name' => 'Tiên Hiệp', 'slug' => 'tien-hiep', 'description' => 'Tu chân luyện đạo, phi thăng tiên giới, nghịch thiên cải mệnh.'],
            ['name' => 'Kiếm Hiệp', 'slug' => 'kiem-hiep', 'description' => 'Ân oán giang hồ, môn phái tranh phong, hiệp nghĩa nhân đạo.'],
            ['name' => 'Huyền Huyễn', 'slug' => 'huyen-huyen', 'description' => 'Thế giới huyền bí ma pháp, dị giới đại lục, vạn tộc tranh bá.'],
            ['name' => 'Khoa Huyễn', 'slug' => 'khoa-huyen', 'description' => 'Vũ trụ vô tận, công nghệ tương lai, cơ giáp và không gian đa chiều.'],
            ['name' => 'Đô Thị', 'slug' => 'do-thi', 'description' => 'Cuộc sống hiện đại, thương trường, y thuật, võ thuật ngầm giữa phố thị.'],
        ];

        $createdGenres = [];
        foreach ($genresData as $g) {
            $createdGenres[] = Genre::firstOrCreate(['slug' => $g['slug']], $g);
        }

        // 4. Sample Novel with 3D Visual Assets
        $novel = Novel::firstOrCreate(
            ['slug' => 'cuu-chau-than-ma-luc-3d'],
            [
                'author_id' => $author->id,
                'title' => 'Cửu Châu Thần Ma Lục 3D',
                'synopsis' => 'Kiệt tác tu chân với hệ thống hình chiếu 3D sống động bám sát từng đoạn văn khi độc giả cuộn màn hình.',
                'cover_image' => '/images/covers/tien-hiep.webp',
                'status' => 'published',
                'age_rating' => '16_plus',
                'copyright_type' => 'original',
                'copyright_license_notes' => 'Tác phẩm sáng tạo nguyên bản của tác giả Cổ Kim Mộng.',
                'content_warnings' => ['violence'],
                'total_words' => 9500,
                'total_chapters' => 3,
                'view_count' => 1250,
                'bookmark_count' => 88,
                'rating_avg' => 4.95,
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]
        );

        $novel->genres()->sync(collect($createdGenres)->pluck('id'));

        // 5. 3D Model Asset
        $asset = Novel3dAsset::firstOrCreate(
            ['novel_id' => $novel->id, 'name' => 'Hiên Viên Cổ Kiếm'],
            [
                'asset_type' => 'weapon',
                'model_file_url' => 'assets3d/hien_vien_kiem.glb',
                'thumbnail_url' => 'assets3d/hien_vien_thumb.webp',
                'scale_factor' => 1.25,
                'camera_init_position' => ['x' => 0, 'y' => 1.5, 'z' => 4.0],
                'ambient_light_color' => '#f59e0b',
                'particle_effect' => 'divine_sparks',
                'sound_ambient_url' => 'audio/sword_hum.mp3',
                'description' => 'Thượng cổ thần kiếm ẩn chứa chí tôn thần uy, chuôi khảm thất tinh nhật nguyệt.',
                'is_private' => false,
            ]
        );

        // 6. Chapters & 3D Projections
        $chapter1 = Chapter::firstOrCreate(
            ['novel_id' => $novel->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Thái Cổ Táng Kiếm Trũng',
                'slug' => 'chuong-1-thai-co-tang-kiem-trung',
                'content' => "<p>Gió lốc gầm thét trên đỉnh Kiếm Vực hoang tàn.</p><p>Đột nhiên, từ sâu trong lòng đất, một luồng ánh sáng vàng kim xé toạc màn đêm. Đó chính là Hiên Viên Cổ Kiếm, thanh thần binh đã ngủ yên mười vạn năm.</p><p>Lâm Phong bước tới, bàn tay nắm chặt lấy chuôi kiếm, luồng linh lực cuồn cuộn tràn vào kinh mạch.</p>",
                'word_count' => 3200,
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'view_count' => 640,
            ]
        );

        Chapter3dProjection::firstOrCreate(
            ['chapter_id' => $chapter1->id, 'asset_3d_id' => $asset->id],
            [
                'paragraph_index' => 1,
                'trigger_keyword' => 'Hiên Viên Cổ Kiếm',
                'hologram_title' => 'Thần Binh Xuất Thế: Hiên Viên Cổ Kiếm',
                'auto_rotate' => true,
                'display_mode' => 'split_stage',
            ]
        );

        Chapter::firstOrCreate(
            ['novel_id' => $novel->id, 'chapter_number' => 2],
            [
                'title' => 'Chương 2: Ngưng Tụ Chí Tôn Đan Điền',
                'slug' => 'chuong-2-ngung-tu-chi-ton-dan-dien',
                'content' => "<p>Sau khi thu phục được cổ kiếm, Lâm Phong tìm một thạch động bí mật để đả tọa.</p><p>Hơi thở điều hòa theo quy luật nhật nguyệt, kim sắc kiếm khí bắt đầu rèn giũa lại toàn bộ tủy cốt.</p>",
                'word_count' => 3100,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'view_count' => 420,
            ]
        );

        Chapter::firstOrCreate(
            ['novel_id' => $novel->id, 'chapter_number' => 3],
            [
                'title' => 'Chương 3: Nhất Kiếm Định Phong Ba',
                'slug' => 'chuong-3-nhat-kiem-dinh-phong-ba',
                'content' => "<p>Ngoài sơn cốc, tiếng gầm của ma thú rung chuyển đất trời.</p><p>Lâm Phong mở mắt, ánh nhìn sắc bén tựa kiếm phong. Trận chiến sinh tử thực sự giờ mới bắt đầu.</p>",
                'word_count' => 3200,
                'status' => 'published',
                'published_at' => now(),
                'view_count' => 190,
            ]
        );
    }
}
