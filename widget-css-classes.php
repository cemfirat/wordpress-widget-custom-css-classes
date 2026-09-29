<?php
/**
 * Plugin Name: WordPress Widget Custom CSS Classes
 * Plugin URI: https://github.com/cemfirat/wordpress-widget-custom-css-classes
 * Description: Adds custom CSS classes to classic WordPress widgets. Compatible with YOOtheme Pro when the theme generates the widget wrapper.
 * Version: 0.0.1
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Cem Firat
 * Author URI: https://cemfirat.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/cemfirat/wordpress-widget-custom-css-classes
 * Text Domain: wordpress-widget-custom-css-classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CCF_Widget_CSS_Classes {

	private const FIELD_KEY = '_ccf_css_classes';

	public function __construct() {
		// Klassischen Widget-Editor verwenden, damit PHP-Widgets ihr Formular anzeigen.
		add_filter( 'use_widgets_block_editor', '__return_false', 100 );

		add_action( 'in_widget_form', array( $this, 'render_field' ), 10, 3 );
		add_filter( 'widget_update_callback', array( $this, 'save_field' ), 10, 4 );

		// Standard-WordPress-Ausgabe: Klassen direkt in before_widget einfügen.
		add_filter( 'dynamic_sidebar_params', array( $this, 'filter_sidebar_params' ), PHP_INT_MAX );

		// Theme-Fallback, insbesondere für YOOtheme Pro: finales HTML anhand der
		// tatsächlichen Widget-ID ergänzen. YOOtheme baut den Wrapper teilweise
		// nach dem WordPress-Filter neu auf und verwirft dadurch before_widget.
		add_action( 'template_redirect', array( $this, 'start_html_buffer' ), 0 );
	}

	public function render_field( $widget, $return, $instance ) {
		$value = isset( $instance[ self::FIELD_KEY ] ) ? (string) $instance[ self::FIELD_KEY ] : '';
		?>
		<p class="ccf-widget-css-classes-field">
			<label for="<?php echo esc_attr( $widget->get_field_id( self::FIELD_KEY ) ); ?>">
				<strong><?php esc_html_e( 'Zusätzliche CSS-Klassen', 'wordpress-widget-custom-css-classes' ); ?></strong>
			</label>
			<input
				type="text"
				class="widefat"
				id="<?php echo esc_attr( $widget->get_field_id( self::FIELD_KEY ) ); ?>"
				name="<?php echo esc_attr( $widget->get_field_name( self::FIELD_KEY ) ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				placeholder="<?php echo esc_attr__( 'z. B. uk-margin-large meine-klasse', 'wordpress-widget-custom-css-classes' ); ?>"
				autocomplete="off"
			>
			<small><?php esc_html_e( 'Mehrere Klassen mit Leerzeichen trennen. Ohne Punkt eingeben.', 'wordpress-widget-custom-css-classes' ); ?></small>
		</p>
		<?php
	}

	public function save_field( $instance, $new_instance, $old_instance, $widget ) {
		if ( false === $instance || ! is_array( $instance ) ) {
			return $instance;
		}

		$value = isset( $new_instance[ self::FIELD_KEY ] )
			? $this->sanitize_class_list( wp_unslash( (string) $new_instance[ self::FIELD_KEY ] ) )
			: '';

		if ( '' === $value ) {
			unset( $instance[ self::FIELD_KEY ] );
		} else {
			$instance[ self::FIELD_KEY ] = $value;
		}

		return $instance;
	}

	public function filter_sidebar_params( $params ) {
		if ( empty( $params[0]['widget_id'] ) || empty( $params[0]['before_widget'] ) ) {
			return $params;
		}

		$classes = $this->get_classes_for_widget( (string) $params[0]['widget_id'] );
		if ( empty( $classes ) ) {
			return $params;
		}

		$params[0]['before_widget'] = $this->add_classes_to_first_tag(
			(string) $params[0]['before_widget'],
			$classes
		);

		return $params;
	}

	public function start_html_buffer() {
		if (
			is_admin()
			|| wp_doing_ajax()
			|| wp_is_json_request()
			|| is_feed()
			|| is_trackback()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		) {
			return;
		}

		ob_start( array( $this, 'filter_final_html' ) );
	}

	public function filter_final_html( $html ) {
		if ( ! is_string( $html ) || '' === $html || false === stripos( $html, '<html' ) ) {
			return $html;
		}

		$map = $this->get_active_widget_class_map();
		if ( empty( $map ) ) {
			return $html;
		}

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new WP_HTML_Tag_Processor( $html );

			while ( $processor->next_tag() ) {
				$id = $processor->get_attribute( 'id' );
				if ( ! is_string( $id ) || ! isset( $map[ $id ] ) ) {
					continue;
				}

				foreach ( $map[ $id ] as $class_name ) {
					$processor->add_class( $class_name );
				}
			}

			return $processor->get_updated_html();
		}

		// Sicherheitsfallback; WordPress 6.2+ besitzt normalerweise den Tag Processor.
		foreach ( $map as $widget_id => $classes ) {
			$html = preg_replace_callback(
				'/<([a-z][a-z0-9:-]*)([^>]*\sid=(?:"|\')' . preg_quote( $widget_id, '/' ) . '(?:"|\')[^>]*)>/i',
				function ( $matches ) use ( $classes ) {
					return $this->add_classes_to_first_tag( $matches[0], $classes );
				},
				$html,
				1
			) ?: $html;
		}

		return $html;
	}

	private function get_active_widget_class_map() {
		$sidebars = wp_get_sidebars_widgets();
		if ( ! is_array( $sidebars ) ) {
			return array();
		}

		$map = array();
		foreach ( $sidebars as $sidebar_id => $widget_ids ) {
			if ( 'wp_inactive_widgets' === $sidebar_id || 'array_version' === $sidebar_id || ! is_array( $widget_ids ) ) {
				continue;
			}

			foreach ( $widget_ids as $widget_id ) {
				$widget_id = (string) $widget_id;
				$classes   = $this->get_classes_for_widget( $widget_id );
				if ( ! empty( $classes ) ) {
					$map[ $widget_id ] = $classes;
				}
			}
		}

		return $map;
	}

	private function get_classes_for_widget( $widget_id ) {
		$instance = $this->get_widget_instance( $widget_id );
		if ( empty( $instance[ self::FIELD_KEY ] ) ) {
			return array();
		}

		$list = $this->sanitize_class_list( (string) $instance[ self::FIELD_KEY ] );
		return '' === $list ? array() : preg_split( '/\s+/', $list );
	}

	private function get_widget_instance( $widget_id ) {
		global $wp_registered_widgets;

		if ( empty( $wp_registered_widgets[ $widget_id ]['callback'] ) ) {
			return array();
		}

		$callback = $wp_registered_widgets[ $widget_id ]['callback'];
		if ( ! is_array( $callback ) || empty( $callback[0] ) || ! $callback[0] instanceof WP_Widget ) {
			return array();
		}

		$widget = $callback[0];
		$number = 0;

		if ( ! empty( $wp_registered_widgets[ $widget_id ]['params'][0]['number'] ) ) {
			$number = absint( $wp_registered_widgets[ $widget_id ]['params'][0]['number'] );
		} elseif ( preg_match( '/-(\d+)$/', $widget_id, $matches ) ) {
			$number = absint( $matches[1] );
		}

		$settings = $widget->get_settings();
		if ( ! is_array( $settings ) ) {
			return array();
		}

		if ( $number > 0 && isset( $settings[ $number ] ) && is_array( $settings[ $number ] ) ) {
			return $settings[ $number ];
		}

		return isset( $settings[ self::FIELD_KEY ] ) ? $settings : array();
	}

	private function sanitize_class_list( $value ) {
		$value = sanitize_text_field( (string) $value );
		$items = preg_split( '/\s+/', trim( $value ) );
		$clean = array();

		if ( ! is_array( $items ) ) {
			return '';
		}

		foreach ( $items as $item ) {
			$item = preg_replace( '/[\x00-\x20\x7F"\'<>`=]/u', '', trim( $item ) );
			if ( is_string( $item ) && '' !== $item ) {
				$clean[] = $item;
			}
		}

		return implode( ' ', array_values( array_unique( $clean ) ) );
	}

	private function add_classes_to_first_tag( $html, array $classes ) {
		if ( '' === $html || empty( $classes ) ) {
			return $html;
		}

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new WP_HTML_Tag_Processor( $html );
			if ( $processor->next_tag() ) {
				foreach ( $classes as $class_name ) {
					$processor->add_class( $class_name );
				}
				return $processor->get_updated_html();
			}
		}

		return preg_replace_callback(
			'/<([a-z][a-z0-9:-]*)(\s[^>]*)?>/i',
			static function ( $matches ) use ( $classes ) {
				$attributes = isset( $matches[2] ) ? $matches[2] : '';
				$extra      = implode( ' ', $classes );

				if ( preg_match( '/\sclass\s*=\s*(["\'])(.*?)\1/i', $attributes ) ) {
					$attributes = preg_replace_callback(
						'/\sclass\s*=\s*(["\'])(.*?)\1/i',
						static function ( $class_matches ) use ( $extra ) {
							return ' class=' . $class_matches[1] . esc_attr( trim( $class_matches[2] . ' ' . $extra ) ) . $class_matches[1];
						},
						$attributes,
						1
					);
				} else {
					$attributes .= ' class="' . esc_attr( $extra ) . '"';
				}

				return '<' . $matches[1] . $attributes . '>';
			},
			$html,
			1
		) ?: $html;
	}
}

new CCF_Widget_CSS_Classes();
