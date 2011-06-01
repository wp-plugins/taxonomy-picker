<?php

/* Functons shared by the shortcode and widget
 * Version: 1.1
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
	$input = explode( '&', taxonomy_picker_dencode( $_GET['kandie_tpicker'], 'decode' ) );
	foreach( $input as $data):
		$key = strtok($data, '=');
		$result[$key] = strtok('='); 
	endforeach;
	return $result;
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

	$enq_bits = explode(',', '!eq!,!and!'); // Encoded text
	$plain_bits = explode(',','=,&'); // Plain text
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

?>