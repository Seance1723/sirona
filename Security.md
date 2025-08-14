# Security

This document lists the sanitization and escaping functions used for theme fields.

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

All REST and AJAX requests are protected using capability checks and nonces where appropriate.