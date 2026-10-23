<?php

namespace Tests\Feature;

use App\Mail\AuthorKycResultMail;
use App\Mail\NovelTakedownNoticeMail;
use App\Models\AuthorProfile;
use App\Models\Genre;
use App\Models\Novel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LegalNotificationMailTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $author;
    protected Novel $novel;
    protected AuthorProfile $profile;
    protected Report $report;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.identity_pepper' => 'test-pepper-secret-32-chars-long!']);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->author = User::factory()->create(['role' => 'author', 'email' => 'author@novelplatform.vn']);

        $this->profile = AuthorProfile::create([
            'user_id' => $this->author->id,
            'pen_name' => 'Nam Phái Tam Thúc',
            'legal_full_name' => 'Nguyễn Từ Nam',
            'identity_number_encrypted' => 'enc',
            'identity_hash' => 'hash',
            'verification_status' => 'pending',
        ]);

        $genre = Genre::create(['name' => 'Tiên Hiệp', 'slug' => 'tien-hiep']);

        $this->novel = Novel::create([
            'author_id' => $this->author->id,
            'title' => 'Cổ Mộ Huyền Bí',
            'slug' => 'co-mo-huyen-bi',
            'synopsis' => 'Khám phá bí ẩn cổ mộ...',
            'copyright_type' => 'original',
            'status' => 'published',
        ]);
        $this->novel->genres()->attach($genre);

        $this->report = Report::create([
            'reporter_email' => 'lawyer@claimant.com',
            'reportable_type' => 'Novel',
            'reportable_id' => $this->novel->id,
            'violation_category' => 'copyright_infringement',
            'description' => 'Sao chép ý tưởng và cốt truyện tác phẩm Đạo Mộ.',
            'status' => 'received',
        ]);
    }

    public function test_takedown_notice_mail_renders_correct_legal_basis_and_link(): void
    {
        $mailable = new NovelTakedownNoticeMail($this->novel, $this->report);

        $mailable->assertHasSubject('[Pháp lý] Thông Báo Tạm Ẩn Tác Phẩm Do Khiếu Nại Bản Quyền - Cổ Mộ Huyền Bí');
        $mailable->assertSeeInHtml('Nghị định 17/2023/NĐ-CP');
        $mailable->assertSeeInHtml('10 ngày làm việc');
        $mailable->assertSeeInHtml('Cổ Mộ Huyền Bí');
        $mailable->assertSeeInHtml(route('author.counter-notice.create', $this->report));
    }

    public function test_author_kyc_result_mail_renders_approval(): void
    {
        $mailable = new AuthorKycResultMail($this->profile, 'verified');

        $mailable->assertHasSubject('[e-KYC] Hồ Sơ Định Danh Tác Giả Đã Được Phê Duyệt');
        $mailable->assertSeeInHtml('Nam Phái Tam Thúc');
        $mailable->assertSeeInHtml('phê duyệt thành công');
        $mailable->assertSeeInHtml('Nghị định 147/2024/NĐ-CP');
    }

    public function test_author_kyc_result_mail_renders_rejection_reason(): void
    {
        $mailable = new AuthorKycResultMail($this->profile, 'rejected', 'Hình chụp CCCD bị lóa sáng số định danh.');

        $mailable->assertHasSubject('[e-KYC] Thông Báo Kết Quả Thẩm Định Định Danh Tác Giả');
        $mailable->assertSeeInHtml('Hình chụp CCCD bị lóa sáng số định danh.');
        $mailable->assertSeeInHtml('chưa được phê duyệt');
    }

    public function test_mail_is_sent_when_admin_issues_temporary_takedown(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('admin.copyright.takedown', $this->report), [
            'resolution_notes' => 'Tạm ẩn tác phẩm để đối soát.',
        ]);

        Mail::assertQueued(NovelTakedownNoticeMail::class, function (NovelTakedownNoticeMail $mail) {
            return $mail->hasTo('author@novelplatform.vn') &&
                   $mail->novel->id === $this->novel->id &&
                   $mail->report->id === $this->report->id;
        });
    }

    public function test_mail_is_sent_when_admin_approves_or_rejects_kyc(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('admin.kyc.approve', $this->profile));

        Mail::assertQueued(AuthorKycResultMail::class, function (AuthorKycResultMail $mail) {
            return $mail->hasTo('author@novelplatform.vn') &&
                   $mail->status === 'verified';
        });

        $this->actingAs($this->admin)->post(route('admin.kyc.reject', $this->profile), [
            'rejection_reason' => 'Thông tin không rõ ràng.',
        ]);

        Mail::assertQueued(AuthorKycResultMail::class, function (AuthorKycResultMail $mail) {
            return $mail->hasTo('author@novelplatform.vn') &&
                   $mail->status === 'rejected' &&
                   $mail->reason === 'Thông tin không rõ ràng.';
        });
    }
}
