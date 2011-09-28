<?php
/* Plugin Name: Taxonomy Picker
 * Plugin URI: http://www.squidoo.com/taxonomy-picker-wordpress-plugin
 * Description: Help visitors build complex queries using categories and your custom taxonomies by chosing terms from drop down boxes.  The widget also includes a text search making it easy to search for text only within certain categories or taxonomies.

Results will be displayed using your theme's standard search form so the results need no additonal styling - but your permalinks must handle standard WordPress queries in the URL and some prettylink settings may be incompatible.

 * Author: Kate Phizackerley
 * Author URI: http://katephizackerley.wordpress.com
 * Version: 1.10.4
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package TaxomomyPicker
 */

/** Defintitions & Kandie Library **
************************************/
if( !defined('TPICKER_DIR') ) define('TPICKER_DIR', trailingslashit(dirname(__FILE__)) );
if( !function_exists('kandie_debug_status') ) require_once (TPICKER_DIR.'kandie-library/kandie-foundation.php');  // Add Kandie debug & versioning support
add_action( 'init', 'taxonomy_picker_enqueue' );  // Enqueue the stylesheet and any scripts

function taxonomy_picker_enqueue() {
	$options = get_option('taxonomy-picker-options');
	if( isset($options['no-stylesheet']) ) return; // Exit if no stylesheet is wanted.

	//Enqueue taxonomy-picker.css from main theme folder if it exists, otherwise from plugin folder
	if(  file_exists( trailingslashit( get_stylesheet_directory() ) . 'taxonomy-picker.css'  ) ): //Test theme
		$last_modified = date( 'ymdHi', filemtime( trailingslashit( get_stylesheet_directory() ) . 'taxonomy-picker.css' ) );
	    wp_register_style("tpicker", trailingslashit( get_stylesheet_directory_uri() ) . "taxonomy-picker.css", array(), $last_modified );
	else:
		$last_modified = date( 'ymdHi', filemtime( TPICKER_DIR . "taxonomy-picker.css" ) );
	    wp_register_style("tpicker", trailingslashit( plugins_url('',__FILE__) ) . "taxonomy-picker.css", array(), $last_modified );
	endif;
    wp_enqueue_style( "tpicker");
    
    // Now add our scripts
    if( array_key_exists('beta-widget', $options) and !is_admin() ):
/*    
		wp_enqueue_script('jquery'); 

	    wp_register_script( 'tree', trailingslashit( plugins_url('',__FILE__) ) . "jquery/jquery.optionTree.js", "" ,"1", false);
		wp_enqueue_script('tree');
*/
	endif;
    
    return;
}

/** Widget **
*************/
$tpicker_options = get_option('taxonomy-picker-options');
require_once(TPICKER_DIR. ( ($tpicker_options['beta-widget'] ) ? 't' : 'taxonomy-' ) . 'picker-library.php');  // Use required library version
unset( $tpicker_options ); // Avoid hanging around in global scope


require_once(TPICKER_DIR.'taxonomy-picker-widget.php');  // Build and display the widget
if( array_key_exists('taxonomies', get_option('taxonomy-picker-options') ) ) include_once(TPICKER_DIR.'taxonomy-picker-taxonomies.php');  // Add pre-built taxonomies

/** Remainder **
*******************/
if(!is_admin()): //only on the front of the blog
	// Handles the form results of the widget

	require_once(TPICKER_DIR.'taxonomy-picker-process.php');  // Process any previous use of the widget
	add_action('init', 'taxonomy_picker_process', 1);  // Hook in our form handler
	// add_action('init', create_function('' , "wp_enqueue_script('jquery');"), 1); // Activate JQuery


/*	Defer shortcode implementation to v1.6	
	require_once(TPICKER_DIR.'/taxonomy-picker-shortcode.php');  // Add shortcode equivalent
*/

else:
	require_once( kandie_include_best_library('kandie-admin-menu.php') ); // Kandie admin menu extensions - include most recent in any plugin
	require_once(TPICKER_DIR.'taxonomy-picker-admin.php'); // Admin panel extensions for Taxonomy Picker
	register_activation_hook(__FILE__, 'taxonomy_picker_default');  // Plugin activation
endif;


/** Activation and Deactivation **
**********************************/

function taxonomy_picker_default() { /* Main plugin activation function  - doubles as restore defaults */

	$default = array('remember' => 'on', 'auto-help' => 'on', 'all-format' => '** All **', 'miss-url' => home_url() );
	add_option( 'taxonomy-picker-options', $default, '', true);  // Add options
}

?>