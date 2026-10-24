<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\ImportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminImportWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $moderator;
    protected User $reader;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->moderator = User::factory()->create(['role' => 'moderator', 'status' => 'active']);
        $this->reader = User::factory()->create(['role' => 'reader', 'status' => 'active']);

        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Kỳ Dị Cư Sĩ',
            'legal_full_name' => 'Vũ Kỳ Dị',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678444'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        Genre::create(['name' => 'Huyền Huyễn', 'slug' => 'huyen-huyen']);
    }

    public function test_only_admin_can_access_import_wizard(): void
    {
        $this->actingAs($this->reader)->get('/admin/du-lieu/nhap-du-lieu')->assertStatus(403);
        $this->actingAs($this->moderator)->get('/admin/du-lieu/nhap-du-lieu')->assertStatus(403);

        $response = $this->actingAs($this->admin)->get('/admin/du-lieu/nhap-du-lieu');
        $response->assertStatus(200);
        $response->assertSee('Nhập dữ liệu', false);
        $response->assertSee('Dry-Run', false);
    }

    public function test_admin_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/du-lieu/mau-import/novels');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('tieu_de,but_danh_tac_gia', $response->streamedContent());
    }

    public function test_upload_and_dry_run_validation_identifies_errors(): void
    {
        // CSV with invalid author pen name
        $content = "\xEF\xBB\xBF" . "tieu_de,but_danh_tac_gia,the_loai,tom_tat,do_tuoi,ban_quyen,tu_khoa_seo,meta_description\n" .
            "Truyện Lỗi,TacGiaKhongTonTai,Huyền Huyễn,Tom tat,all,original,tag,desc\n";

        $file = UploadedFile::fake()->createWithContent('invalid_novels.csv', $content);

        $response = $this->actingAs($this->admin)->post('/admin/du-lieu/tai-len', [
            'type' => 'novels',
            'file' => $file,
        ]);

        $job = ImportJob::latest()->first();
        $this->assertNotNull($job);
        $response->assertRedirect("/admin/du-lieu/xem-truoc/{$job->id}");

        $previewResponse = $this->actingAs($this->admin)->get("/admin/du-lieu/xem-truoc/{$job->id}");
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('TacGiaKhongTonTai', false);

        // Download error report CSV
        $errorReportResponse = $this->actingAs($this->admin)->get("/admin/du-lieu/bao-cao-loi/{$job->id}");
        $errorReportResponse->assertStatus(200);
        $this->assertStringContainsString('TacGiaKhongTonTai', $errorReportResponse->streamedContent());
    }

    public function test_upload_valid_csv_and_commit_to_draft(): void
    {
        $content = "\xEF\xBB\xBF" . "tieu_de,but_danh_tac_gia,the_loai,tom_tat,do_tuoi,ban_quyen,tu_khoa_seo,meta_description\n" .
            "Cửu Châu Phiêu Miểu Lục,Kỳ Dị Cư Sĩ,Huyền Huyễn,Hùng tráng bi ca,all,original,cuu-chau,Mo ta\n";

        $file = UploadedFile::fake()->createWithContent('valid_novels.csv', $content);

        $uploadResponse = $this->actingAs($this->admin)->post('/admin/du-lieu/tai-len', [
            'type' => 'novels',
            'file' => $file,
        ]);

        $job = ImportJob::latest()->first();
        $uploadResponse->assertRedirect("/admin/du-lieu/xem-truoc/{$job->id}");

        $this->assertEquals('validated', $job->status);

        // Commit to database
        $commitResponse = $this->actingAs($this->admin)->post("/admin/du-lieu/xac-nhan-nap/{$job->id}");
        $commitResponse->assertRedirect('/admin/du-lieu/nhap-du-lieu');

        $this->assertDatabaseHas('novels', [
            'title' => 'Cửu Châu Phiêu Miểu Lục',
            'author_id' => $this->authorUser->id,
            'status' => 'draft',
        ]);

        $this->assertEquals('completed', $job->fresh()->status);
    }
}
