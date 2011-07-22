<?php
/* Plugin Name: Taxonomy Picker
 * Plugin URI: http://www.squidoo.com/taxonomy-picker-wordpress-plugin
 * Description: Help visitors build complex queries using categories and your custom taxonomies by chosing terms from drop down boxes.  The widget also includes a text search making it easy to search for text only within certain categories or taxonomies.

Results will be displayed using your theme's standard search form so the results need no additonal styling - but your permalinks must handle standard WordPress queries in the URL and some prettylink settings may be incompatible.

 * Author: Kate Phizackerley
 * Author URI: http://katephizackerley.wordpress.com
 * Version: 1.7
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

/** Widget **
*************/
include_once(TPICKER_DIR.'taxonomy-picker-library.php');  // Add functions common to all aspects
include_once(TPICKER_DIR.'taxonomy-picker-widget.php');  // Build and display the widget

/** Remainder **
*******************/
if(!is_admin()): //only on the front of the blog
	// Handles the form results of the widget

	require_once(TPICKER_DIR.'taxonomy-picker-process.php');  // Process any previous use of the widget
	add_action('init', 'taxonomy_picker_process', 1);  // Hook in our form handler

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