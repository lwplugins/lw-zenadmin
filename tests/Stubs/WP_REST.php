<?php
/**
 * Minimal WP_REST_Request / WP_REST_Response doubles for unit tests.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

// phpcs:ignoreFile -- test doubles mirroring WordPress core class names.

class WP_REST_Request {

	/**
	 * @var array<string, mixed>
	 */
	private array $params;

	private string $body;

	public function __construct( array $params = [], string $body = '' ) {
		$this->params = $params;
		$this->body   = '' === $body ? (string) json_encode( $params ) : $body;
	}

	public function get_param( string $key ) {
		return $this->params[ $key ] ?? null;
	}

	public function get_body(): string {
		return $this->body;
	}

	public function get_json_params() {
		return $this->params;
	}

	public function get_body_params(): array {
		return [];
	}
}

class WP_REST_Response {

	/**
	 * @var mixed
	 */
	private $data;

	public function __construct( $data = null ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}
