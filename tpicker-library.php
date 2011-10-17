<?php

/* Class-based library
 * Functons shared by the shortcode and widget
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
			$instance['taxonomies'][$taxonomy_name] = Array( 
					'name' => $taxonomy_name, 
					'value' => $taxonomy_value,
					'hidden' => '',
					'orderby' => $taxonomy_orderby,
					'sort' => $taxonomy_sort,
					'ancestors '=> array() 
				); 
	
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
	$widget = new taxonomy_picker_widget( $instance, $args);
	return $widget->display( false ); // Return a widget for display
}



/***
 * If the query was "remembered", returns a representation of the query
 *
 * @return	query string
 */
function tpicker_query_string() {
	// Check whether we displaying the results of a prevous use (ie. kandie_tpicker is set)
	$tpicker_inputs = taxonomy_picker_tpicker_array();
	if( empty( $tpicker_inputs ) ):
		return "";
	else:
		foreach( $tpicker_inputs as $key => $data ):
			$taxonomy = get_taxonomy( ($key == 'tag') ? 'post_tag' : $key );
			$result .= $taxonomy->label . ': ' . $data . '; '; 
		endforeach;
	endif;
	return $result;
}




                                   /**************************************************/


/****
 * Class for the taxonomy widget which is displayed on the front of site.  Create it and display with the display() function.
 */

class taxonomy_picker_widget {
	private $taxonomies;  // Array of taxonomies
	private $options; // Array of options from the Admin panel
	private $combo; // Combobox type
	
	private $set_categories;
	private $choose_categories;
	private $this_categories;
	
	private $HTML = ''; // HTML of the widget 	
	private $before_widget = '<div class="widget taxonomy-picker widget-taxonomy-picker"><div class="widget-inside">';	
	private $after_widget = '</div></div>';
	private $before_title = '';	
	private $after_title = '';
	private $title = '';
	private $hidesearch = false; 
	private $tax_type; // Temporary storage of the tree type for the taxonomy while processing
	private $term_args; // Default term arguments for get_terms
	
	/***
	 * Builds HTML for a taxonomy picker widget in memory
	 *
	 * @param $args 		array
	 * @param $instance 	array	an instance of a widget or an array in similar form
	 *								'title' => The widget title
	 *
	 * @return string		HTML of the built widget ready for display
	 */

	public function __construct($instance, $args) {
	
		// Check whether we displaying the results of a prevous use (ie. kandie_tpicker is set)
		$this->inputs = apply_filters('tpicker_inputs', taxonomy_picker_tpicker_array() );
		
		// Get the configuration options from the database
		$this->options = apply_filters('tpicker_options', get_option('taxonomy-picker-options') );
		if( empty( $this->options['empty-terms'] ) ) $this->options['empty-terms'] = 'always'; // Upgrade defence for new option
	
		// Upgrade defence for v1.8 - won't be needed long term.  If taxonomies haven't been set, process the instance
		if( empty($instance['taxonomies']) )  { $instance = taxonomy_picker_taxonomies_array( $instance ); } // Pre-process the instance for efficiency
		
		$this->combo = $instance['combo']; // Saved our combobox type
	
		foreach($instance['taxonomies'] as $taxonomy_name => $data_item):  // Loop through chosen list of taxonomies 
			$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
			$tax_label = __( ( $taxonomy_name == 'category' ) ? $instance['category_title'] : $taxonomy->label ) . $this->options['punctuation']; 
			$this->taxonomies[$tax_label] = $data_item;  // Save the main item
		endforeach;
		ksort( $this->taxonomies ); //Put taxonomies into alpha label order
		$this->taxonomies = apply_filters( 'tpicker_taxonomies', $this->taxonomies); // Filter taxonomy order
	
		if( $args ) extract( $args); // Unpack $before_widget etc
		
		$title = apply_filters('widget_title', $instance['title'] );		
		if($title) $title = $before_title.$title.$after_title;	// Wrap it
		
		$this->before_widget = apply_filters('tpicker_before' , ( ($before_widget) ? $before_widget : $this->before_widget ) );
		$this->after_widget = apply_filters('tpicker_after' , ( ($after_widget) ? $after_widget : $this->after_widget ) );
		
		$this->hidesearch = $instance['hidesearch']; // Defaults to show (false)
		
	
		$this->choose_categories = $instance['choose_categories'];
		$cats = explode(',',$this->set_categories);
	
		// Limit list of categories
		if($this->choose_categories=='I'):  // Only allow specified categories
			$set_categories = 'cat=' . $instance['set_categories']; // We can pass it as is because it will become the list of all categories for query_posts
			foreach($cats as $cat):  // Test against each of our permitted categories
				$this->categories[$cat] = get_term( $cat, 'category' );  // Add individual categories to the array
			endforeach;
		elseif($instance['choose_categories']=='E'): // Reject specified categories
			$set_categories = 'cat=-'.str_replace(',',',-',$instance['set_categories']); // Prefix each cat id with - to exclude it
			$all_cats[] = get_terms('category' );  // Add individual categories to the array
			foreach($all_cats as $acat):  // Test against each of our permitted categories
				$allowed = true;
				foreach($cats as $cat):  // Test against each of our permitted categories
					if( $acat->ID == $cat):
						$allowed = false;
						break;
					endif;
				endforeach;			
				if($allowed):
					$this->categories[$cat] = get_term( $cat, 'category' );  // Add individual categories to the array
				endif;
			endforeach;
		else: // all - no display testing needed but we need to set $set_categories;
			$set_categories = '';		
		endif;

		// Set default term arguments for get_terms
		switch( $this->options['empty-terms'] ): // How to handle empty items
		case 'always':
			$this->term_args['hide_empty'] = 0;
			break;
		case 'never':
			$this->term_args['hide_empty'] = 1;
			$this->term_args['hierarchical'] = 1;
			break;
		case 'sometimes':
			$this->term_args['hide_empty'] = 1;
			$this->term_args['hierarchical'] = 1;
		endswitch;
		$this->term_args['pad_counts'] = 1;
		
		return true; 
	}

