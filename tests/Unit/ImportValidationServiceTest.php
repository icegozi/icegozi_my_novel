<?php

namespace Tests\Unit;

use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\ImportJob;
use App\Models\Novel;
use App\Models\User;
use App\Services\ImportValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ImportValidationService $service;
    protected User $authorUser;
    protected AuthorProfile $authorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ImportValidationService::class);

        $this->authorUser = User::factory()->create(['role' => 'author', 'status' => 'active']);
        $this->authorProfile = AuthorProfile::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Ngọa Hổ',
            'legal_full_name' => 'Lê Ngọa Hổ',
            'identity_number_encrypted' => 'enc123',
            'identity_hash' => hash('sha256', '012345678333'),
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        Genre::create(['name' => 'Kiếm Hiệp', 'slug' => 'kiem-hiep']);
    }

    public function test_generates_valid_csv_templates_with_utf8_bom(): void
    {
        $novelTemplate = $this->service->generateTemplate('novels');
        // UTF-8 BOM is \xEF\xBB\xBF
        $this->assertStringStartsWith("\xEF\xBB\xBF", $novelTemplate);
        $this->assertStringContainsString('tieu_de,but_danh_tac_gia,the_loai,tom_tat', $novelTemplate);

        $chapterTemplate = $this->service->generateTemplate('chapters');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $chapterTemplate);
        $this->assertStringContainsString('slug_truyen,so_chuong,tieu_de_chuong,noi_dung', $chapterTemplate);
    }

    public function test_validates_csv_with_missing_headers(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, "cot_sai_1,cot_sai_2\ngia_tri_1,gia_tri_2\n");

        $result = $this->service->validateCsv($tempFile, 'novels');
        unlink($tempFile);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertEquals('header', $result['errors'][0]['column']);
    }

    public function test_detects_non_existent_author_pen_name(): void
    {
        $csvContent = "\xEF\xBB\xBF" . "tieu_de,but_danh_tac_gia,the_loai,tom_tat,do_tuoi,ban_quyen,tu_khoa_seo,meta_description\n" .
            "Truyện Mới Lạ,TacGiaAo,Kiếm Hiệp,Tóm tắt truyện ngắn,all,original,kiem-hiep,Mo ta\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->validateCsv($tempFile, 'novels');
        unlink($tempFile);

        $this->assertFalse($result['valid']);
        $this->assertEquals('but_danh_tac_gia', $result['errors'][0]['column']);
        $this->assertStringContainsString('TacGiaAo', $result['errors'][0]['message']);
    }

    public function test_passes_valid_rows_and_commits_import_as_draft(): void
    {
        $csvContent = "\xEF\xBB\xBF" . "tieu_de,but_danh_tac_gia,the_loai,tom_tat,do_tuoi,ban_quyen,tu_khoa_seo,meta_description\n" .
            "Long Hổ Phong Vân,Ngọa Hổ,Kiếm Hiệp,Chuyện về long tranh hổ đấu giang hồ,all,original,kiem-hiep,Mo ta SEO\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->validateCsv($tempFile, 'novels');
        unlink($tempFile);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertCount(1, $result['parsed_data']);

        // Commit import job
        $admin = User::factory()->create(['role' => 'admin']);
        $job = ImportJob::create([
            'user_id' => $admin->id,
            'type' => 'novels',
            'file_path' => 'imports/test.csv',
            'original_filename' => 'test.csv',
            'status' => 'validated',
            'total_rows' => 1,
            'idempotency_key' => 'idemp_' . uniqid(),
        ]);

        $importedCount = $this->service->commitImport($job, $result['parsed_data'], $admin);

        $this->assertEquals(1, $importedCount);
        $this->assertDatabaseHas('novels', [
            'title' => 'Long Hổ Phong Vân',
            'author_id' => $this->authorUser->id,
            'status' => 'draft', // Always draft! Never auto published
        ]);

        $this->assertEquals('completed', $job->fresh()->status);
        $this->assertEquals(1, $job->fresh()->successful_rows);
    }
}
