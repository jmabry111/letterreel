<?php
/**
 * Server-side render of the LetterReel Letters block.
 *
 * @package LetterReel
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$letterreel_text = isset( $attributes['text'] ) ? trim( wp_strip_all_tags( (string) $attributes['text'] ) ) : '';
if ( '' === $letterreel_text ) {
	return;
}

$letterreel_tagline = isset( $attributes['tagline'] ) ? trim( wp_strip_all_tags( (string) $attributes['tagline'] ) ) : '';
$letterreel_level   = isset( $attributes['level'] ) ? (int) $attributes['level'] : 2;
$letterreel_tag     = ( $letterreel_level >= 1 && $letterreel_level <= 6 ) ? 'h' . $letterreel_level : 'p';
$letterreel_font    = ( isset( $attributes['fontSource'] ) && 'theme' === $attributes['fontSource'] ) ? 'theme' : 'bundled';
$letterreel_max     = isset( $attributes['maxSize'] ) ? max( 16, min( 600, (float) $attributes['maxSize'] ) ) : 208;
$letterreel_stroke  = isset( $attributes['outlineWidth'] ) ? max( 0.5, min( 6, (float) $attributes['outlineWidth'] ) ) : 1.5;
$letterreel_outline = ! isset( $attributes['outline'] ) || (bool) $attributes['outline'];
$letterreel_animate = ! isset( $attributes['animate'] ) || (bool) $attributes['animate'];
$letterreel_align   = ( isset( $attributes['textAlign'] ) && in_array( $attributes['textAlign'], array( 'left', 'center', 'right' ), true ) ) ? $attributes['textAlign'] : 'center';
$letterreel_back    = isset( $attributes['backdropColor'] ) ? sanitize_hex_color( $attributes['backdropColor'] ) : '';
if ( ! $letterreel_back ) {
	$letterreel_back = '#000000';
}

// Photos: [{id, x, y}], x/y = focal point 0..1.
$letterreel_photos = array();
if ( ! empty( $attributes['photos'] ) && is_array( $attributes['photos'] ) ) {
	foreach ( $attributes['photos'] as $letterreel_p ) {
		$letterreel_id  = isset( $letterreel_p['id'] ) ? absint( $letterreel_p['id'] ) : 0;
		$letterreel_url = $letterreel_id ? letterreel_photo_url( $letterreel_id ) : '';
		if ( '' === $letterreel_url ) {
			continue;
		}
		$letterreel_photos[] = array(
			'url' => $letterreel_url,
			'x'   => letterreel_unit( isset( $letterreel_p['x'] ) ? $letterreel_p['x'] : null, 0.5 ),
			'y'   => letterreel_unit( isset( $letterreel_p['y'] ) ? $letterreel_p['y'] : null, 0.4 ),
		);
	}
}
$letterreel_count = count( $letterreel_photos );

$letterreel_chars = letterreel_chars( $letterreel_text );
$letterreel_html  = '';
$letterreel_i     = 0;
foreach ( $letterreel_chars as $letterreel_c ) {
	if ( ' ' === $letterreel_c ) {
		$letterreel_html .= '<span class="letterreel__space"> </span>';
		continue;
	}
	$letterreel_style = '--lr-i:' . $letterreel_i . ';';
	if ( $letterreel_count ) {
		$letterreel_p = $letterreel_photos[ $letterreel_i % $letterreel_count ];
		// The 2nd, 3rd... use of a photo shows a different part of it.
		$letterreel_x = $letterreel_p['x'];
		for ( $letterreel_k = intdiv( $letterreel_i, $letterreel_count ); $letterreel_k > 0; $letterreel_k-- ) {
			$letterreel_x = $letterreel_x < 0.5 ? $letterreel_x + 0.2 : $letterreel_x - 0.2;
		}
		$letterreel_style .= sprintf(
			'--lr-x:%1$s%%;--lr-y:%2$s%%;background-image:url("%3$s");',
			round( $letterreel_x * 100 ),
			round( $letterreel_p['y'] * 100 ),
			esc_url( $letterreel_p['url'] )
		);
	}
	$letterreel_html .= '<span class="letterreel__letter" style="' . esc_attr( $letterreel_style ) . '">' . esc_html( $letterreel_c ) . '</span>';
	++$letterreel_i;
}

$letterreel_classes = array( 'has-text-align-' . $letterreel_align );
if ( $letterreel_count ) {
	$letterreel_classes[] = 'has-photos';
}
if ( $letterreel_outline ) {
	$letterreel_classes[] = 'has-outline';
}
if ( $letterreel_animate ) {
	$letterreel_classes[] = 'has-animation';
}

$letterreel_wrapper = get_block_wrapper_attributes(
	array(
		'class'              => implode( ' ', $letterreel_classes ),
		'style'              => sprintf(
			'--letterreel-w:%1$s;--letterreel-max:%2$spx;--letterreel-stroke:%3$spx;--letterreel-backdrop:%4$s;',
			number_format( letterreel_word_width( $letterreel_chars, $letterreel_font ), 3, '.', '' ),
			$letterreel_max,
			$letterreel_stroke,
			$letterreel_back
		),
		'data-letterreel-font' => $letterreel_font,
	)
);

printf(
	'<div %1$s><%2$s class="letterreel__word"><span class="letterreel__sr">%3$s</span><span class="letterreel__line" aria-hidden="true">%4$s</span></%2$s>%5$s</div>',
	$letterreel_wrapper, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes().
	tag_escape( $letterreel_tag ),
	esc_html( $letterreel_text ),
	$letterreel_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from escaped parts.
	'' !== $letterreel_tagline ? '<p class="letterreel__tagline">' . esc_html( $letterreel_tagline ) . '</p>' : ''
);
