<p align="center">
  <img src="https://raw.githubusercontent.com/cemfirat/repository-governance/main/assets/brand-banner.webp" alt="Cem Firat creative consultancy artwork" width="900" />
</p>

# WordPress Widget Custom CSS Classes

A lightweight WordPress plugin that adds additional CSS classes to classic `WP_Widget` widgets, including compatibility handling for YOOtheme Pro when the theme rebuilds the final widget wrapper.

**Author:** [Cem Firat](https://cemfirat.com/)  
**License:** GPL-2.0-or-later  
**Requirements:** WordPress 6.5+ and PHP 8.0+.

> **Status:** Early development version 0.0.1. The repository is being stabilized before the first tagged release.

## What it does

- Adds an **Additional CSS classes** field to classic widget forms.
- Accepts multiple whitespace-separated CSS classes.
- Sanitizes and de-duplicates stored class names.
- Adds the configured classes to the normal WordPress `before_widget` wrapper.
- Includes a compatibility fallback for themes that rebuild the final widget wrapper.
- Supports YOOtheme Pro output when the final wrapper keeps the WordPress widget ID.

## Installation

For development builds:

1. Download or clone the repository.
2. Place the plugin directory in `wp-content/plugins/`.
3. Activate **WordPress Widget Custom CSS Classes** in WordPress.
4. Open **Appearance → Widgets** and edit a classic widget.
5. Enter one or more class names without a leading dot, for example:

   ```text
   uk-margin-large my-custom-class
   ```

6. Save the widget.

A stable release ZIP will be published only after the baseline and compatibility checks are complete.

## Classic widget editor

The current implementation disables the block-based Widgets screen so that the additional field can be attached to traditional `WP_Widget` forms.

That global behavior is intentionally under review before the first stable release because it affects the complete Widgets administration screen, not only widgets that use custom classes.

## YOOtheme Pro compatibility

WordPress normally allows the plugin to inject classes through `dynamic_sidebar_params`. Some themes, including YOOtheme Pro configurations, can generate the final widget wrapper after the standard WordPress widget filters have run and therefore discard the modified `before_widget` markup.

For that case, the plugin can inspect the final frontend HTML, identify active widgets by their rendered widget ID and add the configured classes to the matching element.

The fallback is being reviewed for performance and scope before the first stable release.

## Development

The current stabilization work is tracked in [issue #1](https://github.com/cemfirat/wordpress-widget-custom-css-classes/issues/1).

Before the first release the project should have the same baseline as the other Cem Firat WordPress plugins:

- shared repository branding
- consistent plugin/readme metadata
- GPL license file
- WordPress.org-style `readme.txt`
- changelog
- common repository asset/logo
- focused validation before CI/release automation is enabled
- predictable GitHub release packaging and WordPress update behavior

## License

GPL-2.0-or-later. Copyright © 2026 Cem Firat.
