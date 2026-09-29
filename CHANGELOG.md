# Changelog

All notable changes to WordPress Widget Custom CSS Classes are documented here.

## 0.0.1 - Unreleased

- Start the public repository baseline for the renamed plugin.
- Add an additional CSS-class field to classic `WP_Widget` forms.
- Sanitize and de-duplicate whitespace-separated class names.
- Apply classes through the standard WordPress widget wrapper.
- Add a final-HTML compatibility fallback for YOOtheme Pro-style wrapper replacement.
- Keep the WordPress Widgets Block Editor enabled instead of globally forcing the classic editor.
- Start the final-HTML fallback only when active widgets actually need it and allow the fallback to be disabled by filter.
- Use the WordPress HTML Tag Processor as the only HTML mutation path for the supported WordPress baseline.
- Add focused PHP regression tests and pinned CI for supported PHP versions.
- Align plugin metadata, translations, documentation, branding and licensing with the Cem Firat WordPress plugin family.
