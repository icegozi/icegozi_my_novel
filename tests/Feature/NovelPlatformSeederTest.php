<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use Database\Seeders\NovelPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NovelPlatformSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_novel_platform_seeder_populates_core_records(): void
    {
        $this->seed(NovelPlatformSeeder::class);

        // Admin account
        $admin = User::where('email', 'admin@novel3d.vn')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->role);

        // Author account with verified e-KYC
        $author = User::where('email', 'author@novel3d.vn')->first();
        $this->assertNotNull($author);
        $this->assertEquals('author', $author->role);
        $this->assertNotNull($author->authorProfile);
        $this->assertEquals('verified', $author->authorProfile->verification_status);

        // Standard genres
        $genres = Genre::all();
        $this->assertGreaterThanOrEqual(5, $genres->count());
        $this->assertTrue($genres->contains('slug', 'tien-hiep'));
        $this->assertTrue($genres->contains('slug', 'kiem-hiep'));
        $this->assertTrue($genres->contains('slug', 'huyen-huyen'));
        $this->assertTrue($genres->contains('slug', 'khoa-huyen'));
        $this->assertTrue($genres->contains('slug', 'do-thi'));

        // Sample novel with at least 3 chapters and 1 3D asset
        $novel = Novel::where('author_id', $author->id)->first();
        $this->assertNotNull($novel);
        $this->assertGreaterThanOrEqual(3, $novel->chapters()->count());
        $this->assertGreaterThanOrEqual(1, $novel->assets3d()->count());

        $firstChapter = $novel->chapters()->where('chapter_number', 1)->first();
        $this->assertNotNull($firstChapter);
        $this->assertNotEmpty($firstChapter->projections3d);
    }
}
