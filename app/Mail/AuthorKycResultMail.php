<?php

namespace App\Mail;

use App\Models\AuthorProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthorKycResultMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public AuthorProfile $profile,
        public string $status,
        public ?string $reason = null
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->status === 'verified'
            ? '[e-KYC] Hồ Sơ Định Danh Tác Giả Đã Được Phê Duyệt'
            : '[e-KYC] Thông Báo Kết Quả Thẩm Định Định Danh Tác Giả';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.legal.kyc_result',
        );
    }
}
