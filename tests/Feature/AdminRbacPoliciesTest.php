<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminRbacPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $author;
    protected User $otherAuthor;
    protected User $reader;
    protected Novel $novel;
    protected Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->author = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->otherAuthor = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Bút Danh A',
            'legal_full_name' => 'Nguyễn Văn A',
            'identity_number_encrypted' => 'encrypted_mock',
            'identity_hash' => hash('sha256', '012345678901'),
            'verification_status' => 'verified',
        ]);

        AuthorProfile::create([
            'user_id' => $this->otherAuthor->id,
            'pen_name' => 'Bút Danh B',
            'legal_full_name' => 'Trần Văn B',
            'identity_number_encrypted' => 'encrypted_mock2',
            'identity_hash' => hash('sha256', '012345678902'),
            'verification_status' => 'verified',
        ]);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Tác phẩm RBAC',
            'slug' => 'tac-pham-rbac',
            'synopsis' => 'Mô tả',
            'status' => 'published',
        ]);

        $this->chapter = Chapter::create([
            'novel_id' => $this->novel->id,
            'chapter_number' => 1.0,
            'title' => 'Chương 1',
            'slug' => 'chuong-1',
            'content' => '<p>Nội dung</p>',
            'status' => 'published',
            'revision' => 1,
        ]);
    }

    public function test_novel_policy_permissions_by_role(): void
    {
        // viewAnyAdmin: admin & moderator can, author & reader cannot
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewAnyAdmin', Novel::class));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('viewAnyAdmin', Novel::class));
        $this->assertFalse(Gate::forUser($this->author)->allows('viewAnyAdmin', Novel::class));
        $this->assertFalse(Gate::forUser($this->reader)->allows('viewAnyAdmin', Novel::class));

        // createAdmin: admin can, moderator cannot
        $this->assertTrue(Gate::forUser($this->admin)->allows('createAdmin', Novel::class));
        $this->assertFalse(Gate::forUser($this->moderator)->allows('createAdmin', Novel::class));
        $this->assertFalse(Gate::forUser($this->author)->allows('createAdmin', Novel::class));

        // updateAdmin: admin & moderator can
        $this->assertTrue(Gate::forUser($this->admin)->allows('updateAdmin', $this->novel));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('updateAdmin', $this->novel));
        $this->assertFalse(Gate::forUser($this->author)->allows('updateAdmin', $this->novel));

        // deleteAdmin: only admin can
        $this->assertTrue(Gate::forUser($this->admin)->allows('deleteAdmin', $this->novel));
        $this->assertFalse(Gate::forUser($this->moderator)->allows('deleteAdmin', $this->novel));
        $this->assertFalse(Gate::forUser($this->author)->allows('deleteAdmin', $this->novel));

        // manage (author ownership check): author owns novel -> true, other author -> false
        $this->assertTrue(Gate::forUser($this->admin)->allows('manage', $this->novel));
        $this->assertTrue(Gate::forUser($this->author)->allows('manage', $this->novel));
        $this->assertFalse(Gate::forUser($this->otherAuthor)->allows('manage', $this->novel));
        $this->assertFalse(Gate::forUser($this->reader)->allows('manage', $this->novel));
    }

    public function test_chapter_policy_permissions_by_role(): void
    {
        // viewAnyAdmin: admin & moderator can
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewAnyAdmin', Chapter::class));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('viewAnyAdmin', Chapter::class));
        $this->assertFalse(Gate::forUser($this->reader)->allows('viewAnyAdmin', Chapter::class));

        // updateAdmin: admin & moderator can
        $this->assertTrue(Gate::forUser($this->admin)->allows('updateAdmin', $this->chapter));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('updateAdmin', $this->chapter));

        // deleteAdmin: only admin can
        $this->assertTrue(Gate::forUser($this->admin)->allows('deleteAdmin', $this->chapter));
        $this->assertFalse(Gate::forUser($this->moderator)->allows('deleteAdmin', $this->chapter));

        // viewHistory: admin, moderator, and owning author can
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewHistory', $this->chapter));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('viewHistory', $this->chapter));
        $this->assertTrue(Gate::forUser($this->author)->allows('viewHistory', $this->chapter));
        $this->assertFalse(Gate::forUser($this->otherAuthor)->allows('viewHistory', $this->chapter));
        $this->assertFalse(Gate::forUser($this->reader)->allows('viewHistory', $this->chapter));

        // restoreHistory: only admin can
        $this->assertTrue(Gate::forUser($this->admin)->allows('restoreHistory', $this->chapter));
        $this->assertFalse(Gate::forUser($this->moderator)->allows('restoreHistory', $this->chapter));
        $this->assertFalse(Gate::forUser($this->author)->allows('restoreHistory', $this->chapter));
    }

    public function test_user_and_author_profile_policy_permissions(): void
    {
        $authorProfile = $this->author->authorProfile;

        // viewAny author profile: admin & moderator can
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewAny', AuthorProfile::class));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('viewAny', AuthorProfile::class));
        $this->assertFalse(Gate::forUser($this->reader)->allows('viewAny', AuthorProfile::class));

        // view specific profile: admin, mod, and self can
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $authorProfile));
        $this->assertTrue(Gate::forUser($this->moderator)->allows('view', $authorProfile));
        $this->assertTrue(Gate::forUser($this->author)->allows('view', $authorProfile));
        $this->assertFalse(Gate::forUser($this->otherAuthor)->allows('view', $authorProfile));

        // changeStatus (Ban/Suspend user): only admin can, and cannot ban self
        $this->assertTrue(Gate::forUser($this->admin)->allows('changeStatus', [$this->author]));
        $this->assertFalse(Gate::forUser($this->admin)->allows('changeStatus', [$this->admin]));
        $this->assertFalse(Gate::forUser($this->moderator)->allows('changeStatus', [$this->author]));
    }
}