	// Display the widget by echoing it
	public function display( $echo = true ) {
		if(!$this->HTML) $this->build_widget();  // Build it if we need too
		if($echo) echo $this->HTML;
		return $this->HTML;
	}



	private function build_widget() {
		$this->HTML = $this->before_widget . $this->title;
		$this->HTML .= '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" class="taxonomy-picker t-picker" id="taxonomy-picker"><ul class="taxonomy-list">';
		
		$search_text = ($this->options['search-text']) ? $this->options['search-text'] : __('Search');
		if( !$this->hidesearch ):
			$this->HTML .= "<li class='home search first'><label>"  . apply_filters('tpicker_search_text',  $search_text);
			$this->HTML .= "</label><br/><input name='s' value='' type='text' class'tpicker-search-text'></li>";  // Search text box
			$css_class="";
		else:
			$css_class='first home ';
		endif;
		
		foreach($this->taxonomies as $tax_label => $data_item): 
			$this->HTML .= $this->build_taxonomy($tax_label, $data_item, $css_class );  // loop taxomomies
		endforeach;
		
		$this->HTML .= "<input type='hidden' name='set_categories' value='$set_categories' />";
		$this->HTML .= "<input type='hidden' name='kate-phizackerley' value='taxonomy-picker' />";
		$this->HTML .= '<li style="height:8px;" class="last"></li></ul><p style="text-align:center;margin:0 auto;">';
		
		if($this->options['remember']):
			// $this->HTML .= "<p onclick='document.getElementById(\"taxonomy-picker\").reset()';>Clear</p>";  // Sort out in v2.0
		else:
			$this->HTML .= '<input type="reset" value="Reset" style="margin-right:10%;" />';
		endif;
				
		$this->HTML .= "<input type='submit' value='$search_text' /></p></form>";
		
		$this->HTML .= $this->after_widget;	
		
		return $this->HTML;			
		
	}
	
