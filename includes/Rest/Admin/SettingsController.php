<?php
/**
 * Settings REST controller.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin;

use LightweightPlugins\ZenAdmin\Admin\SettingsSanitizer;
use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\Registry;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET/POST lw-zenadmin/v1/admin/settings: the feature switches and the
 * widget, menu and admin bar visibility lists, read and saved together.
 */
final class SettingsController {

	/**
	 * Largest accepted request body, in bytes.
	 */
	public const MAX_BYTES = 65536;

	/**
	 * Register the routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		Routes::add(
			'/admin/settings',
			[
				WP_REST_Server::READABLE  => 'get_settings',
				WP_REST_Server::CREATABLE => 'save_settings',
			],
			$this
		);
	}

	/**
	 * Current state.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		return new WP_REST_Response( SettingsResponse::build() );
	}

	/**
	 * Partial, atomic update: only the submitted keys change, and nothing is
	 * saved when any of them is invalid.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_settings( WP_REST_Request $request ) {
		if ( strlen( (string) $request->get_body() ) > self::MAX_BYTES ) {
			return Routes::error( 'lw_zenadmin_too_large', __( 'The request is too large.', 'lw-zenadmin' ), 413 );
		}

		// Null for an empty or undecodable body at runtime, despite the stub.
		$body = $request->get_json_params();
		$body = empty( $body ) ? [] : $body;

		$sections = Registry::all();
		$input    = new SettingsInput( $body, $sections );

		if ( [] !== $input->errors() ) {
			return Routes::error(
				'lw_zenadmin_invalid',
				__( 'Some settings are not valid. Nothing was saved.', 'lw-zenadmin' ),
				400,
				[ 'fields' => $input->errors() ]
			);
		}

		if ( [] !== $input->options() ) {
			Options::save( SettingsSanitizer::sanitize_options( array_merge( Options::get_all(), $input->options() ) ) );
		}

		foreach ( $input->changes() as $key => $changes ) {
			$section = $sections[ $key ];
			$section->save(
				SettingsSanitizer::merge_visibility(
					$changes,
					$section->discovered_ids(),
					[ $section, 'is_visible' ],
					[ $section, 'is_protected' ]
				)
			);
		}

		return new WP_REST_Response( SettingsResponse::build() );
	}
}
