<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalReportGuidelinesTest extends TestCase
{
    public function test_copyright_report_form_has_labels_and_typographic_ellipsis(): void
    {
        $response = $this->get('/bao-cao-vi-pham');

        $response->assertStatus(200);
        $response->assertSee('<label for="reportable_type"', false);
        $response->assertSee('id="reportable_type"', false);
        $response->assertSee('<label for="reportable_id"', false);
        $response->assertSee('id="reportable_id"', false);
        $response->assertSee('<label for="reason"', false);
        $response->assertSee('id="reason"', false);
        $response->assertSee('placeholder="Nêu rõ các đoạn trích, chương hồi, hoặc tài liệu chứng minh quyền tác giả…"', false);
    }

    public function test_public_report_alias_view_renders_correctly(): void
    {
        $rendered = view('legal.public_report', ['novel' => null])->render();

        $this->assertStringContainsString('id="reportable_type"', $rendered);
        $this->assertStringContainsString('id="reason"', $rendered);
    }
}
