<?php

/* Plugin Name: Taxonomy Picker
 * Plugin URI: http://egyptological.com/build
 * Description: Adds a widget to enable readers to choose custom taxonomies to build a query, combined with a search on Category, Tags or a text string.  The categories and custom to be shown, can be configured in the widget admin so that searches can be restricted to certain categories - for example to allow searching for a name only within a news category.  Searches across the intersection of multiple taxonomies are not supported in WP3.01 but should be available in WP3.1
 * Author: Kate Phizackerley
 * Author URI: http://katephizackerley.wordpress.com
 * Version: 1.01
 *
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package TaxomomyPicker
 */

/** Widget **
*************/

include_once(dirname(__FILE__).'/taxonomy-picker-widget.php');  // Build and display the widget
//	add_filter('pre_get_posts', 'phiz_taxonomy_picker_query');  // hook the widget

/** Form Handler **
*******************/
if(!is_admin()): //only on the front of the blog
	// Handles the form results of the widget
	include_once(dirname(__FILE__).'/taxonomy-picker-process.php');  // Build and display the widget
	add_action('init', 'taxonomy_picker_process', 1);  // Hook in our form handler
endif;

?>