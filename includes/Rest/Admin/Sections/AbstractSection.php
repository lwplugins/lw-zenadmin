<?php
/**
 * Shared presentation of a visibility section.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

/**
 * Turns a section's classic grouping (group ID => label + items keyed by ID)
 * into the REST rows: non-empty groups only, in the grouper's order.
 */
abstract class AbstractSection implements SectionInterface {

	/**
	 * Saved visible list, read once per instance (false = never saved).
	 *
	 * @var array<string>|false|null
	 */
	private array|false|null $saved = null;

	/**
	 * Items grouped by source, in render order.
	 *
	 * @return array<string, array{label: string, items: array<array-key, array<string, mixed>>}>
	 */
	abstract protected function grouped(): array;

	/**
	 * Read the saved visible list from storage.
	 *
	 * @return array<string>|false
	 */
	abstract protected function read_saved(): array|false;

	/**
	 * Saved visible list (false when never saved).
	 *
	 * @return array<string>|false
	 */
	protected function saved(): array|false {
		if ( null === $this->saved ) {
			$this->saved = $this->read_saved();
		}

		return $this->saved;
	}

	/**
	 * Grouped rows for the screen.
	 *
	 * @return array{saved: bool, count: int, groups: array<int, array<string, mixed>>}
	 */
	public function present(): array {
		$groups = [];
		$count  = 0;

		foreach ( $this->grouped() as $group_id => $group ) {
			$rows = [];

			foreach ( $group['items'] as $id => $item ) {
				$rows[] = $this->row( (string) $id, $item );
			}

			if ( [] === $rows ) {
				continue;
			}

			$count   += count( $rows );
			$groups[] = [
				'id'    => (string) $group_id,
				'label' => $group['label'],
				'items' => $rows,
			];
		}

		return [
			'saved'  => false !== $this->saved(),
			'count'  => $count,
			'groups' => $groups,
		];
	}

	/**
	 * One row.
	 *
	 * @param string               $id   Item ID.
	 * @param array<string, mixed> $item Grouped item data (title, depth).
	 * @return array{id: string, title: string, visible: bool, protected: bool, depth: int}
	 */
	private function row( string $id, array $item ): array {
		$protected = $this->is_protected( $id );
		$title     = $item['title'] ?? '';

		return [
			'id'        => $id,
			'title'     => is_scalar( $title ) && '' !== (string) $title ? (string) $title : $id,
			'visible'   => $protected || $this->is_visible( $id ),
			'protected' => $protected,
			'depth'     => max( 0, (int) ( $item['depth'] ?? 0 ) ),
		];
	}
}
