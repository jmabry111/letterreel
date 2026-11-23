/**
 * LetterReel Letters: measures each word once its font has loaded and stores the exact width (in em),
 * so the CSS can fit it to the container. Needed for the theme-font option; refines the bundled font too.
 */
( function () {
	function fit( block ) {
		var line = block.querySelector( '.letterreel__line' );
		if ( ! line ) {
			return;
		}
		var size = parseFloat( window.getComputedStyle( line ).fontSize );
		var width = line.getBoundingClientRect().width;
		if ( size > 0 && width > 0 ) {
			block.style.setProperty( '--letterreel-w', ( width / size ).toFixed( 3 ) );
		}
	}
	function fitAll() {
		var blocks = document.querySelectorAll( '.wp-block-letterreel-letters' );
		for ( var i = 0; i < blocks.length; i++ ) {
			fit( blocks[ i ] );
		}
	}
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( fitAll );
	} else {
		window.addEventListener( 'load', fitAll );
	}
} )();
