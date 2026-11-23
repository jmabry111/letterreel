<?php
/**
 * Plugin Name:       LetterReel
 * Description:       A block that shows a different photo through each letter of a big word or title, with a gentle entrance animation. The word always fits on one line, on any screen.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            jmabry111
 * Author URI:        https://profiles.wordpress.org/jmabry111/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       jmabry111-letterreel
 *
 * @package LetterReel
 */

defined( 'ABSPATH' ) || exit;

define( 'LETTERREEL_VERSION', '1.0.0' );
define( 'LETTERREEL_DIR', plugin_dir_path( __FILE__ ) );

require_once LETTERREEL_DIR . 'includes/functions.php';

/**
 * Registers the LetterReel Letters block from block/block.json.
 */
function letterreel_register_block() {
	register_block_type( LETTERREEL_DIR . 'block' );
}
add_action( 'init', 'letterreel_register_block' );
