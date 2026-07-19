<?php
/**
 * PHPUnit bootstrap file.
 *
 * Unit tests run WITHOUT WordPress: only the Composer autoloader is loaded,
 * which also pulls in Brain Monkey. WordPress functions are stubbed per test
 * via Brain\Monkey — the setUp()/tearDown() lifecycle lives in
 * tests/Unit/MonkeyTestCase.php.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// A hand-rolled WP_Admin_Bar test double: WordPress core's real class isn't
// available in this WordPress-free environment, and the wordpress-stubs
// package (used only by PHPStan) declares get_nodes() `final`, which would
// block subclassing it for a test fixture instead.
require_once __DIR__ . '/Fixtures/WpAdminBarFake.php';
