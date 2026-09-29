# WordPress Widget Custom CSS Classes

A lightweight WordPress plugin that adds an additional CSS-class field to classic `WP_Widget` widgets.

The plugin supports normal WordPress sidebar rendering and includes a compatibility fallback for themes such as YOOtheme Pro that rebuild the final widget wrapper after the standard WordPress widget filters have run.

## Status

Early development version: **0.0.1**

The repository is intentionally being stabilized before the first tagged release.

## Requirements

- WordPress 6.5 or newer
- PHP 8.0 or newer

## Features

- Adds an **Additional CSS classes** field to classic widget forms.
- Accepts multiple whitespace-separated CSS classes.
- Sanitizes and de-duplicates stored class names.
- Adds classes to the normal WordPress `before_widget` wrapper.
- Includes a final-HTML fallback for themes that replace the normal widget wrapper.
- Supports YOOtheme Pro widget output where the rendered wrapper keeps the widget ID.

## Usage

1. Install and activate the plugin.
2. Open **Appearance → Widgets**.
3. Edit a classic widget.
4. Enter one or more class names without a leading dot, for example:

   ```text
   uk-margin-large my-custom-class
   ```

5. Save the widget.

The classes are added to the rendered widget wrapper on the frontend.

## Important compatibility note

The current implementation disables the WordPress block-based widget editor and uses the classic widget editor so that the additional field can be attached to traditional `WP_Widget` forms.

This behavior is under review before the first stable release because it affects the complete Widgets admin screen, not only widgets that use custom classes.

## YOOtheme Pro compatibility

WordPress normally allows the plugin to inject classes through `dynamic_sidebar_params`. Some themes, including YOOtheme Pro configurations, may generate the final widget wrapper later and therefore discard the modified `before_widget` markup.

For that case, the plugin can inspect the final frontend HTML, identify active widgets by their rendered widget ID and add the configured classes to the matching element.

This fallback is also being reviewed for performance and scope before the first stable release.

## Development priorities

The current baseline work is tracked in [issue #1](https://github.com/cemfirat/wordpress-widget-custom-css-classes/issues/1).

Primary goals before the first release:

- consistent metadata and documentation
- predictable behavior with classic widgets
- reliable YOOtheme Pro compatibility
- minimal frontend overhead
- a small and dependable validation/test setup

## License

GPL-2.0-or-later.
