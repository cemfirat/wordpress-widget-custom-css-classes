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

## Widgets editor compatibility

The plugin no longer disables WordPress' block-based Widgets screen. Classic third-party widgets remain editable through WordPress' Legacy Widget compatibility layer, so the plugin does not take over the site's global Widgets Editor preference.

The plugin still targets classic `WP_Widget` instances. It does not add CSS-class controls to arbitrary block widgets.

## YOOtheme Pro compatibility

WordPress normally allows the plugin to inject classes through `dynamic_sidebar_params`. Some themes, including YOOtheme Pro configurations, can generate the final widget wrapper after the standard WordPress widget filters have run and therefore discard the modified `before_widget` markup.

For that case, the plugin can inspect the final frontend HTML, identify active widgets by their rendered widget ID and add the configured classes to the matching element.

The fallback is only started when at least one active widget actually has additional classes configured. It can also be disabled with the `ccf_widget_css_classes_enable_html_fallback` filter. WordPress' built-in HTML Tag Processor is used instead of regex-based HTML rewriting.

## Updates

The plugin is prepared for stable updates from this public GitHub repository. WordPress checks the repository's latest stable GitHub Release and accepts only a non-draft, non-prerelease release that contains the exact `wordpress-widget-custom-css-classes.zip` asset and explicit WordPress/PHP requirements.

Until the first stable release is published, the updater simply has no valid release to offer.

Update checks contact `api.github.com`; package downloads use `github.com`. No widget settings or site content are sent to GitHub.

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
