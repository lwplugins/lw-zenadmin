<?php
/**
 * Visibility section contract.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

/**
 * One discovered-items visibility list (widgets, menus, admin bar):
 * how it is presented, which IDs exist, which are protected, how it is saved.
 */
interface SectionInterface {

	/**
	 * Section key in the REST payload.
	 *
	 * @return string
	 */
	public function key(): string;

	/**
	 * Discovered item IDs, as strings.
	 *
	 * @return array<int, string>
	 */
	public function discovered_ids(): array;

	/**
	 * Whether an item can never be hidden.
	 *
	 * @param string $id Item ID.
	 * @return bool
	 */
	public function is_protected( string $id ): bool;

	/**
	 * Current effective visibility of an item.
	 *
	 * @param string $id Item ID.
	 * @return bool
	 */
	public function is_visible( string $id ): bool;

	/**
	 * Grouped rows for the screen.
	 *
	 * @return array{saved: bool, count: int, groups: array<int, array<string, mixed>>}
	 */
	public function present(): array;

	/**
	 * Persist the visible list.
	 *
	 * @param array<int, string> $visible Visible, non-protected item IDs.
	 * @return void
	 */
	public function save( array $visible ): void;
}
