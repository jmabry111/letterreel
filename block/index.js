/**
 * TypeMotion Letters block: editor.
 *
 * Hand-written for the WordPress globals (no build step). The front end is rendered in PHP
 * (render.php); the editor draws the same markup so changes show up instantly.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useRef = wp.element.useRef;
	var useState = wp.element.useState;
	var useLayoutEffect = wp.element.useLayoutEffect;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var metadata = { name: 'typemotion/letters' };

	function chars( text ) {
		return Array.from( text || '' );
	}

	function clamp01( v, fallback ) {
		var n = parseFloat( v );
		return isNaN( n ) ? fallback : Math.max( 0, Math.min( 1, n ) );
	}

	function photoUrl( media, size ) {
		if ( ! media ) {
			return '';
		}
		var sizes = ( media.media_details && media.media_details.sizes ) || {};
		var order = size === 'thumb' ? [ 'medium', 'thumbnail' ] : [ '1536x1536', 'large' ];
		for ( var i = 0; i < order.length; i++ ) {
			if ( sizes[ order[ i ] ] && sizes[ order[ i ] ].source_url ) {
				return sizes[ order[ i ] ].source_url;
			}
		}
		return media.source_url || '';
	}

	function usePalette() {
		if ( be.useSettings ) {
			return be.useSettings( 'color.palette' )[ 0 ] || [];
		}
		return ( be.useSetting && be.useSetting( 'color.palette' ) ) || [];
	}

	function Edit( props ) {
		var a = props.attributes;
		var set = props.setAttributes;
		var photos = Array.isArray( a.photos ) ? a.photos : [];
		var palette = usePalette();
		var ref = useRef();
		var measured = useState( null );
		var width = measured[ 0 ];
		var setWidth = measured[ 1 ];

		var media = useSelect(
			function ( select ) {
				var core = select( 'core' );
				return photos.map( function ( p ) {
					return p && p.id ? core.getMedia( p.id, { context: 'view' } ) : null;
				} );
			},
			[ photos.map( function ( p ) { return p && p.id; } ).join( ',' ) ]
		);

		// Measure the word once its font is ready, like view.js does on the front end.
		useLayoutEffect(
			function () {
				var node = ref.current;
				if ( ! node ) {
					return;
				}
				var doc = node.ownerDocument;
				var cancelled = false;
				function measure() {
					if ( cancelled ) {
						return;
					}
					var line = node.querySelector( '.typemotion__line' );
					if ( ! line ) {
						return;
					}
					var size = parseFloat( doc.defaultView.getComputedStyle( line ).fontSize );
					var w = line.getBoundingClientRect().width;
					if ( size > 0 && w > 0 ) {
						setWidth( ( w / size ).toFixed( 3 ) );
					}
				}
				if ( doc.fonts && doc.fonts.ready ) {
					doc.fonts.ready.then( measure );
				} else {
					measure();
				}
				return function () {
					cancelled = true;
				};
			},
			[ a.text, a.fontSource, a.level ]
		);

		function updatePhoto( i, patch ) {
			set( {
				photos: photos.map( function ( p, j ) {
					return j === i ? Object.assign( {}, p, patch ) : p;
				} ),
			} );
		}
		function movePhoto( i, d ) {
			var j = i + d;
			if ( j < 0 || j >= photos.length ) {
				return;
			}
			var next = photos.slice();
			var t = next[ i ];
			next[ i ] = next[ j ];
			next[ j ] = t;
			set( { photos: next } );
		}
		function removePhoto( i ) {
			set( {
				photos: photos.filter( function ( p, j ) {
					return j !== i;
				} ),
			} );
		}
		function choosePhotos( items ) {
			var keep = {};
			photos.forEach( function ( p ) {
				keep[ p.id ] = p;
			} );
			set( {
				photos: ( items || [] ).map( function ( m ) {
					var old = keep[ m.id ] || {};
					return { id: m.id, x: old.x !== undefined ? old.x : 0.5, y: old.y !== undefined ? old.y : 0.4 };
				} ),
			} );
		}

		// ---- the preview (same markup as render.php) ----
		var urls = media.map( function ( m ) {
			return photoUrl( m, 'full' );
		} );
		var usable = [];
		photos.forEach( function ( p, i ) {
			if ( urls[ i ] ) {
				usable.push( { url: urls[ i ], x: clamp01( p.x, 0.5 ), y: clamp01( p.y, 0.4 ) } );
			}
		} );
		var letters = [];
		var li = 0;
		chars( a.text ).forEach( function ( ch, k ) {
			if ( ch === ' ' ) {
				letters.push( el( 'span', { key: 's' + k, className: 'typemotion__space' }, ' ' ) );
				return;
			}
			var style = { '--tm-i': li };
			if ( usable.length ) {
				var p = usable[ li % usable.length ];
				var x = p.x;
				for ( var r = Math.floor( li / usable.length ); r > 0; r-- ) {
					x = x < 0.5 ? x + 0.2 : x - 0.2;
				}
				style[ '--tm-x' ] = Math.round( x * 100 ) + '%';
				style[ '--tm-y' ] = Math.round( p.y * 100 ) + '%';
				style.backgroundImage = 'url("' + p.url + '")';
			}
			letters.push( el( 'span', { key: k + ch, className: 'typemotion__letter', style: style }, ch ) );
			li++;
		} );

		var level = a.level >= 1 && a.level <= 6 ? a.level : 0;
		var Tag = level ? 'h' + level : 'p';
		var classes = [ 'has-text-align-' + ( a.textAlign || 'center' ) ];
		if ( usable.length ) {
			classes.push( 'has-photos' );
		}
		if ( a.outline ) {
			classes.push( 'has-outline' );
		}
		if ( a.animate ) {
			classes.push( 'has-animation' );
		}
		var estimate = Math.max( 1, chars( a.text ).length * 0.6 ).toFixed( 3 );
		var blockProps = be.useBlockProps( {
			ref: ref,
			className: classes.join( ' ' ),
			'data-typemotion-font': a.fontSource === 'theme' ? 'theme' : 'bundled',
			style: {
				'--typemotion-w': width || estimate,
				'--typemotion-max': ( a.maxSize || 208 ) + 'px',
				'--typemotion-stroke': ( a.outlineWidth || 1.5 ) + 'px',
				'--typemotion-backdrop': a.backdropColor || '#000000',
			},
		} );

		var preview = el(
			'div',
			blockProps,
			a.text
				? el(
						Tag,
						{ className: 'typemotion__word' },
						el( 'span', { className: 'typemotion__sr' }, a.text ),
						el( 'span', { className: 'typemotion__line', 'aria-hidden': 'true' }, letters )
				  )
				: el( 'p', null, __( 'Type a word in the block settings.', 'typemotion' ) ),
			a.tagline ? el( 'p', { className: 'typemotion__tagline' }, a.tagline ) : null
		);

		// ---- sidebar ----
		var photoRows = photos.map( function ( p, i ) {
			var thumb = photoUrl( media[ i ], 'thumb' );
			return el(
				'div',
				{ key: p.id + '-' + i, className: 'typemotion-photo-row', style: { borderTop: '1px solid #ddd', padding: '12px 0' } },
				el( 'p', { style: { margin: '0 0 6px', fontWeight: 600 } }, sprintf( /* translators: %d: photo number */ __( 'Photo %d', 'typemotion' ), i + 1 ) ),
				thumb
					? el( c.FocalPointPicker, {
							label: __( 'Drag the dot onto the part that should show in the letters', 'typemotion' ),
							url: thumb,
							value: { x: clamp01( p.x, 0.5 ), y: clamp01( p.y, 0.4 ) },
							onChange: function ( v ) {
								updatePhoto( i, { x: v.x, y: v.y } );
							},
					  } )
					: el( c.Spinner ),
				el(
					c.Flex,
					{ justify: 'flex-start' },
					el( c.Button, { variant: 'secondary', size: 'small', disabled: i === 0, onClick: function () { movePhoto( i, -1 ); } }, __( 'Up', 'typemotion' ) ),
					el( c.Button, { variant: 'secondary', size: 'small', disabled: i === photos.length - 1, onClick: function () { movePhoto( i, 1 ); } }, __( 'Down', 'typemotion' ) ),
					el( c.Button, { variant: 'link', isDestructive: true, onClick: function () { removePhoto( i ); } }, __( 'Remove', 'typemotion' ) )
				)
			);
		} );

		var levelOptions = [ { label: __( 'Paragraph (not a heading)', 'typemotion' ), value: '0' } ];
		for ( var h = 1; h <= 6; h++ ) {
			levelOptions.push( { label: sprintf( /* translators: %d: heading level */ __( 'Heading %d', 'typemotion' ), h ), value: String( h ) } );
		}

		return el(
			Fragment,
			null,
			el(
				be.BlockControls,
				{ group: 'block' },
				el( be.AlignmentControl, {
					value: a.textAlign,
					onChange: function ( v ) {
						set( { textAlign: v || 'center' } );
					},
				} )
			),
			el(
				be.InspectorControls,
				null,
				el(
					c.PanelBody,
					{ title: __( 'Text', 'typemotion' ), initialOpen: true },
					el( c.TextControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Big word', 'typemotion' ),
						value: a.text,
						help: __( 'Short works best: one word or a title of about 10 letters.', 'typemotion' ),
						onChange: function ( v ) {
							set( { text: v } );
						},
					} ),
					el( 'div', { style: { height: 16 } } ),
					el( c.TextControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Line underneath', 'typemotion' ),
						value: a.tagline,
						help: __( 'Optional. Leave empty to hide it.', 'typemotion' ),
						onChange: function ( v ) {
							set( { tagline: v } );
						},
					} ),
					el( 'div', { style: { height: 16 } } ),
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Heading level', 'typemotion' ),
						value: String( level ),
						options: levelOptions,
						help: __( 'Use Heading 1 only for the main title of the page.', 'typemotion' ),
						onChange: function ( v ) {
							set( { level: parseInt( v, 10 ) } );
						},
					} )
				),
				el(
					c.PanelBody,
					{ title: sprintf( /* translators: %d: number of photos */ __( 'Photos (%d)', 'typemotion' ), photos.length ), initialOpen: true },
					el( 'p', null, __( 'The letters take turns using these photos, in this order. Bright photos with the subject near the middle work best.', 'typemotion' ) ),
					el(
						be.MediaUploadCheck,
						null,
						el( be.MediaUpload, {
							onSelect: choosePhotos,
							allowedTypes: [ 'image' ],
							multiple: true,
							gallery: false,
							value: photos.map( function ( p ) {
								return p.id;
							} ),
							render: function ( o ) {
								return el( c.Button, { variant: 'primary', onClick: o.open }, photos.length ? __( 'Change photos', 'typemotion' ) : __( 'Choose photos', 'typemotion' ) );
							},
						} )
					),
					photoRows
				),
				el(
					c.PanelBody,
					{ title: __( 'Appearance', 'typemotion' ), initialOpen: false },
					el( c.SelectControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Font', 'typemotion' ),
						value: a.fontSource,
						options: [
							{ label: __( 'Alfa Slab One (bundled, heavy)', 'typemotion' ), value: 'bundled' },
							{ label: __( "Theme's heading font", 'typemotion' ), value: 'theme' },
						],
						help: __( 'Heavy fonts show more of each photo.', 'typemotion' ),
						onChange: function ( v ) {
							set( { fontSource: v } );
						},
					} ),
					el( 'div', { style: { height: 16 } } ),
					el( c.RangeControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Largest size (px)', 'typemotion' ),
						value: a.maxSize,
						min: 32,
						max: 400,
						help: __( 'The word shrinks below this to fit narrow screens.', 'typemotion' ),
						onChange: function ( v ) {
							set( { maxSize: v || 208 } );
						},
					} ),
					el( c.ToggleControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Outline the letters', 'typemotion' ),
						help: __( 'Uses the block text colour (Styles tab).', 'typemotion' ),
						checked: !! a.outline,
						onChange: function ( v ) {
							set( { outline: v } );
						},
					} ),
					a.outline
						? el( c.RangeControl, {
								__nextHasNoMarginBottom: true,
								label: __( 'Outline width (px)', 'typemotion' ),
								value: a.outlineWidth,
								min: 0.5,
								max: 6,
								step: 0.5,
								onChange: function ( v ) {
									set( { outlineWidth: v || 1.5 } );
								},
						  } )
						: null,
					el( c.ToggleControl, {
						__nextHasNoMarginBottom: true,
						label: __( 'Animate', 'typemotion' ),
						help: __( 'Letters rise in, then the photos drift slowly. Always off for visitors who ask their device for reduced motion.', 'typemotion' ),
						checked: !! a.animate,
						onChange: function ( v ) {
							set( { animate: v } );
						},
					} ),
					el( c.BaseControl, {
						__nextHasNoMarginBottom: true,
						id: 'typemotion-backdrop',
						label: __( 'Colour behind the photos', 'typemotion' ),
						help: __( 'Shows only where a photo does not cover a letter, but accessibility checkers compare the text colour with it. Pick a colour that contrasts with the text colour.', 'typemotion' ),
					},
						el( c.ColorPalette, {
							colors: palette,
							value: a.backdropColor,
							onChange: function ( v ) {
								set( { backdropColor: v || '#000000' } );
							},
						} )
					)
				)
			),
			preview
		);
	}

	wp.blocks.registerBlockType( metadata.name, {
		edit: Edit,
		save: function () {
			return null;
		},
	} );
} )( window.wp );
