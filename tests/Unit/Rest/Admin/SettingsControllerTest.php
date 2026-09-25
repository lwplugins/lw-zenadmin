<?php
/**
 * Tests for the settings REST controller, end to end over stubbed storage.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin;

use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Rest\Admin\SettingsController;
use WP_Error;
use WP_REST_Request;

/**
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\SettingsController
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\SettingsResponse
 */
final class SettingsControllerTest extends OptionStoreTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->seed_discovery();
	}

	/**
	 * POST a body.
	 *
	 * @param array<string, mixed> $body Body.
	 * @return mixed
	 */
	private function post( array $body ) {
		$response = ( new SettingsController() )->save_settings( new WP_REST_Request( $body ) );

		return $response instanceof WP_Error ? $response : $response->get_data();
	}

	public function test_get_returns_options_sections_and_meta(): void {
		$data = ( new SettingsController() )->get_settings()->get_data();

		$this->assertSame( Options::get_defaults(), $data['options'] );
		$this->assertSame( [ 'widgets', 'menus', 'adminbar' ], array_keys( $data['sections'] ) );
		$this->assertSame( 3, $data['sections']['widgets']['count'] );
		$this->assertSame( 'https://example.test/wp-admin/index.php', $data['meta']['dashboard_url'] );
	}

	public function test_saving_one_option_keeps_the_others(): void {
		$this->store[ Options::OPTION_NAME ] = [
			'notices_enabled'  => false,
			'widgets_enabled'  => true,
			'menu_enabled'     => false,
			'adminbar_enabled' => true,
		];

		$data = $this->post( [ 'options' => [ 'menu_enabled' => true ] ] );

		$this->assertSame(
			[
				'notices_enabled'  => false,
				'widgets_enabled'  => true,
				'menu_enabled'     => true,
				'adminbar_enabled' => true,
			],
			$this->store[ Options::OPTION_NAME ]
		);
		$this->assertTrue( $data['options']['menu_enabled'] );
	}

	public function test_hiding_a_widget_keeps_the_default_state_of_the_rest(): void {
		$this->post( [ 'widgets' => [ 'dashboard_activity' => false ] ] );

		// Never saved before: Core + WooCommerce were visible, Acme hidden.
		$this->assertSame( [ 'wc_admin_dashboard' ], $this->store[ Options::WIDGET_SETTINGS ] );
	}

	public function test_menu_save_leaves_protected_items_out_of_the_stored_list(): void {
		$this->post( [ 'menus' => [ 'tools.php::import.php' => false ] ] );

		$this->assertSame( [ 'tools.php', 'acme' ], $this->store[ Options::MENU_SETTINGS ] );
	}

	public function test_admin_bar_save_answers_the_fresh_visibility(): void {
		$data = $this->post( [ 'adminbar' => [ 'wp-logo' => false ] ] );

		$items = array_column( $data['sections']['adminbar']['groups'][0]['items'], 'visible', 'id' );
		$this->assertFalse( $items['wp-logo'] );
		$this->assertTrue( $data['sections']['adminbar']['saved'] );
	}

	public function test_an_invalid_body_saves_nothing(): void {
		$result = $this->post(
			[
				'options'  => [ 'menu_enabled' => true ],
				'adminbar' => [ 'logout' => false ],
			]
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'lw_zenadmin_invalid', $result->get_error_code() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
		$this->assertArrayHasKey( 'adminbar', $result->get_error_data()['fields'] );
		$this->assertSame( [], $this->written );
	}

	public function test_an_oversized_body_is_refused_with_413(): void {
		$request = new WP_REST_Request( [], str_repeat( 'x', SettingsController::MAX_BYTES + 1 ) );

		$result = ( new SettingsController() )->save_settings( $request );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 413, $result->get_error_data()['status'] );
	}

	public function test_an_empty_body_changes_nothing_and_answers_the_state(): void {
		$data = $this->post( [] );

		$this->assertSame( [], $this->written );
		$this->assertArrayHasKey( 'sections', $data );
	}
}
