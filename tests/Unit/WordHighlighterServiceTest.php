<?php

namespace Tests\Unit;

use App\Models\ContentFilter;
use App\Services\WordHighlighterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordHighlighterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WordHighlighterService $highlighter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->highlighter = new WordHighlighterService();
    }

    public function test_highlights_forbidden_words_in_text(): void
    {
        $html = '<p>Đây là nội dung chứa từ cấm và hành vi bạo lực nguy hiểm.</p>';
        $words = ['từ cấm', 'bạo lực'];

        $result = $this->highlighter->highlight($html, $words);

        $this->assertStringContainsString('<mark class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50" title="Từ khóa nhạy cảm">từ cấm</mark>', $result);
        $this->assertStringContainsString('<mark class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50" title="Từ khóa nhạy cảm">bạo lực</mark>', $result);
    }

    public function test_does_not_corrupt_html_tags_or_attributes(): void
    {
        $html = '<div class="tu-cam-box"><img src="/images/tu-cam.png" alt="tu-cam avatar" /><p>Nội dung có chứa từ cấm thực sự.</p></div>';
        $words = ['tu-cam', 'từ cấm'];

        $result = $this->highlighter->highlight($html, $words);

        // Tags and attributes should remain untouched
        $this->assertStringContainsString('class="tu-cam-box"', $result);
        $this->assertStringContainsString('src="/images/tu-cam.png"', $result);
        $this->assertStringContainsString('alt="tu-cam avatar"', $result);

        // Only text content is highlighted
        $this->assertStringContainsString('<mark class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50" title="Từ khóa nhạy cảm">từ cấm</mark>', $result);
    }

    public function test_case_insensitive_matching(): void
    {
        $html = '<p>Phát hiện TỪ CẤM viết hoa và từ cấm viết thường.</p>';
        $words = ['từ cấm'];

        $result = $this->highlighter->highlight($html, $words);

        $this->assertStringContainsString('>TỪ CẤM</mark>', $result);
        $this->assertStringContainsString('>từ cấm</mark>', $result);
    }

    public function test_prevents_redos_catastrophic_backtracking(): void
    {
        $html = '<p>' . str_repeat('a', 500) . '!</p>';
        $unsafePatterns = ['(a+)+b'];

        $start = microtime(true);
        $result = $this->highlighter->highlight($html, $unsafePatterns);
        $elapsed = microtime(true) - $start;

        // Must finish under 0.2s without hanging
        $this->assertLessThan(0.2, $elapsed);
        $this->assertIsString($result);
    }

    public function test_highlights_using_database_filters_by_default(): void
    {
        ContentFilter::create([
            'pattern' => 'ma túy',
            'category' => 'vulgarity',
            'severity' => 'auto_block',
        ]);

        $html = '<p>Hành vi tàng trữ ma túy là phạm pháp.</p>';

        $result = $this->highlighter->highlight($html);

        $this->assertStringContainsString('<mark class="bg-rose-500/30 text-rose-300 font-semibold px-1 rounded border border-rose-500/50" title="Từ khóa nhạy cảm">ma túy</mark>', $result);
    }
}
