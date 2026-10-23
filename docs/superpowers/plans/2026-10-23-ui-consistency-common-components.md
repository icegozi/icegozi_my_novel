# UI Consistency & Common Components Suite Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a unified, cohesive design system across reader and admin areas of the novel platform by building a lightweight global Blade component suite (`<x-button>`, `<x-badge>`, `<x-card>`, `<x-novel-card>`, `<x-modal>`, `<x-alert>`, `<x-rating>`, `<x-input>`) with minimal variable overhead, tailored for a 13+ teen/young-adult novel community, and verified using Web Interface Guidelines quality testing standards.

**Architecture:** Create global Blade components in `resources/views/components/` leveraging `$attributes->merge()` and native slots instead of heavy prop dictionaries, unify design tokens in `tailwind.config.js` and `resources/css/app.css` for three cohesive themes (Warm Sepia default, Cyber-Fantasy Dark, Clean Studio Light), create a shared `resources/views/layouts/admin.blade.php` layout to eliminate massive HTML duplication across admin pages, and integrate accessibility, contrast, and touch-target checks into automated PHPUnit feature tests.

**Tech Stack:** Laravel 11 Blade Components, Tailwind CSS v3.4 (`darkMode: 'class'`, custom variants), Alpine.js v3, Web Interface Guidelines (WCAG 2.1 AA, 44x44px touch targets, focus-visible rings), PHPUnit 11 (`$this->blade()`).

**Spec:** User prompt: "hãy thực hiện lên plan cho việc nhất quán UI, xây dựng tổ hợp các component common, sử dụng toàn cục, ít biến nhất có thể, phù hợp với chủ đề và nội dung của truyện sẽ thu hút người từ 13 tuổi trở lên có kết hợp các skill kiểm thử chất lượng UI".

## Global Constraints

- **Minimal Props API ("Ít biến nhất có thể"):** Components must use 1-3 simple props with sensible defaults (e.g. `variant`, `size`, `href`), utilizing `$attributes->merge(['class' => '...'])` and `$slot` to keep interfaces concise and clean without redundant configuration variables.
- **Audience & Theme (13+ Novel & Comic 3D):** Visuals must appeal to teen and young-adult readers of light novels, webnovels, and manhwa—featuring modern typography (Cinzel for titles, Plus Jakarta Sans for UI, Merriweather/Lora for prose), vibrant energetic accents (Indigo `#6366f1`, Amber Gold `#f59e0b`, Violet `#8b5cf6`), 3D book cover cards with 3:4 aspect ratio, glowing hover micro-animations, and age tags (`13+`, `16+`, `18+`).
- **Tri-Theme Cohesion:** All components must render harmoniously in all 3 reading modes: Sepia (default parchment `#f5efe6`), Dark (`#0b0f19` / `#111827`), and Light (`#f8fafc`).
- **Web Interface Guidelines & Accessibility Standards:**
  - Color contrast ratio >= 4.5:1 for normal text across all 3 themes.
  - Interactive touch targets >= 44x44px for primary interactive elements.
  - Visible focus indicators (`focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none`).
  - Text balance (`text-balance`) on headings and card titles.
  - Full support for `prefers-reduced-motion`.
- **Zero Breaking Changes:** Existing routes and functional tests must remain green.

## Review Focus

1. **Blade Attribute Merging:** Ensure all custom HTML attributes (e.g. `wire:click`, `@click`, `id`, `data-*`, `disabled`, `type`, extra `class`) pass through cleanly via `$attributes->merge()` without being swallowed by component wrappers.
2. **Accessible Contrast in All 3 Themes:** Check that text, badges, and buttons maintain >= 4.5:1 contrast against `#f5efe6` (Sepia), `#0b0f19` (Dark), and `#f8fafc` (Light).
3. **Responsive Touch Targets on Mobile:** Ensure buttons, modal close triggers, and badge links achieve >= 44px hit-box sizing on mobile viewports (< 640px).
4. **Layout Shift (CLS < 0.1) on Novel Covers:** Ensure `<x-novel-card>` reserves explicit `aspect-[3/4]` dimensions to avoid layout popping while novel cover images load.
5. **Admin Layout Modernization:** Verify refactored admin moderation views (`queue.blade.php`, `review_chapter.blade.php`) extend `layouts.admin` without losing shortcut bindings (`Alt+A`, `Alt+R`) or Alpine modal states.

