<?php
/**
 * Server-side render of the TypeMotion Letters block.
 *
 * @package TypeMotion
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$typemotion_text = isset( $attributes['text'] ) ? trim( wp_strip_all_tags( (string) $attributes['text'] ) ) : '';
if ( '' === $typemotion_text ) {
	return;
}

$typemotion_tagline = isset( $attributes['tagline'] ) ? trim( wp_strip_all_tags( (string) $attributes['tagline'] ) ) : '';
$typemotion_level   = isset( $attributes['level'] ) ? (int) $attributes['level'] : 2;
$typemotion_tag     = ( $typemotion_level >= 1 && $typemotion_level <= 6 ) ? 'h' . $typemotion_level : 'p';
$typemotion_font    = ( isset( $attributes['fontSource'] ) && 'theme' === $attributes['fontSource'] ) ? 'theme' : 'bundled';
$typemotion_max     = isset( $attributes['maxSize'] ) ? max( 16, min( 600, (float) $attributes['maxSize'] ) ) : 208;
$typemotion_stroke  = isset( $attributes['outlineWidth'] ) ? max( 0.5, min( 6, (float) $attributes['outlineWidth'] ) ) : 1.5;
$typemotion_outline = ! isset( $attributes['outline'] ) || (bool) $attributes['outline'];
$typemotion_animate = ! isset( $attributes['animate'] ) || (bool) $attributes['animate'];
$typemotion_align   = ( isset( $attributes['textAlign'] ) && in_array( $attributes['textAlign'], array( 'left', 'center', 'right' ), true ) ) ? $attributes['textAlign'] : 'center';
$typemotion_back    = isset( $attributes['backdropColor'] ) ? sanitize_hex_color( $attributes['backdropColor'] ) : '';
if ( ! $typemotion_back ) {
	$typemotion_back = '#000000';
}

// Photos: [{id, x, y}], x/y = focal point 0..1.
$typemotion_photos = array();
if ( ! empty( $attributes['photos'] ) && is_array( $attributes['photos'] ) ) {
	foreach ( $attributes['photos'] as $typemotion_p ) {
		$typemotion_id  = isset( $typemotion_p['id'] ) ? absint( $typemotion_p['id'] ) : 0;
		$typemotion_url = $typemotion_id ? typemotion_photo_url( $typemotion_id ) : '';
		if ( '' === $typemotion_url ) {
			continue;
		}
		$typemotion_photos[] = array(
			'url' => $typemotion_url,
			'x'   => typemotion_unit( isset( $typemotion_p['x'] ) ? $typemotion_p['x'] : null, 0.5 ),
			'y'   => typemotion_unit( isset( $typemotion_p['y'] ) ? $typemotion_p['y'] : null, 0.4 ),
		);
	}
}
$typemotion_count = count( $typemotion_photos );

$typemotion_chars = typemotion_chars( $typemotion_text );
$typemotion_html  = '';
$typemotion_i     = 0;
foreach ( $typemotion_chars as $typemotion_c ) {
	if ( ' ' === $typemotion_c ) {
		$typemotion_html .= '<span class="typemotion__space"> </span>';
		continue;
	}
	$typemotion_style = '--tm-i:' . $typemotion_i . ';';
	if ( $typemotion_count ) {
		$typemotion_p = $typemotion_photos[ $typemotion_i % $typemotion_count ];
		// The 2nd, 3rd... use of a photo shows a different part of it.
		$typemotion_x = $typemotion_p['x'];
		for ( $typemotion_k = intdiv( $typemotion_i, $typemotion_count ); $typemotion_k > 0; $typemotion_k-- ) {
			$typemotion_x = $typemotion_x < 0.5 ? $typemotion_x + 0.2 : $typemotion_x - 0.2;
		}
		$typemotion_style .= sprintf(
			'--tm-x:%1$s%%;--tm-y:%2$s%%;background-image:url("%3$s");',
			round( $typemotion_x * 100 ),
			round( $typemotion_p['y'] * 100 ),
			esc_url( $typemotion_p['url'] )
		);
	}
	$typemotion_html .= '<span class="typemotion__letter" style="' . esc_attr( $typemotion_style ) . '">' . esc_html( $typemotion_c ) . '</span>';
	++$typemotion_i;
}

$typemotion_classes = array( 'has-text-align-' . $typemotion_align );
if ( $typemotion_count ) {
	$typemotion_classes[] = 'has-photos';
}
if ( $typemotion_outline ) {
	$typemotion_classes[] = 'has-outline';
}
if ( $typemotion_animate ) {
	$typemotion_classes[] = 'has-animation';
}

$typemotion_wrapper = get_block_wrapper_attributes(
	array(
		'class'              => implode( ' ', $typemotion_classes ),
		'style'              => sprintf(
			'--typemotion-w:%1$s;--typemotion-max:%2$spx;--typemotion-stroke:%3$spx;--typemotion-backdrop:%4$s;',
			number_format( typemotion_word_width( $typemotion_chars, $typemotion_font ), 3, '.', '' ),
			$typemotion_max,
			$typemotion_stroke,
			$typemotion_back
		),
		'data-typemotion-font' => $typemotion_font,
	)
);

printf(
	'<div %1$s><%2$s class="typemotion__word"><span class="typemotion__sr">%3$s</span><span class="typemotion__line" aria-hidden="true">%4$s</span></%2$s>%5$s</div>',
	$typemotion_wrapper, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by get_block_wrapper_attributes().
	tag_escape( $typemotion_tag ),
	esc_html( $typemotion_text ),
	$typemotion_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from escaped parts.
	'' !== $typemotion_tagline ? '<p class="typemotion__tagline">' . esc_html( $typemotion_tagline ) . '</p>' : ''
);
