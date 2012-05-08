<?php
/* Plugin Name: Taxonomy Picker
 * Plugin URI: http://www.squidoo.com/taxonomy-picker-wordpress-plugin
 * Description: Help visitors build complex queries using categories and your custom taxonomies by chosing terms from drop down boxes.  The widget also includes a text search making it easy to search for text only within certain categories or taxonomies. Results will be displayed using your theme's standard search form so the results need no additonal styling - but your permalinks must handle standard WordPress queries in the URL and some prettylink settings may be incompatible.
 *
 *******************************************************************
 *
 * Author: Kate Phizackerley
 * Author URI: http://katephizackerley.wordpress.com
 * Version: 1.12.0b
 *
 *******************************************************************
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Copyright Kae Phizackerley 2011,2012
 *
 */

/** Defintitions & Silverghyll Library **
************************************/
if( !defined('TPICKER_DIR') ) define('TPICKER_DIR', trailingslashit(dirname(__FILE__)) );
if( !function_exists('silverghyll_debug_status') ) require_once (TPICKER_DIR.'silverghyll-library/silverghyll-foundation.php');  // Add silverghyll debug & versioning support
if( !is_admin() ) add_action( 'init', 'taxonomy_picker_enqueue' );  // Enqueue the stylesheet and any scripts or internationalization

function taxonomy_picker_enqueue() { // Also do any other init actions

	$tpicker_options = get_option('taxonomy-picker-options');
		
	if( isset($tpicker_options['no-stylesheet']) ) return; // Exit if no stylesheet is wanted.

	//Enqueue taxonomy-picker.css from main theme folder if it exists, otherwise from plugin folder
	if(  file_exists( trailingslashit( get_stylesheet_directory() ) . 'taxonomy-picker.css'  ) ): //Test theme
		$last_modified = date( 'ymdHi', filemtime( trailingslashit( get_stylesheet_directory() ) . 'taxonomy-picker.css' ) );
	    wp_register_style("tpicker", trailingslashit( get_stylesheet_directory_uri() ) . "taxonomy-picker.css", array(), $last_modified );
	else:
		$last_modified = date( 'ymdHi', filemtime( TPICKER_DIR . "taxonomy-picker.css" ) );
	    wp_register_style("tpicker", trailingslashit( plugins_url('',__FILE__) ) . "taxonomy-picker.css", array(), $last_modified );
	endif;
    wp_enqueue_style( "tpicker");
    
    return;
}

/** Widget **
*************/
$tpicker_options = get_option('taxonomy-picker-options');

/*  Add in our plugin library and widget
******************************************/

if( !empty($tpicker_options) ): // Robust code

	require_once( silverghyll_include_best_library( 'silverghyll-common.php' ) ); // Include common library functions 
	
	if( array_key_exists('premium-widget', $tpicker_options) ): // Are we using the premium version?
		require_once( silverghyll_theme_preferred( TPICKER_DIR . 'tpicker-library.php' ) ); // Use required library version
		require_once( silverghyll_theme_preferred( TPICKER_DIR . 'tpicker-widget.php' ) ); // Build and display the widget
	else:
		require_once( silverghyll_theme_preferred( TPICKER_DIR . 'taxonomy-picker-library.php' ) ); // Use required library version
		require_once( silverghyll_theme_preferred( TPICKER_DIR . 'taxonomy-picker-widget.php' ) ); // Build and display the widget
	endif;
	
	if( array_key_exists('taxonomies', $tpicker_options) ):
		include_once( TPICKER_DIR.'taxonomy-picker-taxonomies.php' );  // Add pre-built taxonomies
	endif;
	
endif;


/** Remainder **
*******************/
if( !is_admin() ): //only on the front of the blog

	require_once( TPICKER_DIR . 'taxonomy-picker-process.php' );  // Process any previous use of the widget
	add_action('init', 'taxonomy_picker_process', 1);  // Hook in our form handler

/*	Needs re-coding to store in a cookie
	if( array_key_exists('redirect', $tpicker_options) ): // Add echo of the redirection which was suppressed
		add_filter( 'the_content', 'tpicker_redirect_growl', 99 );  
		add_filter( 'the_excerpt', 'tpicker_redirect_growl', 99 );  
		
		// Filter for the_content to add debugging info on the redirection
		function tpicker_redirect_growl( $content ) {
			static $growled = false;
			if( !$growled ):
				$content .= '<div class="tpicker-redirect-growl" style="padding: 1em 2em; border 5px red groove;"><h2>Taxonomy Picker Redirect</h2><p>Redirect built: <strong>';
				$content .= defined( 'TPICKER_REDIRECT' ) ? TPICKER_REDIRECT : 'N/A'; // Just in case ...
				$content .= '</strong></p></div>';
				$growled = true; // Growl only once
			endif;
			return $content;
		} 
	endif;
*/

	// Add optional colohon support
	if( (!empty($tpicker_options)) and (array_key_exists('colophon', $tpicker_options)) ):
			require_once( silverghyll_include_best_library('silverghyll-shortcodes.php') ); // Silverghyll shortcodes needed to add [colophon]
	endif;
	
	
	/*	Defer shortcode implementation to v1.6	
		require_once(TPICKER_DIR.'/taxonomy-picker-shortcode.php');  // Add shortcode equivalent
	*/

	// add_action('init', create_function('' , "wp_enqueue_script('jquery');"), 1); // Activate JQuery

else:
	require_once( silverghyll_include_best_library('silverghyll-admin-menu.php') ); // silverghyll admin menu extensions - include most recent in any plugin
	require_once( TPICKER_DIR . 'taxonomy-picker-admin.php'); // Admin panel extensions for Taxonomy Picker
	register_activation_hook(__FILE__, 'taxonomy_picker_default');  // Plugin activation
endif;

unset( $tpicker_options ); // Avoid hanging around in global scope

/** Activation and Deactivation **
**********************************/

function taxonomy_picker_default() { /* Main plugin activation function  - doubles as restore defaults */

	$default = array('remember' => 'on', 'auto-help' => 'on', 'all-format' => '** All **', 'miss-url' => home_url() );
	add_option( 'taxonomy-picker-options', $default, '', true);  // Add options
}

?>