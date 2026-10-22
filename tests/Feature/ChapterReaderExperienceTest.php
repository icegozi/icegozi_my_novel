<?php

namespace Tests\Feature;

use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterReaderExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_chapter_reader_renders_for_text_and_comic(): void
    {
        $this->seed();
        $novel = Novel::where('status', 'published')->first();
        $firstChapter = $novel->chapters()->orderBy('chapter_number')->first();

        $response = $this->get("/stories/{$novel->slug}/read/{$firstChapter->chapter_number}");
        $response->assertStatus(200);
        $response->assertSee($firstChapter->title);
        $response->assertSee('reader-controls.js', false);
        $response->assertSee('theme-sepia', false);
    }
}