	private function build_taxonomy($tax_label, $data_item, $css_class) {
	
	    // Set up any request for the sorting of the terms
	    $term_args = $this->term_args; // Read down defaults
		if( $data_item['orderby'] ) $term_args['orderby'] = $data_item['orderby'];
		if( $data_item['sort'] ) $term_args['order'] = $data_item['sort'];
				
		$taxonomy_name = $data_item['name'];
		$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
	
		if( $data_item['hidden'] ) return "<input type='hidden' name='$taxonomy_name' value='" . $data_item['value'] . "' />"; // Hidden 
		
		if( ($taxonomy_name == 'category') and ($this->choose_categories<>'A') ):
			$terms = $this->categories;
		elseif( $taxonomy_name == "post_tag" ):
			$terms = get_tags($term_args);
			$taxonomy_name = 'tag';
		else:
			$terms = ( substr($data_item['orderby'],-4) == 'tree' ) ? kandie_get_terms_tree( $taxonomy_name, $term_args ) : get_terms($taxonomy_name, $term_args );
		endif;

		$css_class .= ( $data_item['orderby'] == 'pruned_tree' ) ? 'tree pruned' : $data_item['orderby'] ; // Set the class for the containing <li>
		$this->tax_type = $data_item['orderby'];
	
		
		$result .= "<li class='$css_class'><label style='float:left;'>$tax_label</label>";
		$css_class=''; // After home reset to ''
					
		
		// Pre-process tree to find ancestors - for non-trees will just do selected 
		foreach($terms as $term): 

			$current = new taxonomy_picker_ancestor($taxonomy_name, $term); 

			if( empty($this->inputs) ): 
				$current->selected = ($data_item['value'] == ($taxonomy_name . '=' . $term->slug) ) ? 'selected' : '';
			else:
				$current->selected = ($this->inputs[$taxonomy_name] == $term->slug) ? 'selected' : '';
			endif;

			if( substr( $data_item['orderby'] ,-4) == 'tree'): // For trees we need more
			
				if( !empty( $current->parent ) and ( $current->level < 6 ) ): 
					$parent = $this->taxonomies[$tax_label]['ancestors'][ $current->parent ] ;
					if( $parent->selected ) $current->is_kid = true; // Immediate child of selected item
					if( $current->selected ) $parent->is_father = true; // Immediate parent 
					$current->level = $parent->level + 1;
				else:
					$current->level = 1; // No parent so level 1			
				endif;
				
	
				if( $current->selected  ):  // Only need to look for ancestors of selected items in trees	
					$ancestor = $current;
					while( !empty( $ancestor->parent ) ): // For the selected item we need to identify the ancestral trail. Will work fine for trees, otherwise who cares?
						$ancestor = $this->taxonomies[$tax_label]['ancestors'][ $ancestor->parent ] ; // Get the parent ancestor
						$ancestor->is_ancestor = true; // Define as an ancestor
						$this->taxonomies[$tax_label]['ancestors'][ $ancestor->slug ] = $ancestor; // Store before looping
					endwhile;	
				endif;		
					
			endif; // Trees

			$this->taxonomies[$tax_label]['ancestors'][ $term->slug ] = $current;

		endforeach; // Terms
		
		switch( $this->combo ):

			
		case '':
		case 'flat': // Normal combo box

			$result .= "<select name='$taxonomy_name'>"; 
			if( taxonomy_picker_all_text($tax_label) <> 'N/A' ):	
				$result .= "<option value='$taxonomy_name=all'>". taxonomy_picker_all_text($tax_label) ."</option>";// ** ALL **
			endif;
			foreach($terms as $term): 
				$option_name = ($taxonomy_name == 'category' ) ? 'cat='. $term->term_id : $taxonomy_name.'='.$term->slug;
				$result .=  $this->build_term_select($tax_label, $term, $option_name) ; // Loop through terms in the taxonomy
			endforeach;
						
			$result .= "</select></li>";
			break;

		case 'jQuery-tree': // Uses http://kotowicz.net/jquery-option-tree/demo/demo.html OptionTree plugin

			$result .= "<p><input type='text' name='$taxonomy_name' /></p><script type='text/javascript'>";

//			$result .= "jQuery(document).ready(function($){" ;

			$result .= "(function($){";
			
			$result .= "alert('BOUNCE');";

			$result .= "$(function() {var option_tree = {'Option 1': {'Suboption':200}, 'Option 2': {'Suboption 2': {'Subsub 1':201, 'Subsub 2':202},
				'Suboption 3': {'Subsub 3':203, 'Subsub 4':204, 'Subsub 5':205}}};";   
			$result .= "$('input[name=$taxonomy_name].nested').optionTree(option_tree);});";  
			
			$result .= "}) (jQuery)";

//			$result .= "});";
			
			$result .= "</script></li>";
			
		endswitch;
		
				
		return $result;	
	}

	/***
	 * Build a single select item
	 *
	 * @param	$tax_name		string	Name of the taxonomy
	 * @param	$term			object	Term object
	 * @param	$option_name	string 	Name of the select item
	 */

	private function build_term_select($tax_label, $term, $option_name) {
		
		$current = $this->taxonomies[$tax_label]['ancestors'][$term->slug]; // Get details of current item from ancestors tree

		$css_class = ( empty($term->parent) )  ? 'parent' : 'child'; // Top level are parents, rest are child
		
		if( substr($this->tax_type, -4) == 'tree' ):  // Just for efficiency
			if( $current->is_kid ) $css_class .=  ' kid'; 
			if( $current->is_ancestor ) $css_class .= ' ancestor';
			if( $current->is_father ) $css_class .= ' father';
			if( $current->selected ) $css_class .= ' selected';
			if( !empty( $current->parent ) ):
				$parent = $this->taxonomies[$tax_label]['ancestors'][$current->parent]; // Get details of current item from ancestors tree
				if( $parent->is_father ) $css_class .= ' sibling';
			endif;
			$css_class .= ' level'.sprintf($current->level, '%d'); // Add a class for the level
	
		endif;
		
		$t_name = $term->name . ( ( $this->options['show-count'] ) ? " ({$term->count})" : "" );

		$result = "<option value='$option_name' $current->selected class='$css_class'>$t_name</option>";														
		return $result;
	} 

}

// Class of ancestors
class taxonomy_picker_ancestor {

	public $slug; // Slug of item
	public $parent; // Slug of parent
	public $selected=''; // String of selection text (or blank)
	public $is_kid=false; // True if immediate parent is the selected item
	public $is_ancestor=false; // True if an ancestor of the selected item
	public $level = 0; // Level o is "all items", first data is level 1  
	public $is_father=false; // True if immediate parent
		
	// Consructor - pass a term object
	public function __construct($taxonomy_name, $term) {
		$this->slug = $term->slug;
		if( !empty( $term->parent ) ):
			$parent = get_term( $term->parent, $taxonomy_name);
			$this->parent = $parent->slug;
		endif;
	}
}

?>