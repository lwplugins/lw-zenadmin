<?php
/**
 * WP-CLI Commands.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\CLI;

use LightweightPlugins\ZenAdmin\Options;
use WP_CLI;

/**
 * Manage LW ZenAdmin features via WP-CLI.
 *
 * ## EXAMPLES
 *
 *     wp lw-zenadmin status
 *     wp lw-zenadmin enable notices_enabled
 *     wp lw-zenadmin disable widgets_enabled
 */
final class Commands {

	/**
	 * Show feature status.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin status
	 *
	 * @subcommand status
	 */
	public function status(): void {
		$options  = Options::get_all();
		$defaults = Options::get_defaults();

		$items = [];
		foreach ( array_keys( $defaults ) as $key ) {
			$items[] = [
				'feature' => $key,
				'status'  => $options[ $key ] ? 'enabled' : 'disabled',
			];
		}

		WP_CLI\Utils\format_items( 'table', $items, [ 'feature', 'status' ] );
	}

	/**
	 * Enable a feature.
	 *
	 * ## OPTIONS
	 *
	 * <feature>
	 * : The feature to enable (notices_enabled or widgets_enabled).
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin enable notices_enabled
	 *     wp lw-zenadmin enable widgets_enabled
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function enable( array $args ): void {
		$this->set_feature( $args[0], true );
	}

	/**
	 * Disable a feature.
	 *
	 * ## OPTIONS
	 *
	 * <feature>
	 * : The feature to disable (notices_enabled or widgets_enabled).
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin disable notices_enabled
	 *     wp lw-zenadmin disable widgets_enabled
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function disable( array $args ): void {
		$this->set_feature( $args[0], false );
	}

	/**
	 * Set feature status.
	 *
	 * @param string $feature Feature key.
	 * @param bool   $enabled Enable or disable.
	 */
	private function set_feature( string $feature, bool $enabled ): void {
		$defaults = Options::get_defaults();

		if ( ! array_key_exists( $feature, $defaults ) ) {
			WP_CLI::error( "Unknown feature: {$feature}. Available: " . implode( ', ', array_keys( $defaults ) ) );
		}

		$options             = Options::get_all();
		$options[ $feature ] = $enabled;
		Options::save( $options );

		$action = $enabled ? 'enabled' : 'disabled';
		WP_CLI::success( "Feature '{$feature}' {$action}." );
	}
}
