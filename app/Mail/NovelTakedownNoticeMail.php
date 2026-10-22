<?php

namespace App\Mail;

use App\Models\Novel;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovelTakedownNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Novel $novel,
        public Report $report
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Pháp lý] Thông Báo Tạm Ẩn Tác Phẩm Do Khiếu Nại Bản Quyền - {$this->novel->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.legal.takedown_notice',
        );
    }
}
