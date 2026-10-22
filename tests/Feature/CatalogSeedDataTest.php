<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contains_novels_and_comics_with_chapters(): void
    {
        $this->seed();
        $this->assertGreaterThanOrEqual(5, Novel::where('status', 'published')->count());
        $this->assertGreaterThanOrEqual(5, Genre::count());
        $comic = Novel::where('slug', 'vo-than-tai-sinh-webtoon')->first();
        $this->assertNotNull($comic);
        $this->assertTrue($comic->chapters()->count() >= 1);
    }
}
