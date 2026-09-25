<?php
/**
 * Tests for the visibility sections' REST rows.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin\Sections;

use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\AdminBarSection;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\MenuSection;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\WidgetSection;
use LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin\OptionStoreTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\Sections\AbstractSection
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\Sections\WidgetSection
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\Sections\MenuSection
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\Sections\AdminBarSection
 */
final class SectionsTest extends OptionStoreTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->seed_discovery();
	}

	/**
	 * Rows of a presented section, keyed by group then ID.
	 *
	 * @param array<string, mixed> $presented Presented section.
	 * @return array<string, array<string, array<string, mixed>>>
	 */
	private function rows( array $presented ): array {
		$rows = [];

		foreach ( $presented['groups'] as $group ) {
			$rows[ $group['id'] ] = array_column( $group['items'], null, 'id' );
		}

		return $rows;
	}

	public function test_widgets_group_by_source_with_defaults_when_never_saved(): void {
		$presented = ( new WidgetSection() )->present();
		$rows      = $this->rows( $presented );

		$this->assertFalse( $presented['saved'] );
		$this->assertSame( [ 'core', 'woocommerce', 'third_party' ], array_keys( $rows ) );
		$this->assertTrue( $rows['core']['dashboard_activity']['visible'] );
		$this->assertTrue( $rows['woocommerce']['wc_admin_dashboard']['visible'] );
		$this->assertFalse( $rows['third_party']['acme_stats']['visible'] );
	}

	public function test_widgets_follow_the_saved_list(): void {
		$this->store[ Options::WIDGET_SETTINGS ] = [ 'acme_stats' ];

		$rows = $this->rows( ( new WidgetSection() )->present() );

		$this->assertFalse( $rows['core']['dashboard_activity']['visible'] );
		$this->assertTrue( $rows['third_party']['acme_stats']['visible'] );
	}

	public function test_empty_groups_are_left_out(): void {
		$this->store[ Options::DISCOVERED_WIDGETS ] = [ 'acme_stats' => 'Acme Stats' ];

		$presented = ( new WidgetSection() )->present();

		$this->assertSame( [ 'third_party' ], array_column( $presented['groups'], 'id' ) );
		$this->assertSame( 1, $presented['count'] );
	}

	public function test_menus_nest_submenus_and_lock_protected_items(): void {
		$this->store[ Options::MENU_SETTINGS ] = [ 'tools.php' ];

		$rows = $this->rows( ( new MenuSection() )->present() );

		$this->assertSame( [ 'index.php', 'tools.php', 'tools.php::import.php' ], array_keys( $rows['core'] ) );
		$this->assertSame( 1, $rows['core']['tools.php::import.php']['depth'] );
		$this->assertFalse( $rows['core']['tools.php::import.php']['visible'] );
		$this->assertTrue( $rows['core']['index.php']['protected'] );
		$this->assertTrue( $rows['core']['index.php']['visible'] );
		$this->assertTrue( $rows['lw_plugins']['lw-plugins']['protected'] );
		$this->assertFalse( $rows['third_party']['acme']['visible'] );
	}

	/**
	 * @dataProvider provide_menu_protection
	 *
	 * @param string $id       Menu slug or submenu key.
	 * @param bool   $expected Protected.
	 */
	public function test_menu_protection_matches_the_filter_rule( string $id, bool $expected ): void {
		$this->assertSame( $expected, ( new MenuSection() )->is_protected( $id ) );
	}

	/**
	 * @return array<string, array{0: string, 1: bool}>
	 */
	public static function provide_menu_protection(): array {
		return [
			'protected top level'                 => [ 'plugins.php', true ],
			'lw- slug'                            => [ 'lw-seo', true ],
			'plain top level'                     => [ 'tools.php', false ],
			'protected parent, protected child'   => [ 'options-general.php::plugins.php', true ],
			'protected parent, ordinary child'    => [ 'options-general.php::options-writing.php', false ],
		];
	}

	public function test_admin_bar_rows_are_depth_first_with_ids_as_missing_titles(): void {
		$rows = $this->rows( ( new AdminBarSection() )->present() );

		$this->assertSame( [ 'wp-logo', 'top-secondary', 'my-account', 'logout' ], array_keys( $rows['core'] ) );
		$this->assertSame( 2, $rows['core']['logout']['depth'] );
		$this->assertSame( 'top-secondary', $rows['core']['top-secondary']['title'] );
		$this->assertTrue( $rows['core']['logout']['protected'] );
		$this->assertTrue( $rows['third_party']['acme-bar']['visible'] );
	}
}
