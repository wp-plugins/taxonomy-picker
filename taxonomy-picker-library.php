<?php

/* Functons shared by the shortcode and widget
 * Version: 1.3.1
 */

/* Standardise function for accessing $_GET variables
 *
 * @return string cleaned, decoded URI variable
 */
 
function taxonomy_picker_option_set($get_var) {
	return ( $_GET['kandie_tpicker'] )  ? taxonomy_picker_dencode( $_GET[$get_var], 'decode' ) : '';  // Only return when tpicker is set
}

/* Return array of saved tpicker options
 *
 * @return array of strings		keys: names of taxomies    data: value used in search 
 */

function taxonomy_picker_tpicker_array() {
	$tpicker_get = taxonomy_picker_dencode( $_GET['kandie_tpicker'], 'decode' );
	if( $tpicker_get ):
		$input = explode( '&', $tpicker_get );
		foreach( $input as $data):
			$key = strtok($data, '=');
			$result[$key] = strtok('='); 
		endforeach;
		return $result;
	else:
		return NULL;
	endif;
}

function taxonomy_picker_count_posts($tax_name, $term_slug) {
	echo "Count: $tax_name";
	$all = get_posts( array( 'taxonomy' => $tax_name, 'term' => $term_slug ) ); // need taxonomy query_var and term slug
	$count = count( $all );
	return $count;
}

/* 	Encode string to remove & and = so not taken as multiple variable
 *
 * 	@param	input string	string to decode
 *
 *	@return string 	t-picker encoded version of input
 */

function taxonomy_picker_encode($input) {
	return taxonomy_picker_dencode( $input, 'encode' );
}

/*	Decode string encoded by taxonomy_picker_encode()
 *
 * 	@param	input string	string to decode
 *
 *	@return string 	t-picker decoded version of input
 */
function taxonomy_picker_decode($input) {
	return taxonomy_picker_dencode( $input, 'decode' );
}

/*	Encode or decode string for taxonomy_pciker
 *
 * 	@param	input 		string	string to decode
 *	@param	direction	string	encode or decode (default) to indicate type of action required
 *
 *	@return string 	t-picker decoded version of input
 */

function taxonomy_picker_dencode( $input, $direction = 'decode') {

	$enq_bits = explode(   '!eq!', '!and!'); // Encoded text
	$plain_bits = explode( '='   , '&'    ); // Plain text
	if( strtolower($direction)  == 'encode') return htmlspecialchars( str_replace( $plain_bits, $enq_bits, $input ) );
	return str_replace( $enq_bits, $plain_bits,  htmlspecialchars_decode( $input ) );
}

/*	Get the text to use for the 'All' option for a taxonomy
 *
 * 	@param	$tax_name	String		Name of taxonomy
 *
 *	@return String 					All text to display
 */

function taxonomy_picker_all_text( $tax_name ) {
	$options = get_option('taxonomy-picker-options');
	$all_text = trim($options['all-format']); // Just in case!
	$override = trim($options['all-override']); // Just in case!
	
	if( $override )	$all_text = $override; // Override option for international users
	if( substr($all_text ,-6) == '{name}' ):
		$all_text = str_replace( '{name}', ucfirst($tax_name), $all_text );
	elseif( substr($all_text ,-7) == '{name}s' ):
		$all_text = str_replace( '{name}', ucfirst($tax_name), $all_text );
		if( substr($all_text,-2) == 'ys' ):
			 $all_text = substr_replace( $all_text, 'ies', -2 ); // ys => ies for neat plurals
		endif;				
	endif;
	
	return $all_text;
}

/*	Pre-process the $instance to consolidate taxonomy info in $instance['taxonomies']
 *
 * 	@param	$instance	Array		Array instance of taxonomy picker widget
 *
 *	@return String 					Update version of the instance
 */


function taxonomy_picker_taxonomies_array( $instance ) {
	// Pack up the taxonomy stuff as a single array
	foreach($instance as $key => $data_item):  // Loop through chosen list of taxonomies (by string detection on all items in the array)
		if( (strpos($key,'taxonomy_') === 0) ):  // Will only pick up shown taxonomies
			$taxonomy_name = substr($key,9); 
			$taxonomy_value = $instance[ 'fix_' . $taxonomy_name ];
			$taxonomy_orderby = $instance[ 'orderby_' . $taxonomy_name ];
			$taxonomy_sort = $instance[ 'sort_' . $taxonomy_name ];
	
			// Add the taxonomy to our array
			$instance['taxonomies'][$taxonomy_name] = 
				Array( 'name' => $taxonomy_name, 'value' => $taxonomy_value, 'hidden' => '', 'orderby' => $taxonomy_orderby, 'sort' => $taxonomy_sort); 
	
		elseif( (strpos($key,'fix_') === 0) ):
			$taxonomy_name = substr($key,4); 
			$taxonomy_value = $data_item;
			// Store in a temporary array
			if( $taxonomy_value <> ($taxonomy_name . '=all' ) ):
				$fixes[$taxonomy_name] = Array( 'name' => $taxonomy_name, 'value' => $taxonomy_value, 'hidden' => ' hidden' );
			endif;
		endif;
	endforeach;
	
	// Add in any fixes which aren't shown
	foreach($fixes as $fix) {if( empty($instance['taxonomies'][$fix['name']]) ) { $instance['taxonomies'][$fix['name']] = $fix; } }
	
	return $instance;
}

?>