<?php

namespace Tests\Feature;

use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryDetailRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_detail_renders_at_both_stories_and_truyen_slugs(): void
    {
        $this->seed();
        $novel = Novel::where('status', 'published')->first();

        $this->get('/stories/' . $novel->slug)
            ->assertStatus(200)
            ->assertSee($novel->title)
            ->assertSee('Danh Sách Chương');

        $this->get('/truyen/' . $novel->slug)
            ->assertStatus(200)
            ->assertSee($novel->title);
    }
}