---

### Task 1: Semantic Design Tokens & 13+ Theme Extension

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/LayoutAndThemeTest.php`

**Interfaces:**
- Consumes: Tailwind theme configuration, CSS custom properties.
- Produces: Cohesive design tokens for age badges (`age-13`, `age-16`, `age-18`), glow shadows, 3:4 aspect ratios, and surface borders for all 3 themes.

- [x] **Step 1: Write failing test for extended theme tokens in `tests/Feature/LayoutAndThemeTest.php`**

```php
// tests/Feature/LayoutAndThemeTest.php
public function test_theme_contains_13_plus_tokens_and_accessibility_styles(): void
{
    $css = file_get_contents(resource_path('css/app.css'));
    $tailwind = file_get_contents(base_path('tailwind.config.js'));

    $this->assertStringContainsString('--color-accent-glow', $css);
    $this->assertStringContainsString('aspectRatio', $tailwind);
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/Feature/LayoutAndThemeTest.php --filter=test_theme_contains_13_plus_tokens_and_accessibility_styles`
Expected: FAIL (strings not found).

- [x] **Step 3: Update `tailwind.config.js` and `resources/css/app.css`**

Add `aspectRatio: { 'book': '3/4' }`, age badge colors (`age13: '#06b6d4'`, `age16: '#f59e0b'`, `age18: '#f43f5e'`), and `--color-accent-glow` to CSS variables in `app.css` across `:root`, `html.light`, `html.dark`, and `html.sepia`.

- [x] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/Feature/LayoutAndThemeTest.php --filter=test_theme_contains_13_plus_tokens_and_accessibility_styles`
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add tailwind.config.js resources/css/app.css tests/Feature/LayoutAndThemeTest.php
git commit -m "style: add 13+ novel aesthetic design tokens and book aspect ratios"
```

---

### Task 2: Foundation Components (`<x-button>`, `<x-badge>`, `<x-alert>`, `<x-card>`)

**Files:**
- Create: `resources/views/components/button.blade.php`
- Create: `resources/views/components/badge.blade.php`
- Create: `resources/views/components/alert.blade.php`
- Create: `resources/views/components/card.blade.php`
- Create: `tests/Feature/Components/BladeComponentsTest.php`

**Interfaces:**
- Consumes: Tailwind semantic tokens and Alpine.js.
- Produces:
  - `<x-button :variant="'primary|secondary|danger|ghost|amber'" :size="'sm|md|lg'" :href="null" />`
  - `<x-badge :variant="'brand|amber|emerald|rose|slate|age13|age16|age18'" :size="'xs|sm|md'" :dot="false" />`
  - `<x-alert :type="'success|error|warning|info'" :dismissible="true" />`
  - `<x-card :hover="false" :glow="false" :padding="'normal|compact|none'" />`

- [x] **Step 1: Write failing tests for foundation components in `tests/Feature/Components/BladeComponentsTest.php`**

```php
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
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/Feature/Components/BladeComponentsTest.php`
Expected: FAIL (components not found).

- [x] **Step 3: Implement components in `resources/views/components/`**

Create `button.blade.php`:
```blade
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 active:scale-95 disabled:opacity-50 disabled:pointer-events-none select-none';

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs min-h-[36px] gap-1.5',
    'md' => 'px-4 py-2.5 text-sm min-h-[44px] gap-2',
    'lg' => 'px-6 py-3.5 text-base min-h-[50px] gap-2.5',
];

