<?php
/**
 * Tests for the settings save validation.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin;

use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\Registry;
use LightweightPlugins\ZenAdmin\Rest\Admin\SettingsInput;

/**
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\SettingsInput
 */
final class SettingsInputTest extends OptionStoreTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->seed_discovery();
	}

	/**
	 * Validate a body.
	 *
	 * @param array<array-key, mixed> $body Body.
	 * @return SettingsInput
	 */
	private function input( array $body ): SettingsInput {
		return new SettingsInput( $body, Registry::all() );
	}

	public function test_a_valid_body_yields_options_and_section_changes(): void {
		$input = $this->input(
			[
				'options' => [ 'menu_enabled' => true ],
				'menus'   => [ 'tools.php' => false ],
			]
		);

		$this->assertSame( [], $input->errors() );
		$this->assertSame( [ 'menu_enabled' => true ], $input->options() );
		$this->assertSame( [ 'menus' => [ 'tools.php' => false ] ], $input->changes() );
	}

	/**
	 * @dataProvider provide_booleans
	 *
	 * @param mixed $value    Submitted value.
	 * @param bool  $expected Parsed value.
	 */
	public function test_accepts_strict_booleans( $value, bool $expected ): void {
		$this->assertSame( [ 'notices_enabled' => $expected ], $this->input( [ 'options' => [ 'notices_enabled' => $value ] ] )->options() );
	}

	/**
	 * @return array<string, array{0: mixed, 1: bool}>
	 */
	public static function provide_booleans(): array {
		return [
			'true'  => [ true, true ],
			'false' => [ false, false ],
			'1'     => [ 1, true ],
			'0'     => [ 0, false ],
			'"1"'   => [ '1', true ],
			'"0"'   => [ '0', false ],
		];
	}

	/**
	 * @dataProvider provide_invalid_bodies
	 *
	 * @param array<array-key, mixed> $body  Body.
	 * @param string                  $field Field expected to carry an error.
	 */
	public function test_flags_the_field_of_an_invalid_value( array $body, string $field ): void {
		$this->assertArrayHasKey( $field, $this->input( $body )->errors() );
	}

	/**
	 * @return array<string, array{0: array<array-key, mixed>, 1: string}>
	 */
	public static function provide_invalid_bodies(): array {
		return [
			'unknown top-level key'    => [ [ 'bogus' => true ], 'bogus' ],
			'options not an object'    => [ [ 'options' => 'yes' ], 'options' ],
			'unknown option'           => [ [ 'options' => [ 'evil' => true ] ], 'evil' ],
			'option not a boolean'     => [ [ 'options' => [ 'menu_enabled' => 'yes' ] ], 'menu_enabled' ],
			'section not an object'    => [ [ 'widgets' => true ], 'widgets' ],
			'undiscovered widget'      => [ [ 'widgets' => [ 'nope' => true ] ], 'widgets' ],
			'menu value not a boolean' => [ [ 'menus' => [ 'tools.php' => 'off' ] ], 'menus' ],
			'hiding a protected menu'  => [ [ 'menus' => [ 'lw-plugins' => false ] ], 'menus' ],
			'hiding Log Out'           => [ [ 'adminbar' => [ 'logout' => false ] ], 'adminbar' ],
		];
	}

	public function test_a_protected_item_may_be_sent_as_visible(): void {
		$input = $this->input( [ 'adminbar' => [ 'logout' => true ] ] );

		$this->assertSame( [], $input->errors() );
	}

	public function test_caps_the_messages_per_field(): void {
		$ids = array_fill_keys( array_map( static fn ( int $n ): string => 'x' . $n, range( 1, 20 ) ), true );

		$this->assertCount( 5, $this->input( [ 'widgets' => $ids ] )->errors()['widgets'] );
	}
}
