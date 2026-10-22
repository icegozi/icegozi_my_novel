<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Novel3dAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Novel3dAssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected User $otherAuthor;
    protected Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->author = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Tác Giả 3D Master',
            'legal_full_name' => 'Trần Văn B',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => 'hash',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $this->otherAuthor = User::factory()->create(['role' => 'author']);
        AuthorProfile::create([
            'user_id' => $this->otherAuthor->id,
            'pen_name' => 'Người Khác',
            'legal_full_name' => 'Lê Văn C',
            'identity_number_encrypted' => 'enc2',
            'identity_hash' => 'hash_other',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $genre = Genre::create(['name' => 'Khoa Huyễn', 'slug' => 'khoa-huyen']);
        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Thần Binh Tái Thế',
            'slug' => 'than-binh-tai-the',
            'synopsis' => 'Tiểu thuyết vũ trụ...',
            'copyright_type' => 'original',
            'age_rating' => '16_plus',
            'status' => 'draft',
        ]);
    }

    public function test_author_can_view_3d_asset_gallery(): void
    {
        Novel3dAsset::create([
            'novel_id' => $this->novel->id,
            'name' => 'Hiên Viên Kiếm',
            'asset_type' => 'weapon',
            'model_file_url' => '/storage/3d-models/sword.glb',
            'scale_factor' => 1.5,
            'particle_effect' => 'divine_sparks',
        ]);

        $response = $this->actingAs($this->author)
            ->get(route('author.novels.3d-assets.index', $this->novel));

        $response->assertOk()
            ->assertSee('Hiên Viên Kiếm')
            ->assertSee('weapon');
    }

    public function test_author_can_upload_valid_glb_model_with_gltf_magic_header(): void
    {
        // glTF binary header: 0x46546C67 ("glTF" in ASCII) followed by version 2 (4 bytes) and total length (4 bytes)
        $binaryGlbContent = "glTF" . pack('V2', 2, 100) . str_repeat("\0", 88);
        $file = UploadedFile::fake()->createWithContent('excalibur.glb', $binaryGlbContent);

        $response = $this->actingAs($this->author)
            ->post(route('author.novels.3d-assets.store', $this->novel), [
                'name' => 'Thần Kiếm Excalibur',
                'asset_type' => 'weapon',
                'model_file' => $file,
                'scale_factor' => 1.25,
                'particle_effect' => 'fireflies',
                'ambient_light_color' => '#ffeedd',
                'description' => 'Mô hình kiếm thần thoại cổ đại.',
            ]);

        $response->assertRedirect(route('author.novels.3d-assets.index', $this->novel));

        $this->assertDatabaseHas('novel_3d_assets', [
            'novel_id' => $this->novel->id,
            'name' => 'Thần Kiếm Excalibur',
            'asset_type' => 'weapon',
            'particle_effect' => 'fireflies',
        ]);

        $asset = Novel3dAsset::where('name', 'Thần Kiếm Excalibur')->first();
        $this->assertNotNull($asset);
        $this->assertStringEndsWith('.glb', $asset->model_file_url);
    }

    public function test_upload_rejects_spoofed_file_without_gltf_magic_header(): void
    {
        // Fake GLB: plain text or PHP script disguised with .glb extension
        $fakeContent = "<?php echo 'malicious payload'; ?>";
        $file = UploadedFile::fake()->createWithContent('malware.glb', $fakeContent);

        $response = $this->actingAs($this->author)
            ->post(route('author.novels.3d-assets.store', $this->novel), [
                'name' => 'Giả Mạo Mô Hình',
                'asset_type' => 'beast',
                'model_file' => $file,
            ]);

        $response->assertSessionHasErrors('model_file');
        $this->assertDatabaseMissing('novel_3d_assets', [
            'name' => 'Giả Mạo Mô Hình',
        ]);
    }

    public function test_non_owner_cannot_upload_or_delete_asset(): void
    {
        $binaryGlbContent = "glTF" . pack('V2', 2, 100) . str_repeat("\0", 88);
        $file = UploadedFile::fake()->createWithContent('hack.glb', $binaryGlbContent);

        $response = $this->actingAs($this->otherAuthor)
            ->post(route('author.novels.3d-assets.store', $this->novel), [
                'name' => 'Tài Sản Trộm',
                'asset_type' => 'artifact',
                'model_file' => $file,
            ]);

        $response->assertForbidden();
    }
}
