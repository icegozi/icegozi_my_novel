<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComicAndNovelShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'author')->first() ?? User::first();
        $admin = User::where('role', 'admin')->first() ?? $author;

        // Ensure genres exist
        $genres = [
            'tien-hiep' => Genre::firstOrCreate(['slug' => 'tien-hiep'], ['name' => 'Tiên Hiệp', 'description' => 'Tu chân luyện đạo, phi thăng tiên giới.']),
            'huyen-huyen' => Genre::firstOrCreate(['slug' => 'huyen-huyen'], ['name' => 'Huyền Huyễn', 'description' => 'Dị giới đại lục, ma pháp tranh phong.']),
            'comic' => Genre::firstOrCreate(['slug' => 'comic'], ['name' => 'Truyện Tranh (Webtoon)', 'description' => 'Hình ảnh cuộn dọc sắc nét phong cách manhwa, manga.']),
            'do-thi' => Genre::firstOrCreate(['slug' => 'do-thi'], ['name' => 'Đô Thị', 'description' => 'Thương trường, nhân sinh hiện đại, siêu năng lượng ngầm.']),
            'khoa-huyen' => Genre::firstOrCreate(['slug' => 'khoa-huyen'], ['name' => 'Khoa Huyễn', 'description' => 'Vũ trụ vô tận, cơ giáp không gian, tương lai viễn tưởng.']),
            'kiem-hiep' => Genre::firstOrCreate(['slug' => 'kiem-hiep'], ['name' => 'Kiếm Hiệp', 'description' => 'Ân oán giang hồ, môn phái chính tà hiệp nghĩa.']),
        ];

        // 1. Comic / Webtoon
        $comic = Novel::firstOrCreate(
            ['slug' => 'vo-than-tai-sinh-webtoon'],
            [
                'author_id' => $author->id,
                'title' => 'Võ Thần Tái Sinh (Webtoon)',
                'synopsis' => "Đỉnh phong Võ Thần bị thập đại tông môn vây hãm tại Đoạn Hồn Nhai. Sau khi tự bạo đan điền, linh hồn trùng sinh về thời niên thiếu 16 tuổi trong một gia tộc suy tàn ở biên cảnh.\nVới ký ức kiếp trước và vạn quyển tuyệt học, hắn từng bước quét ngang cường địch, thức tỉnh võ hồn cửu phẩm!",
                'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80',
                'status' => 'published',
                'age_rating' => '16_plus',
                'copyright_type' => 'original',
                'copyright_license_notes' => 'Tác phẩm truyện tranh bản quyền độc quyền Novel Engine.',
                'content_warnings' => ['violence'],
                'total_words' => 12000,
                'total_chapters' => 4,
                'view_count' => 84200,
                'bookmark_count' => 3420,
                'rating_avg' => 4.96,
                'approved_at' => now()->subDays(10),
                'approved_by' => $admin->id,
            ]
        );
        $comic->genres()->sync([$genres['comic']->id, $genres['huyen-huyen']->id]);

        // Comic Chapter 1 with vertical panels
        Chapter::firstOrCreate(
            ['novel_id' => $comic->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Trùng Sinh Đoạn Hồn Nhai (Bản Vẽ Độc Quyền)',
                'slug' => 'chuong-1-trung-sinh-doan-hon-nhai',
                'content' => '<div class="comic-stream space-y-4">
                    <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1000&q=80" alt="Panel 1 - Đoạn Hồn Nhai sấm chớp" class="w-full rounded-2xl shadow-lg border border-slate-800" loading="eager">
                    <img src="https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1000&q=80" alt="Panel 2 - Võ thần tự bạo kim đan" class="w-full rounded-2xl shadow-lg border border-slate-800" loading="lazy">
                    <img src="https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1000&q=80" alt="Panel 3 - Thức tỉnh trong thạch thất" class="w-full rounded-2xl shadow-lg border border-slate-800" loading="lazy">
                    <img src="https://images.unsplash.com/photo-1563089145-599997674d42?auto=format&fit=crop&w=1000&q=80" alt="Panel 4 - Cửu phẩm Võ Hồn bừng sáng" class="w-full rounded-2xl shadow-lg border border-slate-800" loading="lazy">
                </div>',
                'word_count' => 850,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'view_count' => 45000,
            ]
        );

        // 2. Tiên Hiệp: Vạn Cổ Chí Tôn
        $novel1 = Novel::firstOrCreate(
            ['slug' => 'van-co-chi-ton'],
            [
                'author_id' => $author->id,
                'title' => 'Vạn Cổ Chí Tôn',
                'synopsis' => "Mười lăm năm trước, Cửu Châu Phong Thần Bảng chấn động thiên địa. Diệp Thần thân là Đệ Nhất Kiếm Đế, bị tri kỷ phản bội hãm hại trong Luân Hồi Cổ Điện.\nMười lăm năm sau, linh hồn thức tỉnh trong thân xác một thiếu niên phế tài của Diệp gia. Một thanh rỉ sét cổ kiếm, một trái tim bất khuất, nghịch chuyển càn khôn tái lập trật tự vạn cổ!",
                'cover_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
                'status' => 'published',
                'age_rating' => '13_plus',
                'copyright_type' => 'original',
                'copyright_license_notes' => 'Nguyên bản tiếng Việt.',
                'total_words' => 145000,
                'total_chapters' => 24,
                'view_count' => 62100,
                'bookmark_count' => 2890,
                'rating_avg' => 4.92,
                'approved_at' => now()->subDays(20),
                'approved_by' => $admin->id,
            ]
        );
        $novel1->genres()->sync([$genres['tien-hiep']->id]);

        Chapter::firstOrCreate(
            ['novel_id' => $novel1->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Mười Lăm Năm Một Giấc Mộng',
                'slug' => 'chuong-1-muoi-lam-nam-mot-giac-mong',
                'content' => "<p>Bầu trời thành Thanh Vân phủ một màn sương mù xám xịt.</p><p>Tại hậu viện Diệp phủ, thiếu niên mở to đôi mắt, trong con ngươi lóe lên luồng tinh quang sắc lạnh như thần kiếm xuất vỏ.</p><p>‘Mười lăm năm... Cuối cùng ta cũng đã trở lại.’ Diệp Thần siết chặt nắm tay, cảm nhận từng dòng khí huyết yếu ớt chảy qua kinh mạch.</p><p>Kiếp trước hắn là Kiếm Đế vô địch thiên hạ, kiếp này dù bắt đầu từ con số không, cũng không ai có thể ngăn cản bước chân hắn bước lên đỉnh cao vạn trượng.</p>",
                'word_count' => 3120,
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'view_count' => 12400,
            ]
        );

        Chapter::firstOrCreate(
            ['novel_id' => $novel1->id, 'chapter_number' => 2],
            [
                'title' => 'Chương 2: Thần Kiếm Phá Băng',
                'slug' => 'chuong-2-than-kiem-pha-bang',
                'content' => "<p>Tiếng bước chân gấp gáp vang lên ngoài cửa phòng.</p><p>‘Diệp Thần! Mau giao Linh Dược tháng này ra đây, nếu không đừng trách bổn thiếu gia không nể tình đồng tộc!’ Một giọng nói ngạo mạn cắt đứt dòng suy nghĩ.</p><p>Diệp Thần nhếch môi nở một nụ cười lạnh lùng. Hắn đứng dậy, bước từng bước khoan thai ra sân.</p>",
                'word_count' => 2980,
                'status' => 'published',
                'published_at' => now()->subDays(6),
                'view_count' => 9800,
            ]
        );

        // 3. Huyền Huyễn: Linh Vũ Thiên Hạ
        $novel2 = Novel::firstOrCreate(
            ['slug' => 'linh-vu-thien-ha'],
            [
                'author_id' => $author->id,
                'title' => 'Linh Vũ Thiên Hạ',
                'synopsis' => 'Thế giới nơi cường giả vi tôn, Linh sư khống chế thiên địa linh khí, Vũ giả rèn luyện thân thể bất hoại. Thiếu niên mang trong mình song tu thể chất ngàn năm khó gặp, một bước đạp nát thiên khung.',
                'cover_image' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=600&q=80',
                'status' => 'published',
                'age_rating' => '16_plus',
                'copyright_type' => 'original',
                'total_words' => 98000,
                'total_chapters' => 18,
                'view_count' => 45600,
                'bookmark_count' => 1950,
                'rating_avg' => 4.88,
                'approved_at' => now()->subDays(15),
                'approved_by' => $admin->id,
            ]
        );
        $novel2->genres()->sync([$genres['huyen-huyen']->id]);

        Chapter::firstOrCreate(
            ['novel_id' => $novel2->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Dị Biến Linh Mạch',
                'slug' => 'chuong-1-di-bien-linh-mach',
                'content' => "<p>Sấm chớp rền vang khắp Cửu U Hạp Cốc.</p><p>Lục Vân thở dốc, cả người đẫm máu dựa vào vách đá dựng đứng. Đan điền truyền đến từng đợt đau đớn xé ruột xé gan.</p><p>Thế nhưng ngay vào khoảnh khắc hắn tưởng như sắp bỏ mạng, một đạo lôi đình màu lam tím xé rách mây đen giáng thẳng xuống đỉnh đầu.</p>",
                'word_count' => 3350,
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'view_count' => 8700,
            ]
        );

        // 4. Khoa Huyễn: Kỷ Nguyên Tinh Thần
        $novel3 = Novel::firstOrCreate(
            ['slug' => 'ky-nguyen-tinh-than'],
            [
                'author_id' => $author->id,
                'title' => 'Kỷ Nguyên Tinh Thần 3000',
                'synopsis' => 'Năm 3000, Trái Đất bước vào kỷ nguyên liên hành tinh. Loài người mở ra các cổng không gian đến các thiên hà xa xôi, đồng thời đánh thức tiềm năng cơ giáp lượng tử của cơ thể.',
                'cover_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=600&q=80',
                'status' => 'published',
                'age_rating' => 'all',
                'copyright_type' => 'original',
                'total_words' => 54000,
                'total_chapters' => 12,
                'view_count' => 31200,
                'bookmark_count' => 1420,
                'rating_avg' => 4.85,
                'approved_at' => now()->subDays(5),
                'approved_by' => $admin->id,
            ]
        );
        $novel3->genres()->sync([$genres['khoa-huyen']->id]);

        Chapter::firstOrCreate(
            ['novel_id' => $novel3->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Trạm Không Gian Vọng Hải',
                'slug' => 'chuong-1-tram-khong-gian-vong-hai',
                'content' => "<p>Bên ngoài cửa sổ quan sát của trạm không gian, dải Ngân Hà trải dài như một tấm lụa ánh bạc tuyệt mỹ.</p><p>Trần Vũ đeo găng tay dẫn truyền thần kinh, màn hình ba chiều trước mặt nhảy múa hàng triệu dòng mã thuật toán.</p><p>‘Báo cáo cơ trưởng, phát hiện dao động không gian dị thường ở góc phần tư số 7.’</p>",
                'word_count' => 2800,
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'view_count' => 6400,
            ]
        );

        // 5. Đô Thị: Thần Y Hạ Sơn
        $novel4 = Novel::firstOrCreate(
            ['slug' => 'than-y-ha-son'],
            [
                'author_id' => $author->id,
                'title' => 'Thần Y Hạ Sơn',
                'synopsis' => 'Thiếu niên theo sư phụ ẩn dật nơi núi sâu mười tám năm ròng, y thuật thông thiên, võ đạo trác tuyệt. Xuống núi thực hiện bảy tờ hôn ước, quấy đảo thương trường đô thị.',
                'cover_image' => 'https://images.unsplash.com/photo-1477959858617-67f30bc75b82?auto=format&fit=crop&w=600&q=80',
                'status' => 'published',
                'age_rating' => '16_plus',
                'copyright_type' => 'original',
                'total_words' => 82000,
                'total_chapters' => 16,
                'view_count' => 51000,
                'bookmark_count' => 2100,
                'rating_avg' => 4.79,
                'approved_at' => now()->subDays(3),
                'approved_by' => $admin->id,
            ]
        );
        $novel4->genres()->sync([$genres['do-thi']->id]);

        Chapter::firstOrCreate(
            ['novel_id' => $novel4->id, 'chapter_number' => 1],
            [
                'title' => 'Chương 1: Xuống Núi Thực Hiện Hôn Ước',
                'slug' => 'chuong-1-xuong-nui-thuc-hien-hon-uoc',
                'content' => "<p>Chuyến tàu hỏa từ vùng núi Tây Bắc chậm rãi tiến vào ga trung tâm thành phố Giang Nam.</p><p>Lâm Hiên khoác chiếc túi vải thô, ánh mắt tò mò nhìn ngắm những tòa cao ốc chọc trời.</p><p>Trong túi áo ngực của hắn là bảy phong thư đính kèm tín vật hôn ước do sư phụ truyền lại trước lúc xuống núi.</p>",
                'word_count' => 3100,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'view_count' => 7800,
            ]
        );
    }
}
