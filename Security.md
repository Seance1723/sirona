# Security

This document lists the sanitization, escaping, nonces, and capability checks used across the theme.

| Field Type | Sanitizer(s) | Escaper(s) |
|------------|--------------|------------|
| Text       | `sanitize_text_field` | `esc_attr`, `esc_html` |
| Textarea   | `sanitize_textarea_field` | `esc_textarea` |
| Checkbox   | Cast to integer (`isset` check) | `checked` helper |
| Number     | `absint` | `esc_attr` |
| Color      | `sanitize_hex_color` | `esc_attr` |
| URL        | `esc_url_raw` | `esc_url` |
| Email      | `sanitize_email` | `esc_attr` |
| Select     | `sanitize_text_field` | `esc_html`, `esc_attr` |
| Twitter Handle | `sanitize_text_field` | `esc_attr` |
| Social Links / Script Manager | `sanitize_textarea_field` | `esc_textarea` |

Additional field groups

- Mega Menu (theme/inc/mega-menu/meta.php):
  - Sanitizers: `absint` (cols, clamped 2–6), `sanitize_hex_color` (bg color), `esc_url_raw` (bg image), `sanitize_text_field` (width), `wp_kses_post` (custom HTML), checkbox as `isset` → `1`.
  - Escapers: `esc_attr`, `esc_html`, `esc_textarea` in field output; walker renders use appropriate escaping.
  - Nonce: `fx_megamenu_nonce` verified against `fx_megamenu_save` on save; capability `edit_theme_options`.

- Header/Footer Builder (theme/inc/builders/header-footer/storage.php):
  - Sanitizers: `sanitize_key` for `type`, `slug`, and each element key within layout; booleans normalized. Saved with `fx_hf_sanitize_layout()`.
  - Capability: `manage_options` required for saving.

- Options (theme/inc/options.php):
  - Registers settings with `sanitize_callback = fx_sanitize_options` covering all fields above.
  - Nonce: `settings_fields('fx_options')` includes the options nonce automatically.
  - Capability: page restricted to `manage_options`.

- Contact Form AJAX (theme/inc/contact-form.php):
  - Nonce: `fx_contact_nonce` verified against `fx_contact`.
  - Sanitizers: `sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`.

- REST Endpoints:
  - License (theme/inc/licensing/admin-ui.php): All routes require `manage_options`; REST nonce is enqueued via `wp_create_nonce('wp_rest')` and `apiFetch` middleware.
  - Wizard (theme/inc/setup-wizard/wizard.php): Routes require `manage_options`; inputs sanitized with `sanitize_key`, `sanitize_email`.
  - Dashboard (theme/inc/admin/rest-dashboard.php): Routes require `manage_options` via `fx_dashboard_rest_permission`.

- TGMPA (theme/inc/tgm/register-plugins.php):
  - Actions use TGMPA’s built-in nonces and URLs and are limited by `edit_theme_options`/plugin caps.

Escaping on Output

- Admin pages and fields use `esc_html`, `esc_attr`, `esc_url`, `esc_textarea`, and `checked()` helpers.
- Frontend builder renderer sanitizes classes via `sanitize_html_class` and attributes via `esc_attr`.

Cron & Integrity

- Integrity scanner compares file hashes against `inc/integrity/manifest.php`; admin-only, rate-limited.
- No telemetry; repair actions require admin capability and use WP_Filesystem APIs.

All REST and AJAX requests are protected using capability checks and nonces where appropriate.
