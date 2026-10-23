<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterAutoSaveApiTest extends TestCase
{
    use RefreshDatabase;

    private User $author;
    private Novel $novel;
    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Ngọa Long',
            'legal_full_name' => 'GIA CÁT LƯỢNG',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => hash('sha256', '001099000888'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Tam Quốc Chí 3D',
            'slug' => 'tam-quoc-chi-3d',
            'synopsis' => 'Một thiên sử thi Tam Quốc chân thực...',
            'status' => 'draft',
        ]);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1,
            'title' => 'Chương 1: Đào Viên Kết Nghĩa',
            'slug' => 'chuong-1-dao-vien-ket-nghia',
            'content' => '<p>Ba vị hào kiệt gặp nhau tại vườn đào.</p>',
            'word_count' => 10,
            'revision' => 1,
            'status' => 'draft',
        ]);
    }

    public function test_autosave_successfully_updates_content_and_sanitizes_xss(): void
    {
        $payload = [
            'chapter_id' => $this->chapter->id,
            'title' => 'Chương 1: Đào Viên Kết Nghĩa (Đã sửa)',
            'content' => '<p>Nội dung hợp lệ</p><script>alert("hacked")</script><img src="x" onerror="alert(1)">',
            'revision' => 2,
            'client_uuid' => 'client-tab-1',
        ];

        $response = $this->actingAs($this->author)->postJson('/api/author/chapters/autosave', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'saved',
            'current_revision' => 2,
        ]);

        $this->chapter->refresh();
        $this->assertEquals(2, $this->chapter->revision);
        $this->assertEquals('client-tab-1', $this->chapter->client_uuid);
        $this->assertStringNotContainsString('<script>', $this->chapter->content);
        $this->assertStringNotContainsString('onerror', $this->chapter->content);
        $this->assertStringContainsString('Nội dung hợp lệ', $this->chapter->content);
        $this->assertGreaterThan(0, $this->chapter->word_count);
    }

    public function test_autosave_rejects_stale_revision_with_conflict_warning(): void
    {
        // Advance chapter revision in database to 5
        $this->chapter->update(['revision' => 5, 'content' => '<p>Bản ghi mới nhất trên server</p>']);

        // Stale client sends revision 3
        $stalePayload = [
            'chapter_id' => $this->chapter->id,
            'title' => 'Chương 1',
            'content' => '<p>Nội dung cũ từ tab khác</p>',
            'revision' => 3,
            'client_uuid' => 'client-tab-old',
        ];

        $response = $this->actingAs($this->author)->postJson('/api/author/chapters/autosave', $stalePayload);

        $response->assertStatus(409);
        $response->assertJson([
            'status' => 'conflict',
            'current_revision' => 5,
        ]);
        $response->assertSee('Bản ghi mới nhất trên server');

        // Database remains unchanged
        $this->chapter->refresh();
        $this->assertEquals(5, $this->chapter->revision);
        $this->assertStringContainsString('Bản ghi mới nhất trên server', $this->chapter->content);
    }

    public function test_author_can_access_zen_editor_interface(): void
    {
        $response = $this->actingAs($this->author)->get("/tac-gia/truyen/{$this->novel->id}/chuong/{$this->chapter->id}/soan-thao");
        $response->assertStatus(200);
        $response->assertSee('Đào Viên Kết Nghĩa');
        $response->assertSee('Zen Mode');
    }
}
