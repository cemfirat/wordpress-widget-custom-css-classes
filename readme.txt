=== WordPress Widget Custom CSS Classes ===
Contributors: cemfirat
Tags: widgets, css, classes, yootheme
Requires at least: 6.5
Requires PHP: 8.0
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add custom CSS classes to classic WordPress widgets, including compatibility handling for YOOtheme Pro.

== Description ==

WordPress Widget Custom CSS Classes adds an additional CSS-class field to classic WP_Widget forms.

Multiple class names can be entered as a whitespace-separated list. The plugin sanitizes and de-duplicates the saved values and adds them to the widget wrapper on the frontend.

For normal WordPress widget output, classes are inserted through the standard dynamic_sidebar_params flow. Some themes can rebuild the final widget wrapper after that filter has run. The plugin therefore includes a compatibility fallback that can match active widgets by their rendered widget ID and add the configured classes to the final HTML.

The current development version uses the classic Widgets administration screen so traditional WP_Widget forms can expose the additional field. This behavior and the final-HTML fallback are being reviewed before the first stable release.

== Installation ==

1. Download the plugin source or a release ZIP when one is available.
2. Upload the plugin through Plugins > Add New > Upload Plugin, or place the plugin directory in wp-content/plugins.
3. Activate WordPress Widget Custom CSS Classes.
4. Open Appearance > Widgets.
5. Edit a classic widget and enter one or more class names without a leading dot.
6. Save the widget.

Example:

uk-margin-large my-custom-class

== Frequently Asked Questions ==

= Can I enter more than one class? =

Yes. Separate class names with whitespace.

= Should class names include a dot? =

No. Enter class names only, for example my-custom-class rather than .my-custom-class.

= Why is there a YOOtheme Pro fallback? =

Some theme configurations generate the final widget wrapper after the normal WordPress widget filters have run. In that situation a modified before_widget value can be discarded. The fallback identifies the final widget element by its WordPress widget ID and adds the configured classes there.

= Does the plugin support block widgets? =

The current development version targets classic WP_Widget forms. Broader block-widget support is not claimed for the first release.

== Changelog ==

= 0.0.1 =
* Initial development baseline.
* Add custom class storage and frontend output for classic widgets.
* Add compatibility handling for YOOtheme Pro-style wrapper replacement.
* Align repository branding, metadata and licensing with the related Cem Firat WordPress plugins.
