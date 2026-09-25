<?php
/**
 * Tests for PlainTitle (readable labels from menu / admin bar / widget HTML).
 *
 * Samples are the markup WordPress core (7.1) and WooCommerce put in titles.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features;

use LightweightPlugins\ZenAdmin\Features\PlainTitle;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\PlainTitle
 */
final class PlainTitleTest extends TestCase {

	/**
	 * @dataProvider provide_titles
	 */
	public function test_from_html( string $html, string $expected ): void {
		$this->assertSame( $expected, PlainTitle::from_html( $html ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_titles(): array {
		return [
			'plain text'                    => [ 'Tools', 'Tools' ],
			'empty'                         => [ '', '' ],
			'menu: comments in moderation'  => [
				'Hozzászólások <span class="awaiting-mod count-0"><span class="pending-count" aria-hidden="true">0</span><span class="comments-in-moderation-text screen-reader-text">0 hozzászólás moderálás alatt</span></span>',
				'Hozzászólások',
			],
			'menu: updates count'           => [
				'Frissítések <span class="update-plugins count-8"><span class="update-count">8</span></span>',
				'Frissítések',
			],
			'menu: plugins count'           => [
				'Bővítmények <span class="update-plugins count-4"><span class="plugin-count">4</span></span>',
				'Bővítmények',
			],
			'menu: themes count'            => [
				'Themes <span class="update-plugins count-2"><span class="theme-count">2</span></span>',
				'Themes',
			],
			'woocommerce: orders count'     => [
				'Orders <span class="awaiting-mod update-plugins count-3"><span class="processing-count">3</span></span>',
				'Orders',
			],
			'unknown class, count-N only'   => [
				'Inbox <span class="my-badge count-12">12</span>',
				'Inbox',
			],
			'single quotes, stray closer'   => [
				"Media</b> <span class='update-plugins count-1'>1</span>",
				'Media',
			],
			'widget: quick draft'           => [
				'<span class="hide-if-no-js">Gyors vázlat</span> <span class="hide-if-js">Legutóbbi vázlatok</span>',
				'Gyors vázlat',
			],
			'admin bar: wp logo'            => [
				'<span class="ab-icon" aria-hidden="true"></span><span class="screen-reader-text">About WordPress</span>',
				'About WordPress',
			],
			'admin bar: comments'           => [
				'<span class="ab-icon" aria-hidden="true"></span><span class="ab-label awaiting-mod pending-count count-0" aria-hidden="true">0</span><span class="screen-reader-text comments-in-moderation-text">0 Comments in moderation</span>',
				'Comments in moderation',
			],
			'admin bar: updates'            => [
				'<span class="ab-icon" aria-hidden="true"></span><span class="ab-label" aria-hidden="true">1&nbsp;234</span><span class="screen-reader-text updates-available-text">1&nbsp;234 frissítés érhető el</span>',
				'Frissítés érhető el',
			],
			'admin bar: command palette'    => [
				'<span class="ab-icon" aria-hidden="true"></span><span class="ab-label"><kbd>⌘K</kbd><span class="screen-reader-text"> Command Palette</span></span>',
				'Command Palette',
			],
			'admin bar: user info spans'    => [
				"<img alt='' src='https://example.com/a.png' class='avatar avatar-64 photo' height='64' width='64' /><span class='display-name'>Claude Test</span><span class='username'>claude-test</span><span class='display-name edit-profile'>Edit Profile</span>",
				'Claude Test claude-test Edit Profile',
			],
			'inline formatting joins'       => [
				'Word<strong>Press</strong> <em>News</em>',
				'WordPress News',
			],
			'admin bar: new content'        => [
				'<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">New</span>',
				'New',
			],
			'admin bar: my account'         => [
				"Howdy, <span class=\"display-name\">admin</span><img alt='' src='https://example.com/a.png' class='avatar avatar-26 photo' height='26' width='26' />",
				'Howdy, admin',
			],
			'admin bar: search form'        => [
				'<form action="https://example.com/" method="get" id="adminbarsearch"><input class="adminbar-input" name="s" id="adminbar-search" type="text" value="" maxlength="150" /><label for="adminbar-search" class="screen-reader-text">Search</label><input type="submit" class="adminbar-button" value="Search" /></form>',
				'Search',
			],
			'entities and nbsp collapse'    => [
				"Tom &amp; Jerry&nbsp;&nbsp;\n Menu <!-- note -->",
				'Tom & Jerry Menu',
			],
			'number in a label stays'       => [
				'Top 10 Posts',
				'Top 10 Posts',
			],
			'only a number, no fallback'    => [
				'<span class="ab-label">5</span>',
				'5',
			],
		];
	}
}
