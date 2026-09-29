<?php
/**
 * Real WordPress integration checks for WordPress Widget Custom CSS Classes.
 */

function ccf_integration_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

ccf_integration_assert(
	class_exists( 'CCF_Widget_CSS_Classes' ),
	'Plugin class is not loaded.'
);

ccf_integration_assert(
	class_exists( 'CCF_Widget_CSS_Classes_Updater' ),
	'Updater class is not loaded.'
);

ccf_integration_assert(
	'wordpress-widget-custom-css-classes/widget-css-classes.php' === plugin_basename( CCF_WIDGET_CSS_CLASSES_FILE ),
	'Packaged plugin basename is not stable.'
);

ccf_integration_assert(
	false === has_filter( 'use_widgets_block_editor', '__return_false' ),
	'Plugin must not globally disable the Widgets Block Editor.'
);

ccf_integration_assert(
	false !== has_filter( 'update_plugins_github.com' ),
	'GitHub update hook is not registered.'
);

function ccf_find_plugin_instance() {
	global $wp_filter;

	if ( empty( $wp_filter['dynamic_sidebar_params'] ) || empty( $wp_filter['dynamic_sidebar_params']->callbacks ) ) {
		return null;
	}

	foreach ( $wp_filter['dynamic_sidebar_params']->callbacks as $callbacks ) {
		foreach ( $callbacks as $entry ) {
			if (
				isset( $entry['function'] )
				&& is_array( $entry['function'] )
				&& isset( $entry['function'][0] )
				&& $entry['function'][0] instanceof CCF_Widget_CSS_Classes
			) {
				return $entry['function'][0];
			}
		}
	}

	return null;
}

$plugin = ccf_find_plugin_instance();
ccf_integration_assert(
	$plugin instanceof CCF_Widget_CSS_Classes,
	'Could not resolve the registered plugin instance.'
);

final class CCF_Widget_CSS_Classes_Integration_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'ccf_widget_css_classes_integration',
			'CCF Widget CSS Classes Integration'
		);
	}

	public function widget( $args, $instance ) {}

	public function form( $instance ) {}
}

$widget = new CCF_Widget_CSS_Classes_Integration_Widget();
$widget->_set( 2 );

ob_start();
do_action(
	'in_widget_form',
	$widget,
	null,
	array(
		'_ccf_css_classes' => 'alpha beta',
	)
);
$form = ob_get_clean();

ccf_integration_assert(
	false !== strpos( $form, 'Additional CSS classes' ),
	'Widget form does not contain the additional CSS-class field.'
);

$saved = apply_filters(
	'widget_update_callback',
	array(),
	array(
		'_ccf_css_classes' => 'alpha alpha bad" x=y',
	),
	array(),
	$widget
);

ccf_integration_assert(
	isset( $saved['_ccf_css_classes'] )
		&& 'alpha bad xy' === $saved['_ccf_css_classes'],
	'Widget class sanitization is not stable.'
);

update_option(
	$widget->option_name,
	array(
		2 => array(
			'_ccf_css_classes' => 'alpha beta',
		),
		'_multiwidget' => 1,
	)
);

global $wp_registered_widgets;
$wp_registered_widgets['ccf_widget_css_classes_integration-2'] = array(
	'callback' => array( $widget, 'display_callback' ),
	'params'   => array(
		array(
			'number' => 2,
		),
	),
);

$params = array(
	array(
		'widget_id'     => 'ccf_widget_css_classes_integration-2',
		'before_widget' => '<section id="ccf_widget_css_classes_integration-2" class="widget">',
	),
);

$filtered = $plugin->filter_sidebar_params( $params );

ccf_integration_assert(
	false !== strpos( $filtered[0]['before_widget'], 'widget alpha beta' ),
	'Standard WordPress widget wrapper did not receive the configured classes.'
);

add_filter(
	'sidebars_widgets',
	static function () {
		return array(
			'integration-sidebar'  => array( 'ccf_widget_css_classes_integration-2' ),
			'wp_inactive_widgets' => array(),
			'array_version'       => 3,
		);
	}
);

ob_start();
$outer_level = ob_get_level();
$plugin->start_html_buffer();

ccf_integration_assert(
	$outer_level + 1 === ob_get_level(),
	'Final HTML fallback did not start for an active configured widget.'
);

echo '<html><body><div id="ccf_widget_css_classes_integration-2" class="widget">Integration</div></body></html>';
ob_end_flush();
$html = ob_get_clean();

ccf_integration_assert(
	false !== strpos( $html, 'class="widget alpha beta"' ),
	'Final HTML fallback did not add configured classes through WP_HTML_Tag_Processor.'
);

$valid_release = CCF_Widget_CSS_Classes_Updater::parse_release(
	array(
		'draft'      => false,
		'prerelease' => false,
		'tag_name'   => 'v0.0.2',
		'body'       => "Requires WordPress: 6.5\nRequires PHP: 8.0\n\nIntegration release.",
		'assets'     => array(
			array(
				'name'                 => 'wordpress-widget-custom-css-classes.zip',
				'browser_download_url' => 'https://github.com/cemfirat/wordpress-widget-custom-css-classes/releases/download/v0.0.2/wordpress-widget-custom-css-classes.zip',
				'state'                => 'uploaded',
				'size'                 => 1234,
			),
		),
	)
);

ccf_integration_assert(
	is_array( $valid_release ) && '0.0.2' === $valid_release['version'],
	'Updater release validation failed in real WordPress.'
);

echo "WordPress integration OK\n";
