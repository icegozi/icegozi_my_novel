<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalMediaAssetsTest extends TestCase
{
    public function test_default_cover_image_exists_and_is_accessible(): void
    {
        $this->assertFileExists(public_path('images/default_cover.webp'));
        $this->assertGreaterThan(100, filesize(public_path('images/default_cover.webp')));
    }

    public function test_genre_cover_images_exist(): void
    {
        $genres = ['tien-hiep', 'huyen-huyen', 'comic-webtoon', 'khoa-huyen', 'do-thi', 'kiem-hiep'];
        foreach ($genres as $slug) {
            $this->assertFileExists(public_path("images/covers/{$slug}.webp"));
        }
    }

    public function test_comic_panels_and_avatars_exist(): void
    {
        $this->assertFileExists(public_path('images/comics/panel-1.webp'));
        $this->assertFileExists(public_path('images/comics/panel-2.webp'));
        $this->assertFileExists(public_path('images/comics/panel-3.webp'));
        $this->assertFileExists(public_path('images/avatars/admin.webp'));
        $this->assertFileExists(public_path('images/avatars/author.webp'));
        $this->assertFileExists(public_path('images/avatars/reader.webp'));
    }
}
