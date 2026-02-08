<?php
/**
 * Widget Manager feature.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Manages dashboard widget visibility.
 *
 * Discovers all registered widgets and hides disabled ones.
 */
final class WidgetManager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'filter_widgets' ], 999 );
	}

	/**
	 * Filter dashboard widgets based on saved settings.
	 *
	 * Runs at priority 999 so every plugin has already registered its widgets.
	 *
	 * @return void
	 */
	public function filter_widgets(): void {
		global $wp_meta_boxes;

		if ( empty( $wp_meta_boxes['dashboard'] ) || ! is_array( $wp_meta_boxes['dashboard'] ) ) {
			return;
		}

		$settings   = Options::get_widget_settings();
		$discovered = Options::get_discovered_widgets();
		$changed    = false;

		foreach ( $wp_meta_boxes['dashboard'] as $context => $priorities ) {
			if ( ! is_array( $priorities ) ) {
				continue;
			}

			foreach ( $priorities as $priority => $widgets ) {
				if ( ! is_array( $widgets ) ) {
					continue;
				}

				foreach ( $widgets as $widget_id => $widget ) {
					if ( false === $widget ) {
						continue;
					}

					$title = isset( $widget['title'] )
						? wp_strip_all_tags( $widget['title'] )
						: $widget_id;

					if ( ! isset( $discovered[ $widget_id ] ) || $discovered[ $widget_id ] !== $title ) {
						$discovered[ $widget_id ] = $title;
						$changed                  = true;
					}

					if ( ! self::is_widget_visible( $widget_id, $settings ) ) {
						unset( $wp_meta_boxes['dashboard'][ $context ][ $priority ][ $widget_id ] );
					}
				}
			}
		}

		if ( $changed ) {
			Options::save_discovered_widgets( $discovered );
		}
	}

	/**
	 * Determine if a widget should be visible.
	 *
	 * @param string              $widget_id Widget identifier.
	 * @param array<string>|false $settings  Saved settings or false if never saved.
	 * @return bool
	 */
	public static function is_widget_visible( string $widget_id, array|false $settings ): bool {
		// No settings saved yet — use defaults.
		if ( false === $settings ) {
			return CoreWidgets::is_default( $widget_id );
		}

		return in_array( $widget_id, $settings, true );
	}
}
