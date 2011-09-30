<?php

/* Functons shared by the shortcode and widget - Deprecated version
 * Version: 1.10.7
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
	if( !isset( $_GET['kandie_tpicker'] ) ) return null; 
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
	if( strtolower($direction)  == 'encode') return htmlentities( str_replace( $plain_bits, $enq_bits, $input ) );
	return str_replace( $enq_bits, $plain_bits,  html_entity_decode( $input ) );
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

/***
 * Displays a taxonomy picker widget
 *
 * @param $args 		array
 * @param $instance 	array	an instance of a widget or an array in similar form
 *
 * @return string		HTML of the built widget ready for display
 */

function taxonomy_picker_display_widget( $instance, $args = null ) {
		
	// Check whether we displaying the results of a prevous use (ie. kandie_tpicker is set)
	$tpicker_inputs = taxonomy_picker_tpicker_array();
	
	// Get the configuration options from the database
	$tpicker_options = get_option('taxonomy-picker-options');

	// Upgrade defence for v1.8 - won't be needed long term.  If taxonomies haven't been set, process the instance
	iF( empty($instance['taxonomies']) )  { $instance = taxonomy_picker_taxonomies_array( $instance ); } // Pre-process the instance for efficiency

	// Main display section starts here - builds a form which is passed via POST

	if( $args ): 
		extract( $args); // Unpack $before_widget etc
	else:
		$before_widget = '<div class="widget taxonomy-picker widget-taxonomy-picker"><div class="widget-inside">';
		$after_widget  = '</div></div>';
	endif;
		
	$title = apply_filters('widget_title', $instance['title'] );		
	$result = $before_widget;
	if($title) $result .= $before_title.$title.$after_title;	
	$result .= '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" class="taxonomy-picker" id="taxonomy-picker"><ul class="taxonomy-list">';
	
	$search_text = ($tpicker_options['search-text']) ? $tpicker_options['search-text'] : __('Search');
	if( !$instance['hidesearch'] ):
		$result .= "<li class='home search first'><label>"  . apply_filters('tpicker-search-text',  $search_text);
		$result .= "</label><br/><input name='s' value='' type='text' style='width:90%;'></li>";  // Search text box
		$css_class="";
	else:
		$css_class='first home ';
	endif;
	
	

	foreach($instance['taxonomies'] as $taxonomy_name => $data_item):  // Loop through chosen list of taxonomies 
		$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
		$tax_label = __( ( $taxonomy_name == 'category' ) ? $instance['category_title'] : $taxonomy->label ) . $tpicker_options['punctuation']; 
		$taxies[$tax_label] = $data_item;
	endforeach;
	ksort( $taxies ); //Put taxonomies into alpha label order
	$taxies = apply_filters( 'tpicker-taxonomies', $taxies); // Filter taxonomy order
	
	foreach($taxies as $tax_label => $data_item):  // Loop through chosen list of taxonomies (by string detection on all items in the array)
	
		// Set up any request for the sorting of the terms
		if( $data_item['orderby'] ) $term_args['orderby'] = $data_item['orderby'];
		if( $data_item['sort'] ) $term_args['order'] = $data_item['sort'];
		
		switch( $tpicker_options['empty-terms'] ): // How to handle empty items
		case 'always':
			$term_args['hide_empty'] = 0;
			break;
		case 'never':
			$term_args['hide_empty'] = 1;
			$term_args['hierarchical'] = 1;
			break;
		case 'sometimes':
			$term_args['hide_empty'] = 1;
			$term_args['hierarchical'] = 1;
		endswitch;
		
		$taxonomy_name = $data_item['name'];
		$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
		$terms = ( $data_item['orderby'] == 'tree' ) ? kandie_get_terms_tree( $taxonomy_name, $term_args ) : get_terms($taxonomy_name, $term_args );

		if( $data_item['hidden'] ):
			$result .= "<input type='hidden' name='$taxonomy_name' value='" . $data_item['value'] . "' />";
				
		elseif( taxonomy_picker_all_text($tax_label) <> 'N/A' ): // Main live display of combobox
			$css_class .= $data_item['orderby'];
			$result .= "<li class='$css_class'><label style='float:left;'>$tax_label</label><select name='$taxonomy_name'>"; 
			
			$result .= "<option value='$taxonomy_name=all'>". taxonomy_picker_all_text($tax_label) ."</option>";
			$css_class=''; // After home reset to ''

			foreach($terms as $term):  // Loop through terms in the taxonomy

				// ** Categories only ** //
				if( $taxonomy_name == 'category' ):
				
					$option_name = 'cat='. $term->term_id; // Pass in a format which suits query_posts - for categories cat=id works best
					$cats = explode(',',$instance['set_categories']);
					
					if($instance['choose_categories']=='I'):  // Only allow specified categories
						$set_categories = 'cat=' . $instance['set_categories']; // We can pass it as is because it will become the list of all categories for query_posts
						$allowed = false;
						foreach($cats as $cat):  // Test against each of our permitted categories
							if($cat == $term->term_id): // Category matches so allowed
								$allowed = true;
								break;
							endif;
						endforeach;
					elseif($instance['choose_categories']=='E'): // Reject specified categories
						$set_categories = 'cat=-'.str_replace(',',',-',$instance['set_categories']); // Prefix each cat id with - to exclude it
						$allowed = true;
						foreach($cats as $cat):
							if($cat == $term->term_id): // Category matches so disallowed - break out of loop
								$allowed = false;
								break;
							endif;
						endforeach;							
						// No category match so allow to proceed
					else: // all - no display testing needed but we need to set $set_categories;
						$set_categories = '';		
						$allowed=true; // All categories allowed				
					endif;
				
				// ** Other Taxonomies ** //	
				else:
					$allowed = true;
					$option_name = $taxonomy_name.'='.$term->slug;
				endif;
									
				$t_name = __($term->name);
				
				$selected = '';
				if( empty($tpicker_inputs) ): 
					$selected = ($data_item['value'] == ($taxonomy_name . '=' . $term->slug) ) ? 'on' : '';
				else:
					$selected = ($tpicker_inputs[$taxonomy_name] == $term->slug) ? 'on' : '';
				endif;
				
				
				if($tpicker_options['show-count'] and $allowed ): 
					$result .= taxonomy_picker_widget_select_option( $option_name, "$t_name ({$term->count})", $selected, $term->parent );
				elseif($allowed):
					$result .= taxonomy_picker_widget_select_option( $option_name, $t_name, $selected, $term->parent  );
				endif;
			endforeach;

			$result .= "</select></li>";
			
		endif; // Hidden?
		
	
	endforeach;
	unset($taxies);
	
	$result .= "<input type='hidden' name='set_categories' value='$set_categories' />";
	$result .= "<input type='hidden' name='kate-phizackerley' value='taxonomy-picker' />";
	$result .= '<li style="height:8px;" class="last"></li></ul><p style="text-align:center;margin:0 auto;">';
	
	if( isset($tpicker_options['remember']) ):
		// $result .= "<p onclick='document.getElementById(\"taxonomy-picker\").reset()';>Clear</p>";  // Sort out in v2.0
	else:
		$result .= '<input type="reset" value="Reset" style="margin-right:10%;" />';
	endif;
			
	$result .= "<input type='submit' value='$search_text' /></p></form>";
	
	$result .= $after_widget;	
	
	return $result;
}
/***
 * Displays an option value - relegated to  function so we can add a function table
 *
 * @param	$option_name	string	name of the option
 * @param	$option_label	string	label for the option
 * @param	$selected		mixed	anything non-empty or empty
 * @param 	$parent			string	the term's parent, if there is one
 * @return	
 */

function taxonomy_picker_widget_select_option( $option_name, $option_label, $selected,  $parent = '') {
	if($selected) $selected =  'selected="selected"'; // force correct format
	$css_class = ($parent) ? "child" : "parent" ; // If there is a parent, then it is a child
	return "<option value='$option_name' $selected class='$css_class'>$option_label</option>";
}

?>