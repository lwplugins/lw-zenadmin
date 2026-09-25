<?php
/**
 * Validates a settings save request.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin;

use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Rest\Admin\Sections\SectionInterface;

/**
 * Checks the whole partial body before anything is written:
 * `options` (known keys, booleans) and one `{ id: visible }` map per
 * visibility section (discovered IDs only, protected items never hidden).
 */
final class SettingsInput {

	/**
	 * Most messages reported per field, so a huge bad body stays readable.
	 */
	private const MAX_MESSAGES = 5;

	/**
	 * Field errors: key => messages.
	 *
	 * @var array<string, array<int, string>>
	 */
	private array $errors = [];

	/**
	 * Validated option changes.
	 *
	 * @var array<string, bool>
	 */
	private array $options = [];

	/**
	 * Validated visibility changes per section key.
	 *
	 * @var array<string, array<string, bool>>
	 */
	private array $changes = [];

	/**
	 * Validate a body.
	 *
	 * @param array<array-key, mixed>         $body     Decoded JSON body.
	 * @param array<string, SectionInterface> $sections Sections by key.
	 */
	public function __construct( array $body, array $sections ) {
		foreach ( $body as $key => $value ) {
			$key = (string) $key;

			if ( 'options' === $key ) {
				$this->parse_options( $value );
			} elseif ( isset( $sections[ $key ] ) ) {
				$this->parse_section( $sections[ $key ], $value );
			} else {
				$this->add( $key, __( 'Unknown setting.', 'lw-zenadmin' ) );
			}
		}
	}

	/**
	 * Field errors (empty when the body is valid).
	 *
	 * @return array<string, array<int, string>>
	 */
	public function errors(): array {
		return $this->errors;
	}

	/**
	 * Validated option changes.
	 *
	 * @return array<string, bool>
	 */
	public function options(): array {
		return $this->options;
	}

	/**
	 * Validated visibility changes per section key.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public function changes(): array {
		return $this->changes;
	}

	/**
	 * Validate the `options` object.
	 *
	 * @param mixed $value Submitted value.
	 * @return void
	 */
	private function parse_options( mixed $value ): void {
		if ( ! is_array( $value ) ) {
			$this->add( 'options', __( 'Expected an object of settings.', 'lw-zenadmin' ) );
			return;
		}

		$defaults = Options::get_defaults();

		foreach ( $value as $key => $flag ) {
			$key  = (string) $key;
			$bool = self::to_bool( $flag );

			if ( ! array_key_exists( $key, $defaults ) ) {
				$this->add( $key, __( 'Unknown setting.', 'lw-zenadmin' ) );
			} elseif ( null === $bool ) {
				$this->add( $key, __( 'Must be true or false.', 'lw-zenadmin' ) );
			} else {
				$this->options[ $key ] = $bool;
			}
		}
	}

	/**
	 * Validate one section's `{ id: visible }` map.
	 *
	 * @param SectionInterface $section Section.
	 * @param mixed            $value   Submitted value.
	 * @return void
	 */
	private function parse_section( SectionInterface $section, mixed $value ): void {
		$key = $section->key();

		if ( ! is_array( $value ) ) {
			$this->add( $key, __( 'Expected an object of item IDs and true or false.', 'lw-zenadmin' ) );
			return;
		}

		$known   = array_flip( $section->discovered_ids() );
		$changes = [];

		foreach ( $value as $id => $flag ) {
			$id    = (string) $id;
			$bool  = self::to_bool( $flag );
			$label = substr( $id, 0, 80 );

			if ( ! isset( $known[ $id ] ) ) {
				/* translators: %s: item ID. */
				$this->add( $key, sprintf( __( 'Unknown item: "%s".', 'lw-zenadmin' ), $label ) );
			} elseif ( null === $bool ) {
				/* translators: %s: item ID. */
				$this->add( $key, sprintf( __( '"%s" must be true or false.', 'lw-zenadmin' ), $label ) );
			} elseif ( ! $bool && $section->is_protected( $id ) ) {
				/* translators: %s: item ID. */
				$this->add( $key, sprintf( __( '"%s" is protected and cannot be hidden.', 'lw-zenadmin' ), $label ) );
			} else {
				$changes[ $id ] = $bool;
			}
		}

		if ( [] !== $changes ) {
			$this->changes[ $key ] = $changes;
		}
	}

	/**
	 * Strict boolean: true/false, 1/0 or "1"/"0".
	 *
	 * @param mixed $value Submitted value.
	 * @return bool|null Null when not a boolean.
	 */
	private static function to_bool( mixed $value ): ?bool {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( 1 === $value || '1' === $value ) {
			return true;
		}

		return 0 === $value || '0' === $value ? false : null;
	}

	/**
	 * Record a message for a field (capped).
	 *
	 * @param string $field   Field key.
	 * @param string $message Message.
	 * @return void
	 */
	private function add( string $field, string $message ): void {
		if ( count( $this->errors[ $field ] ?? [] ) < self::MAX_MESSAGES ) {
			$this->errors[ $field ][] = $message;
		}
	}
}
