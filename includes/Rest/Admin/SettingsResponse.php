<?php
/**
 * Settings response shape.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin;

use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\Registry;

/**
 * The payload GET and a successful POST share: options, the three
 * visibility sections and the screen context.
 */
final class SettingsResponse {

	/**
	 * Plugin documentation.
	 */
	public const DOCS_URL = 'https://github.com/lwplugins/lw-zenadmin#readme';

	/**
	 * Build the payload from storage (fresh section instances).
	 *
	 * @return array{options: array<string, bool>, sections: array<string, array<string, mixed>>, meta: array<string, mixed>}
	 */
	public static function build(): array {
		$options  = [];
		$sections = [];

		foreach ( array_keys( Options::get_defaults() ) as $key ) {
			$options[ $key ] = (bool) Options::get( $key );
		}

		foreach ( Registry::all() as $key => $section ) {
			$sections[ $key ] = $section->present();
		}

		return [
			'options'  => $options,
			'sections' => $sections,
			'meta'     => [
				'defaults'      => Options::get_defaults(),
				'dashboard_url' => admin_url( 'index.php' ),
				'docs_url'      => self::DOCS_URL,
			],
		];
	}
}
