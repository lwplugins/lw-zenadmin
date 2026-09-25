<?php
/**
 * Plain-text titles from menu, admin bar and widget title HTML.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

/**
 * Turns a title's HTML into the label a person reads.
 *
 * Plain tag stripping keeps the text of count badges ("Comments 00 Comments
 * in moderation", "Updates 8") and of hidden alternates ("Quick Draft Your
 * Recent Drafts"). This walks the markup with a small element stack (no DOM
 * extension needed, nesting-safe) and drops badges, hidden alternates and
 * keyboard hints. Screen-reader text is kept apart: it becomes the label
 * only when nothing readable is left (icon-only admin bar nodes such as
 * "About WordPress" or "Search"), with its counts removed.
 */
final class PlainTitle {

	/**
	 * Classes whose element (and everything inside) is dropped.
	 */
	private const DROP_CLASSES = [
		'awaiting-mod',
		'update-plugins',
		'plugin-count',
		'theme-count',
		'update-count',
		'pending-count',
		'hide-if-js',
	];

	/**
	 * Elements dropped whatever their class.
	 */
	private const DROP_TAGS = [ 'kbd', 'script', 'style', 'template' ];

	/**
	 * Elements that never have a closing tag.
	 */
	private const VOID_TAGS = [ 'area', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'wbr' ];

	/**
	 * Readable title from HTML ('' when there is none).
	 *
	 * @param string $html Title markup.
	 * @return string
	 */
	public static function from_html( string $html ): string {
		[ $visible, $screen_reader ] = self::split( $html );

		$visible = self::normalize( $visible );

		if ( preg_match( '/\p{L}/u', $visible ) ) {
			return $visible;
		}

		$fallback = self::normalize( self::without_numbers( self::normalize( $screen_reader ) ) );

		return '' !== $fallback ? $fallback : $visible;
	}

	/**
	 * Visible text and screen-reader text, badges and hidden parts dropped.
	 *
	 * @param string $html Title markup.
	 * @return array{0: string, 1: string}
	 */
	private static function split( string $html ): array {
		$tokens  = preg_split( '/(<!--.*?-->|<[^>]*>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
		$stack   = [];
		$visible = '';
		$sr      = '';

		foreach ( false === $tokens ? [] : $tokens as $token ) {
			if ( '<' === $token[0] ) {
				self::apply_tag( $token, $stack );
				continue;
			}

			$kinds = array_column( $stack, 1 );

			if ( in_array( 'drop', $kinds, true ) ) {
				continue;
			}

			if ( in_array( 'sr', $kinds, true ) ) {
				$sr .= $token;
			} else {
				$visible .= $token;
			}
		}

		return [ $visible, $sr ];
	}

	/**
	 * Update the open-element stack for one tag. A closing tag pops back to
	 * its nearest open element; an unmatched one is ignored.
	 *
	 * @param string                                  $tag   Tag markup.
	 * @param array<int, array{0: string, 1: string}> $stack Open elements as [name, kind], by reference.
	 * @return void
	 */
	private static function apply_tag( string $tag, array &$stack ): void {
		if ( ! preg_match( '/^<\s*(\/?)\s*([a-zA-Z][a-zA-Z0-9-]*)/', $tag, $match ) ) {
			return; // Comment, doctype or a stray "<".
		}

		$name = strtolower( $match[2] );

		if ( '/' === $match[1] ) {
			for ( $i = count( $stack ) - 1; $i >= 0; $i-- ) {
				if ( $stack[ $i ][0] === $name ) {
					array_splice( $stack, $i );
					return;
				}
			}
			return;
		}

		if ( in_array( $name, self::VOID_TAGS, true ) || str_ends_with( rtrim( $tag, "> \t\n\r" ), '/' ) ) {
			return;
		}

		$stack[] = [ $name, self::kind( $name, $tag ) ];
	}

	/**
	 * How an element's text is treated: drop, sr (screen-reader) or keep.
	 *
	 * @param string $name Lowercase tag name.
	 * @param string $tag  Opening tag markup.
	 * @return string
	 */
	private static function kind( string $name, string $tag ): string {
		if ( in_array( $name, self::DROP_TAGS, true ) ) {
			return 'drop';
		}

		$classes = [];
		if ( preg_match( '/\sclass\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $tag, $match ) ) {
			$classes = preg_split( '/\s+/', strtolower( $match[1] . ( $match[2] ?? '' ) . ( $match[3] ?? '' ) ), -1, PREG_SPLIT_NO_EMPTY );
			$classes = false === $classes ? [] : $classes;
		}

		foreach ( $classes as $class ) {
			if ( in_array( $class, self::DROP_CLASSES, true ) || preg_match( '/^count-\d+$/', $class ) ) {
				return 'drop';
			}
		}

		return in_array( 'screen-reader-text', $classes, true ) ? 'sr' : 'keep';
	}

	/**
	 * Decode entities and collapse whitespace (non-breaking spaces too).
	 *
	 * @param string $text Text with entities.
	 * @return string
	 */
	private static function normalize( string $text ): string {
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/[\s\x{00A0}\x{202F}]+/u', ' ', $text );

		return trim( (string) $text );
	}

	/**
	 * Drop standalone numbers ("8 updates available" -> "updates available").
	 *
	 * @param string $text Normalized text.
	 * @return string
	 */
	private static function without_numbers( string $text ): string {
		return (string) preg_replace( '/(?<![\p{L}\p{N}])\d[\d.,\s]*(?![\p{L}\p{N}])/u', ' ', $text );
	}
}
