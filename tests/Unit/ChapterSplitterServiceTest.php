<?php

namespace Tests\Unit;

use App\Services\ChapterSplitterService;
use PHPUnit\Framework\TestCase;

class ChapterSplitterServiceTest extends TestCase
{
    public function test_splits_text_with_50_consecutive_chapters_accurately(): void
    {
        $rawText = "";
        for ($i = 1; $i <= 50; $i++) {
            $rawText .= "\n\nChương {$i}: Tiêu đề chương {$i}\n";
            $rawText .= "Đây là đoạn văn thứ nhất của chương {$i}.\n";
            $rawText .= "Đây là đoạn văn thứ hai kể về cuộc hành trình của chương {$i}.\n";
        }

        $service = new ChapterSplitterService();
        $chapters = $service->split($rawText);

        $this->assertCount(50, $chapters);

        foreach ($chapters as $index => $chapter) {
            $expectedNum = $index + 1;
            $this->assertEquals($expectedNum, $chapter['chapter_number']);
            $this->assertEquals("Chương {$expectedNum}: Tiêu đề chương {$expectedNum}", $chapter['title']);
            $this->assertStringContainsString("Đây là đoạn văn thứ nhất của chương {$expectedNum}.", $chapter['content']);
            $this->assertStringContainsString("Đây là đoạn văn thứ hai kể về cuộc hành trình của chương {$expectedNum}.", $chapter['content']);
            $this->assertGreaterThan(0, $chapter['word_count']);
        }
    }

    public function test_handles_various_chapter_heading_formats(): void
    {
        $rawText = <<<TEXT
Hồi 1 - Lạc vào dị giới
Lâm Phong mở mắt ra thấy mình ở một nơi xa lạ.

Chương 2. Thức tỉnh võ hồn
Hắn nhận ra bản thân sở hữu song sinh võ hồn hiếm có.

Chapter 3: Đại náo phường thị
Một cuộc ẩu đả nhỏ đã diễn ra tại khu chợ.
TEXT;

        $service = new ChapterSplitterService();
        $chapters = $service->split($rawText);

        $this->assertCount(3, $chapters);
        $this->assertEquals(1, $chapters[0]['chapter_number']);
        $this->assertStringContainsString('Hồi 1', $chapters[0]['title']);
        $this->assertStringContainsString('Lâm Phong mở mắt ra', $chapters[0]['content']);

        $this->assertEquals(2, $chapters[1]['chapter_number']);
        $this->assertStringContainsString('Chương 2', $chapters[1]['title']);

        $this->assertEquals(3, $chapters[2]['chapter_number']);
        $this->assertStringContainsString('Chapter 3', $chapters[2]['title']);
    }

    public function test_handles_empty_or_single_content_gracefully(): void
    {
        $service = new ChapterSplitterService();

        $emptyResult = $service->split("");
        $this->assertEmpty($emptyResult);

        $noHeaderResult = $service->split("Toàn bộ văn bản không có tiêu đề chương cụ thể.");
        $this->assertCount(1, $noHeaderResult);
        $this->assertEquals(1, $noHeaderResult[0]['chapter_number']);
    }
}
