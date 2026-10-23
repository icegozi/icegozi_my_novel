# Workspace Rule: Frontend UI, Accessibility & Performance

> **Scope:** Governs Blade templates, CSS classes, Tailwind design tokens, typography, and interactive components.

---

## 1. Web Content Accessibility Guidelines (WCAG 2.1/2.2 AA)

1. **WCAG 2.5.3 — Label in Name:**
   - Any interactive element with visible text **MUST** contain that exact visible string inside its accessible name (`aria-label`):
     ```html
     <!-- Correct: visible text "A-" is included in aria-label -->
     <button type="button" aria-label="A- Giảm kích thước phông chữ">A-</button>
     <!-- Correct: visible text "Aa Cỡ Chữ" is included in aria-label -->
     <button type="button" aria-label="Aa Cỡ Chữ - Cài đặt giao diện đọc"><span>Aa</span> <span>Cỡ Chữ</span></button>
     ```
2. **WCAG 1.3.1 — Info and Relationships (Heading Order & Form Labels):**
   - Headings **MUST NOT** skip levels (e.g. going directly from `<h1>` to `<h3>` is forbidden; use `<h2>` as the intermediary).
   - Form controls **MUST** pair `<label for="field_id">` with `<input id="field_id">`, `<select id="field_id">`, or `<textarea id="field_id">`.
3. **WCAG 1.1.1 — Non-Text Content:**
   - Meaningful images **MUST** provide descriptive `alt="..."`.
   - Decorative icons, emojis (e.g. `📚`, `✍️`, `🗡️`, `🔒`, `❌`, `📑`, `🔄`), and decorative SVGs **MUST** be wrapped in `<span aria-hidden="true">...</span>` or have `aria-hidden="true"`.
4. **Accessible Modals & Drawers:**
   - Slide-out drawers and dialog overlays **MUST** include `role="dialog"`, `aria-modal="true"`, `aria-label="..."`, and CSS `overscroll-contain` to trap touch scroll and inform screen readers.
5. **Password Managers & Autofill:**
   - Password change forms **MUST** include an accessible hidden username field:
     ```html
     <input type="text" name="username" value="{{ auth()->user()?->email ?? '' }}" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
     ```

---

## 2. Core Web Vitals & Performance

1. **Cumulative Layout Shift (CLS) Prevention:**
   - All `<img>` tags **MUST** specify explicit `width` and `height` integer attributes:
     ```html
     <!-- Author novel cards -->
     <img src="..." alt="..." width="64" height="88" class="w-16 h-22 object-cover rounded-xl shrink-0">
     <!-- Home continue reading -->
     <img src="..." alt="..." width="56" height="80" class="w-14 h-20 object-cover rounded-xl shrink-0">
     <!-- Home chapter feed -->
     <img src="..." alt="..." width="48" height="64" class="w-12 h-16 rounded-lg object-cover shrink-0">
     ```
2. **CSS Transitions:**
   - You **MUST NOT** use generic `transition-all`.
   - You **MUST** declare specific transition properties:
     - Background/text color: `transition-colors duration-200`
     - Opacity fade: `transition-opacity duration-200`
     - Scale/hover transforms: `transition-transform duration-200`
     - Mixed: `transition-opacity transition-colors duration-200`

---

## 3. Visual Ergonomics & Interaction Targets

1. **Touch Targets:**
   - Interactive buttons, chapter links, and navigation items **MUST** have a minimum target size of 24px x 24px (use `py-0.5 min-h-[24px]` on inline text links).
   - Primary mobile action buttons **SHOULD** achieve >= 44px x 44px.
2. **Color Contrast:**
   - Normal text against light, dark, or sepia backgrounds **MUST** satisfy a contrast ratio of at least 4.5:1 (e.g., use `text-slate-500 dark:text-slate-400 sepia:text-sepia-muted` instead of low-contrast `text-slate-400`).
   - Large or bold text (18px+ or 14px+ bold) **MUST** achieve at least 3.0:1.
3. **Keyboard Focus Rings:**
   - All interactive controls (buttons, links, toggles) **MUST** provide visible focus indicators (e.g. `focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg`).
