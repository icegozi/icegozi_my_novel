<?php

namespace Tests\Unit;

use App\Jobs\ScanSensitiveContentJob;
use App\Models\AuditLog;
use App\Models\Chapter;
use App\Models\ContentFilter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use App\Services\ContentFilterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanSensitiveContentJobTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        $genre = Genre::create(['name' => 'Kỳ Ảo', 'slug' => 'ky-ao']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Tiên Giới Truyền Kỳ',
            'slug' => 'tien-gioi-truyen-ky',
            'synopsis' => 'Một câu chuyện huyền ảo.',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'draft',
        ]);
    }

    public function test_content_filter_service_detects_prohibited_keywords_with_accent_normalization(): void
    {
        ContentFilter::create([
            'pattern' => 'lật đổ chính quyền',
            'category' => 'politics',
            'severity' => 'auto_block',
        ]);

        ContentFilter::create([
            'pattern' => 'đánh bạc ăn tiền',
            'category' => 'fraud',
            'severity' => 'flag_for_review',
        ]);

        $service = new ContentFilterService();

        // Test with exact accented text
        $result1 = $service->scan('Trong chương này có âm mưu lật đổ chính quyền nhà vua.');
        $this->assertTrue($result1['has_violation']);
        $this->assertEquals('auto_block', $result1['max_severity']);
        $this->assertCount(1, $result1['violations']);

        // Test with unaccented text (bypass evasion)
        $result2 = $service->scan('bon ho dang danh bac an tien o goc pho.');
        $this->assertTrue($result2['has_violation']);
        $this->assertEquals('flag_for_review', $result2['max_severity']);

        // Test with clean content
        $result3 = $service->scan('Nhân vật chính bước vào mật thất tu luyện công pháp.');
        $this->assertFalse($result3['has_violation']);
        $this->assertEmpty($result3['violations']);
    }

    public function test_content_filter_service_prevents_redos_catastrophic_backtracking(): void
    {
        // Malicious or vulnerable regex test
        ContentFilter::create([
            'pattern' => '(a+)+$', // Classic ReDoS pattern
            'category' => 'vulgarity',
            'severity' => 'flag_for_review',
        ]);

        $service = new ContentFilterService();
        $evilInput = str_repeat('a', 30) . '!';

        $startTime = microtime(true);
        $result = $service->scan($evilInput);
        $duration = microtime(true) - $startTime;

        // Must terminate safely and quickly (under 0.5s), avoiding infinite hang
        $this->assertLessThan(0.5, $duration, 'Regex scan took too long, vulnerable to ReDoS');
    }

    public function test_scan_sensitive_content_job_flags_chapter_for_review_or_block(): void
    {
        ContentFilter::create([
            'pattern' => 'tuyên truyền đồi trụy',
            'category' => 'vulgarity',
            'severity' => 'auto_block',
        ]);

        $chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Khởi đầu hiểm độc',
            'slug' => 'chuong-1-khoi-dau-hiem-doc',
            'content' => 'Nội dung chứa đoạn tuyên truyền đồi trụy không thể chấp nhận.',
            'word_count' => 10,
            'status' => 'draft',
            'revision' => 1,
        ]);

        $service = new ContentFilterService();
        $job = new ScanSensitiveContentJob($chapter);
        $job->handle($service);

        $chapter->refresh();
        $this->assertEquals('rejected', $chapter->status);

        // Verify audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'content_filter_auto_blocked',
            'target_type' => 'Chapter',
            'target_id' => $chapter->id,
        ]);
    }

    public function test_scan_sensitive_content_job_marks_clean_chapter_without_penalties(): void
    {
        ContentFilter::create([
            'pattern' => 'từ khóa cấm cực đoan',
            'category' => 'politics',
            'severity' => 'auto_block',
        ]);

        $chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 2,
            'title' => 'Chương 2: Tu luyện bình thường',
            'slug' => 'chuong-2-tu-luyen-binh-thuong',
            'content' => 'Nội dung chương rất trong sạch và tuân thủ thuần phong mỹ tục.',
            'word_count' => 12,
            'status' => 'draft',
            'revision' => 1,
        ]);

        $service = new ContentFilterService();
        $job = new ScanSensitiveContentJob($chapter);
        $job->handle($service);

        $chapter->refresh();
        $this->assertEquals('draft', $chapter->status);
    }
}