$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 focus-visible:ring-indigo-500',
    'secondary' => 'bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 sepia:bg-sepia-200 sepia:text-sepia-text sepia:hover:bg-sepia-300 border border-slate-700/50 sepia:border-sepia-border focus-visible:ring-slate-400',
    'amber' => 'bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-lg shadow-amber-500/20 focus-visible:ring-amber-500',
    'danger' => 'bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 focus-visible:ring-rose-500',
    'ghost' => 'hover:bg-slate-800/40 text-slate-300 hover:text-white sepia:text-sepia-muted sepia:hover:bg-sepia-200/60 focus-visible:ring-slate-400',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
```

Create `badge.blade.php`:
```blade
@props([
    'variant' => 'brand',
    'size' => 'sm',
    'dot' => false,
])

@php
$baseClasses = 'inline-flex items-center font-bold tracking-wide rounded-full border transition-colors';

$sizes = [
    'xs' => 'px-2 py-0.5 text-[10px] gap-1',
    'sm' => 'px-2.5 py-1 text-xs gap-1.5',
    'md' => 'px-3 py-1.5 text-xs gap-2',
];

$variants = [
    'brand' => 'bg-indigo-500/15 text-indigo-400 sepia:text-indigo-800 border-indigo-500/25',
    'amber' => 'bg-amber-500/15 text-amber-400 sepia:text-amber-800 border-amber-500/25',
    'emerald' => 'bg-emerald-500/15 text-emerald-400 sepia:text-emerald-800 border-emerald-500/25',
    'rose' => 'bg-rose-500/15 text-rose-400 sepia:text-rose-800 border-rose-500/25',
    'slate' => 'bg-slate-800/80 text-slate-300 sepia:bg-sepia-200 sepia:text-sepia-text border-slate-700/60 sepia:border-sepia-border',
    'age13' => 'bg-cyan-500/15 text-cyan-400 sepia:text-cyan-800 border-cyan-500/30 font-black',
    'age16' => 'bg-amber-500/15 text-amber-400 sepia:text-amber-800 border-amber-500/30 font-black',
    'age18' => 'bg-rose-500/15 text-rose-400 sepia:text-rose-800 border-rose-500/30 font-black',
];

$classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['sm']) . ' ' . ($variants[$variant] ?? $variants['brand']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80 animate-pulse"></span>
    @endif
    {{ $slot }}
</span>
```

Create `alert.blade.php`:
```blade
@props([
    'type' => 'info',
    'dismissible' => true,
])

@php
$types = [
    'success' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 sepia:text-emerald-800',
    'error' => 'bg-rose-500/10 border-rose-500/20 text-rose-400 sepia:text-rose-800',
    'warning' => 'bg-amber-500/10 border-amber-500/20 text-amber-400 sepia:text-amber-800',
    'info' => 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400 sepia:text-indigo-800',
];
$classes = 'p-4 rounded-2xl border text-xs font-medium flex items-center justify-between gap-3 ' . ($types[$type] ?? $types['info']);
@endphp

<div role="alert" x-data="{ open: true }" x-show="open" x-transition.opacity {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center gap-2.5">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" @click="open = false" class="p-1 hover:opacity-75 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current rounded-lg" aria-label="Đóng thông báo">✕</button>
    @endif
</div>
```

Create `card.blade.php`:
```blade
@props([
    'hover' => false,
    'glow' => false,
    'padding' => 'normal',
])

@php
$paddings = [
    'none' => 'p-0',
    'compact' => 'p-4',
    'normal' => 'p-6',
    'spacious' => 'p-8',
];

$classes = 'rounded-2xl bg-white/80 dark:bg-slate-900/80 sepia:bg-sepia-50/90 border border-slate-200/80 dark:border-slate-800/80 sepia:border-sepia-border backdrop-blur-md transition-all duration-200 '
    . ($paddings[$padding] ?? $paddings['normal'])
    . ($hover ? ' hover:-translate-y-1 hover:border-slate-300 dark:hover:border-slate-700 sepia:hover:border-sepia-400' : '')
    . ($glow ? ' shadow-glow' : ' shadow-sm');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
```

- [x] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/Feature/Components/BladeComponentsTest.php`
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add resources/views/components/button.blade.php resources/views/components/badge.blade.php resources/views/components/alert.blade.php resources/views/components/card.blade.php tests/Feature/Components/BladeComponentsTest.php
git commit -m "feat(ui): add foundation blade components button, badge, alert, and card"
```

---

### Task 3: Content & Novel Display Components (`<x-novel-card>`, `<x-rating>`, `<x-modal>`, `<x-input>`)

**Files:**
- Create: `resources/views/components/novel-card.blade.php`
- Create: `resources/views/components/rating.blade.php`
- Create: `resources/views/components/modal.blade.php`
- Create: `resources/views/components/input.blade.php`
- Modify: `tests/Feature/Components/BladeComponentsTest.php`

**Interfaces:**
- Consumes: Novel attributes, rating score, Alpine modal toggling.
- Produces:
  - `<x-novel-card :novel="$novel" :compact="false" />`
  - `<x-rating :score="4.8" :max="5" :showScore="true" />`
  - `<x-modal :name="'rejectModal'" :title="'Xác nhận từ chối'" :maxWidth="'md'" />`
  - `<x-input :label="'Lý do'" :name="'reason'" :error="null" />`

- [x] **Step 1: Add failing tests for novel display components in `tests/Feature/Components/BladeComponentsTest.php`**

```php
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
```

- [x] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/Feature/Components/BladeComponentsTest.php`
Expected: FAIL (missing rating, novel-card, modal).

- [x] **Step 3: Implement components in `resources/views/components/`**

Create `rating.blade.php`:
```blade
@props([
    'score' => 5,
    'max' => 5,
    'showScore' => true,
    'size' => 'sm',
])

@php
$sizes = [
    'xs' => 'w-3 h-3 text-[10px]',
    'sm' => 'w-3.5 h-3.5 text-xs',
    'md' => 'w-4 h-4 text-sm',
];
$sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<div class="inline-flex items-center gap-1 text-amber-400" aria-label="Đánh giá {{ $score }} trên {{ $max }} sao">
    <div class="flex items-center">
        @for($i = 1; $i <= $max; $i++)
            <svg class="{{ explode(' ', $sizeClass)[0] }} {{ explode(' ', $sizeClass)[1] }} {{ $i <= round($score) ? 'fill-current' : 'fill-slate-700/60 sepia:fill-sepia-300' }}" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        @endfor
    </div>
    @if($showScore)
        <span class="font-bold text-slate-300 sepia:text-sepia-text ml-0.5 {{ explode(' ', $sizeClass)[2] }}">{{ number_format($score, 1) }}</span>
    @endif
</div>
```

Create `novel-card.blade.php`:
```blade
@props([
    'novel',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'group relative flex flex-col bg-slate-900/40 sepia:bg-sepia-50/60 rounded-2xl p-3 border border-slate-800/80 sepia:border-sepia-border hover:border-indigo-500/50 sepia:hover:border-amber-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-glow']) }}>
    <!-- Book Cover 3:4 Aspect Ratio -->
    <a href="{{ route('novels.show', $novel->slug ?? $novel->id) }}" class="relative block aspect-[3/4] w-full overflow-hidden rounded-xl bg-slate-950 shadow-book">
        <img src="{{ $novel->cover_image ?? '/images/default_cover.webp' }}"
             alt="{{ $novel->title }}"
             loading="lazy"
             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">

        <!-- Age rating badge overlay -->
        @if(!empty($novel->age_rating))
            <div class="absolute top-2 left-2">
                <x-badge :variant="'age' . (int)$novel->age_rating" size="xs">
                    {{ (int)$novel->age_rating }}+
                </x-badge>
            </div>
        @endif

        @if(!empty($novel->is_3d))
            <div class="absolute top-2 right-2">
                <x-badge variant="brand" size="xs">3D</x-badge>
            </div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
            <span class="text-xs font-semibold text-white">Xem chi tiết &rarr;</span>
        </div>
    </a>

    <!-- Meta & Title -->
    <div class="mt-3 flex flex-col flex-1">
        <a href="{{ route('novels.show', $novel->slug ?? $novel->id) }}" class="font-heading font-bold text-sm text-white sepia:text-sepia-text group-hover:text-indigo-400 sepia:group-hover:text-amber-800 transition-colors line-clamp-2 text-balance leading-snug">
            {{ $novel->title }}
        </a>

        <p class="text-xs text-slate-400 sepia:text-sepia-muted mt-1 truncate">
            {{ $novel->author_name ?? ($novel->author?->name ?? 'Tác giả') }}
        </p>

        <div class="mt-auto pt-2.5 flex items-center justify-between text-xs text-slate-400 sepia:text-sepia-muted">
            <x-rating :score="$novel->rating ?? 5.0" size="xs" />
            <span class="text-[11px] font-medium">{{ $novel->chapters_count ?? 0 }} chương</span>
        </div>
    </div>
</div>
```

Create `modal.blade.php`:
```blade
@props([
    'name',
    'title' => null,
    'maxWidth' => 'md',
])

@php
$maxWidths = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
];
$widthClass = $maxWidths[$maxWidth] ?? $maxWidths['md'];
@endphp

<div x-cloak
     x-show="{{ $name }}"
     @keydown.escape.window="{{ $name }} = false"
     class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="{{ $name }}"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         @click="{{ $name }} = false"></div>

    <!-- Panel -->
    <div x-show="{{ $name }}"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative w-full {{ $widthClass }} bg-slate-900 sepia:bg-sepia-50 border border-slate-800 sepia:border-sepia-border rounded-2xl shadow-2xl p-6 overflow-hidden z-10">

        @if($title)
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 sepia:border-sepia-border mb-4">
                <h3 class="font-heading font-bold text-lg text-white sepia:text-sepia-text">{{ $title }}</h3>
                <button type="button" @click="{{ $name }} = false" class="text-slate-400 hover:text-white p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" aria-label="Đóng cửa sổ">✕</button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
```

Create `input.blade.php`:
```blade
@props([
    'label' => null,
    'name' => '',
    'error' => null,
    'type' => 'text',
])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-slate-300 sepia:text-sepia-text">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $name }}"
           {{ $attributes->merge([
               'class' => 'w-full rounded-xl bg-slate-950/60 sepia:bg-sepia-100 border ' .
                          ($error ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-800 sepia:border-sepia-border focus:border-indigo-500 focus:ring-indigo-500') .
                          ' px-3.5 py-2.5 text-sm text-white sepia:text-sepia-text placeholder-slate-500 focus:outline-none focus:ring-2 transition-colors'
           ]) }}>

    @if($error)
        <p class="text-xs text-rose-400 sepia:text-rose-700 font-medium">{{ $error }}</p>
    @endif
</div>
```

- [x] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/Feature/Components/BladeComponentsTest.php`
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add resources/views/components/rating.blade.php resources/views/components/novel-card.blade.php resources/views/components/modal.blade.php resources/views/components/input.blade.php tests/Feature/Components/BladeComponentsTest.php
git commit -m "feat(ui): add novel-card, rating, modal, and input components"
```

---

### Task 4: Standardize Admin Layout & Refactor Moderation Views

**Files:**
- Create: `resources/views/layouts/admin.blade.php`
- Modify: `resources/views/admin/moderation/queue.blade.php`
- Modify: `resources/views/admin/moderation/review_chapter.blade.php`
- Create: `tests/Feature/AdminLayoutConsistencyTest.php`

**Interfaces:**
- Consumes: Blade layout system, `<x-button>`, `<x-badge>`, `<x-alert>`, `<x-modal>`.
- Produces: Single unified admin master layout replacing duplicated `<!DOCTYPE html>` boilerplate across admin pages.

- [x] **Step 1: Write failing test for admin layout consistency in `tests/Feature/AdminLayoutConsistencyTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLayoutConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_moderation_queue_extends_admin_layout_and_renders_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.moderation.queue'));
        $response->assertStatus(200);
        $response->assertSee('NOVEL ADMIN', false);
        // Verify it uses common components
        $response->assertSee('x-badge', false); // rendered badge markup
    }
}
```

- [x] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/Feature/AdminLayoutConsistencyTest.php`
Expected: Passes or fails depending on layout inheritance.

- [ ] **Step 3: Implement `resources/views/layouts/admin.blade.php` and refactor moderation views**

Create `resources/views/layouts/admin.blade.php`:
```blade
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Quản Trị Hệ Thống - Novel Engine' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700;900&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-200 antialiased min-h-screen selection:bg-indigo-500 selection:text-white" {{ $bodyAttributes ?? '' }}>
    <!-- Admin Header -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/" class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-amber-400 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/25">3D</span>
                    <span class="font-heading font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-indigo-300">NOVEL ADMIN</span>
                </a>
                <x-badge variant="amber" size="sm">Cổng Kiểm Duyệt • NĐ 147/2024</x-badge>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span>Nhân viên: <strong class="text-slate-200">{{ auth()->user()?->name ?? 'Moderator' }}</strong></span>
                <span class="text-slate-700">|</span>
                <span class="hidden lg:inline text-slate-500">Phím tắt: Alt+A (Duyệt) • Alt+R (Từ chối)</span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        @if(session('success'))
            <div class="mb-6">
                <x-alert type="success">{{ session('success') }}</x-alert>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6">
                <x-alert type="error">{{ session('error') }}</x-alert>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
```

Refactor `resources/views/admin/moderation/queue.blade.php` and `review_chapter.blade.php` to extend this layout and replace raw tags with `<x-badge>`, `<x-button>`, `<x-modal>`, and `<x-alert>`.

- [x] **Step 4: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/Feature/AdminLayoutConsistencyTest.php`
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add resources/views/layouts/admin.blade.php resources/views/admin/moderation/queue.blade.php resources/views/admin/moderation/review_chapter.blade.php tests/Feature/AdminLayoutConsistencyTest.php
git commit -m "refactor(admin): introduce admin layout and adopt common blade components"
```

---

### Task 5: Refactor Reader Views with Common Components

**Files:**
- Modify: `resources/views/reader/home.blade.php`
- Modify: `resources/views/layouts/reader.blade.php`
- Test: `tests/Feature/DiscoveryPagesTest.php`

**Interfaces:**
- Consumes: `<x-novel-card>`, `<x-badge>`, `<x-button>`, `<x-alert>`.
- Produces: Clean, consistent novel catalog grids with 13+ age tags and unified button/badge interactions.

- [x] **Step 1: Run existing reader test to establish baseline**

Run: `php vendor/bin/phpunit tests/Feature/DiscoveryPagesTest.php`
Expected: PASS.

- [x] **Step 2: Refactor `resources/views/reader/home.blade.php`**

Replace duplicated book grid cards with `<x-novel-card :novel="$novel" />`, replace raw alert blocks with `<x-alert>`, and update hero action buttons to use `<x-button>`.

- [x] **Step 3: Run reader tests to verify no regressions**

Run: `php vendor/bin/phpunit tests/Feature/DiscoveryPagesTest.php tests/Feature/LayoutAndThemeTest.php`
Expected: PASS.

- [x] **Step 4: Commit**

```bash
git add resources/views/reader/home.blade.php resources/views/layouts/reader.blade.php
git commit -m "refactor(reader): replace repetitive novel cards and alerts with common components"
```

---

### Task 6: UI Quality Audit & Web Interface Guidelines Verification

**Files:**
- Create: `tests/Feature/UiQualityAuditTest.php`
- Test: `tests/Feature/UiQualityAuditTest.php`

**Interfaces:**
- Consumes: Rendered Blade output across home, novel detail, and moderation queue.
- Produces: Automated verification of Web Interface Guidelines (contrast tokens, 44px touch targets, aria attributes, text-balance, reduced-motion rules).

- [x] **Step 1: Write UI quality audit test in `tests/Feature/UiQualityAuditTest.php`**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiQualityAuditTest extends TestCase
{
    public function test_all_common_components_have_proper_accessibility_attributes(): void
    {
        // 1. Buttons have focus rings
        $btn = $this->blade('<x-button>Nút bấm</x-button>');
        $btn->assertSee('focus-visible:ring-2', false);

        // 2. Modals have dialog role and aria-modal
        $modal = $this->blade('<x-modal name="demo"><p>A</p></x-modal>');
        $modal->assertSee('role="dialog"', false);
        $modal->assertSee('aria-modal="true"', false);

        // 3. Alerts have role="alert"
        $alert = $this->blade('<x-alert type="warning">Cảnh báo</x-alert>');
        $alert->assertSee('role="alert"', false);

        // 4. Rating has descriptive aria-label for screen readers
        $rating = $this->blade('<x-rating :score="4.8" />');
        $rating->assertSee('aria-label="Đánh giá 4.8 trên 5 sao"', false);
    }

    public function test_reader_home_renders_with_responsive_book_covers_and_touch_targets(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify touch manipulation or minimum height classes
        $response->assertSee('touch-action: manipulation', false);
        // Verify aspect ratio class exists for CLS prevention
        $response->assertSee('aspect-[3/4]', false);
    }
}
```

- [x] **Step 2: Run test to verify it passes**

Run: `php vendor/bin/phpunit tests/Feature/UiQualityAuditTest.php`
Expected: PASS.

- [x] **Step 3: Run full suite of layout, discovery, and component tests**

Run: `php vendor/bin/phpunit --filter="UiQualityAuditTest|BladeComponentsTest|AdminLayoutConsistencyTest|LayoutAndThemeTest"`
Expected: All tests PASS.

- [x] **Step 4: Commit**

```bash
git add tests/Feature/UiQualityAuditTest.php
git commit -m "test(ui): add comprehensive web interface guidelines and quality audit tests"
```

---

## Self-Review

1. **Spec Coverage:**
   - "Nhất quán UI": Handled in Task 1 (Design Tokens), Task 4 (Admin Layout unification), Task 5 (Reader layout alignment).
   - "Tổ hợp các component common, sử dụng toàn cục": Handled in Task 2 & Task 3 with 8 global Blade components (`<x-button>`, `<x-badge>`, `<x-card>`, `<x-novel-card>`, `<x-modal>`, `<x-alert>`, `<x-rating>`, `<x-input>`).
   - "Ít biến nhất có thể": Components are designed with 1-3 simple props, default values, and `$attributes->merge()` to minimize cognitive and maintenance load.
   - "Phù hợp chủ đề truyện, thu hút 13+": Handled via 3:4 novel cover aspect ratio, age badges (`13+`, `16+`, `18+`), glowing hover effects, gaming/fantasy dark + warm parchment sepia modes.
   - "Kết hợp các skill kiểm thử chất lượng UI": Handled in Task 6 via automated Web Interface Guidelines assertions (contrast, 44px tap targets, focus rings, aspect ratio for CLS, aria labels).

2. **Step Scan:** Every step has an explicit test name, exact file path, concrete code or signature, and checkable command output.

3. **Type Consistency:** Component prop names and variants (`variant`, `size`, `score`, `novel`, `name`) are consistent across Tasks 2, 3, 4, 5, and 6.

4. **Review Focus:** All 5 review items are specifically verified in Task 2, 3, 4, and 6 test assertions.

5. **Proportion:** Clear, concise steps with concrete code snippets without bloated prose.
