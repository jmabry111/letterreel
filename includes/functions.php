<?php
/**
 * Helpers used by the block's server-side render.
 *
 * @package LetterReel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Splits a string into characters (UTF-8 aware).
 *
 * @param string $text Text to split.
 * @return string[]
 */
function letterreel_chars( $text ) {
	$chars = preg_split( '//u', (string) $text, -1, PREG_SPLIT_NO_EMPTY );
	return is_array( $chars ) ? $chars : array();
}

/**
 * Estimated width of the word in em, so CSS can fit it to its container before any JavaScript runs.
 * Uses the real letter widths of the bundled font; for the theme's font it uses an average
 * (the front-end script then measures the exact width).
 *
 * @param string[] $chars       Characters of the word.
 * @param string   $font_source 'bundled' or 'theme'.
 * @return float
 */
function letterreel_word_width( $chars, $font_source ) {
	static $widths = null;
	if ( 'bundled' !== $font_source ) {
		return max( 1.0, count( $chars ) * 0.6 );
	}
	if ( null === $widths ) {
		$widths = include LETTERREEL_DIR . 'includes/font-widths.php';
	}
	$total = 0;
	foreach ( $chars as $char ) {
		$code   = function_exists( 'mb_ord' ) ? mb_ord( $char, 'UTF-8' ) : ord( $char );
		$total += isset( $widths[ $code ] ) ? $widths[ $code ] : 750;
	}
	return max( 1.0, $total / 1000 );
}

/**
 * Returns a usable image URL for a letter: a large size when the theme registers one, else the original.
 *
 * @param int $attachment_id Attachment ID.
 * @return string Empty string when the image does not exist.
 */
function letterreel_photo_url( $attachment_id ) {
	foreach ( array( '1536x1536', 'large', 'full' ) as $size ) {
		$src = wp_get_attachment_image_src( $attachment_id, $size );
		if ( $src && ! empty( $src[0] ) ) {
			return $src[0];
		}
	}
	return '';
}

/**
 * Clamps a focal-point coordinate to 0..1.
 *
 * @param mixed $value    Raw value.
 * @param float $fallback Used when the value is missing.
 * @return float
 */
function letterreel_unit( $value, $fallback ) {
	if ( ! is_numeric( $value ) ) {
		return $fallback;
	}
	return max( 0.0, min( 1.0, (float) $value ) );
}
