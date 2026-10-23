<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class BladeComponentsTest extends TestCase
{
    public function test_button_renders_button_and_link_with_minimal_props(): void
    {
        $btn = $this->blade('<x-button variant="primary">Khám Phá</x-button>');
        $btn->assertSee('Khám Phá');
        $btn->assertSee('bg-indigo-600');
        $btn->assertSee('focus-visible:ring-2');

        $link = $this->blade('<x-button href="/doc-truyen" variant="ghost">Đọc Ngay</x-button>');
        $link->assertSee('<a', false);
        $link->assertSee('href="/doc-truyen"', false);
    }

    public function test_badge_renders_with_age_13_and_status_variants(): void
    {
        $ageBadge = $this->blade('<x-badge variant="age13">13+</x-badge>');
        $ageBadge->assertSee('13+');
        $ageBadge->assertSee('bg-cyan-500/15');

        $dotBadge = $this->blade('<x-badge variant="emerald" dot>Hoàn thành</x-badge>');
        $dotBadge->assertSee('Hoàn thành');
        $dotBadge->assertSee('rounded-full');
    }

    public function test_alert_renders_with_accessibility_role_and_dismiss_button(): void
    {
        $alert = $this->blade('<x-alert type="error">Có lỗi xảy ra!</x-alert>');
        $alert->assertSee('role="alert"', false);
        $alert->assertSee('Có lỗi xảy ra!');
        $alert->assertSee('aria-label="Đóng thông báo"', false);
    }

    public function test_card_renders_with_glassmorphism_and_optional_hover(): void
    {
        $card = $this->blade('<x-card hover glow><p>Nội dung thẻ</p></x-card>');
        $card->assertSee('Nội dung thẻ');
        $card->assertSee('hover:-translate-y-1');
        $card->assertSee('shadow-glow');
    }

    public function test_rating_renders_stars_with_accessible_aria_label(): void
    {
        $rating = $this->blade('<x-rating :score="4.5" :max="5" />');
        $rating->assertSee('aria-label="Đánh giá 4.5 trên 5 sao"', false);
        $rating->assertSee('4.5');
    }

    public function test_novel_card_renders_with_aspect_ratio_and_13_plus_badge(): void
    {
        $novel = (object)[
            'id' => 1,
            'title' => 'Vương Quốc Hư Ảo',
            'slug' => 'vuong-quoc-hu-ao',
            'cover_image' => '/covers/sample.webp',
            'author_name' => 'IceGozi',
            'rating' => 4.9,
            'age_rating' => 13,
            'views_count' => 12500,
            'chapters_count' => 120,
        ];

        $view = $this->blade('<x-novel-card :novel="$novel" />', ['novel' => $novel]);
        $view->assertSee('Vương Quốc Hư Ảo');
        $view->assertSee('IceGozi');
        $view->assertSee('aspect-[3/4]', false);
        $view->assertSee('13+');
    }

    public function test_modal_renders_with_dialog_semantics_and_backdrop(): void
    {
        $modal = $this->blade('<x-modal name="testModal" title="Tiêu đề Modal"><p>Nội dung</p></x-modal>');
        $modal->assertSee('role="dialog"', false);
        $modal->assertSee('aria-modal="true"', false);
        $modal->assertSee('Tiêu đề Modal');
        $modal->assertSee('x-show="testModal"', false);
    }

    public function test_input_renders_with_label_and_error(): void
    {
        $input = $this->blade('<x-input label="Tên người dùng" name="username" error="Tên không hợp lệ" />');
        $input->assertSee('Tên người dùng');
        $input->assertSee('name="username"', false);
        $input->assertSee('Tên không hợp lệ');
        $input->assertSee('border-rose-500');
    }
}

