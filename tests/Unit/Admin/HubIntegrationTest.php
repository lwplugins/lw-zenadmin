<?php
/**
 * Checks the synced LW Plugins hub copy (lwplugins/admin-hub).
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Admin\Hub\Assets;
use LightweightPlugins\ZenAdmin\Admin\Hub\Hub;
use LightweightPlugins\ZenAdmin\Admin\Hub\RegistryFallback;
use LightweightPlugins\ZenAdmin\Admin\ParentPage;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * The hub logic itself is tested in the admin-hub repo; these tests prove the
 * copy landed in this plugin's namespace, text domain and asset layout.
 */
final class HubIntegrationTest extends MonkeyTestCase {

	/**
	 * Plugin root.
	 *
	 * @return string
	 */
	private function root(): string {
		return dirname( __DIR__, 3 ) . '/';
	}

	protected function tearDown(): void {
		Hub::reset();
		parent::tearDown();
	}

	public function test_candidate_is_this_plugins_copy(): void {
		Hub::init( $this->root() . 'lw-zenadmin.php' );

		$candidate = Hub::add_candidate( [] )[0];

		$this->assertSame( 'LightweightPlugins\\ZenAdmin\\Admin\\Hub\\Hub', $candidate['class'] );
		$this->assertSame( Hub::VERSION, $candidate['version'] );
	}

	public function test_legacy_parent_page_api_is_kept(): void {
		Functions\expect( 'get_transient' )->once()->andReturn( [ 'lw-zenadmin' => [ 'name' => 'LW ZenAdmin' ] ] );
		Functions\stubTranslationFunctions();

		$this->assertSame( 'lw-plugins', ParentPage::SLUG );
		$this->assertSame( [ 'lw-zenadmin' ], array_keys( ParentPage::get_plugins_registry() ) );
	}

	public function test_bundled_assets_use_this_text_domain(): void {
		$dir = $this->root() . Assets::DIR . '/';
		$js  = (string) file_get_contents( $dir . 'index.js' );

		$this->assertFileExists( $dir . 'index.css' );
		$this->assertFileExists( $dir . 'index.asset.json' );
		$this->assertStringContainsString( '"lw-zenadmin"', $js );
		$this->assertStringNotContainsString( 'lw-admin-hub', $js );
		$this->assertFileExists( $this->root() . 'languages/lw-zenadmin-hu_HU-' . md5( Assets::DIR . '/index.js' ) . '.json' );
	}

	public function test_every_bundled_registry_plugin_has_an_icon(): void {
		Functions\stubTranslationFunctions();

		foreach ( array_keys( RegistryFallback::get() ) as $slug ) {
			$this->assertFileExists( $this->root() . Assets::DIR . '/icons/' . $slug . '.svg' );
		}
	}
}
