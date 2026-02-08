<?php
/**
 * Field Renderer Trait.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Options;

/**
 * Trait for rendering form fields.
 */
trait FieldRendererTrait {

	/**
	 * Render a checkbox field.
	 *
	 * @param array{name: string, label: string, description?: string} $args Field arguments.
	 * @return void
	 */
	protected function render_checkbox_field( array $args ): void {
		$name  = $args['name'];
		$label = $args['label'] ?? '';
		$desc  = $args['description'] ?? '';
		$value = Options::get( $name );

		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s /> %s</label>',
			esc_attr( Options::OPTION_NAME ),
			esc_attr( $name ),
			checked( $value, true, false ),
			esc_html( $label )
		);

		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
	}
}
