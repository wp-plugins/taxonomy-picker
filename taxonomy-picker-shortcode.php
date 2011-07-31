<?php
// Display the widget

add_shortcode( 'taxonomy-picker', 'taxonomy_picker_shortcode') ;

function taxonomy_picker_shortcode($atts, $content = ''){ 
		
	extract(shortcode_atts(array("taxonomies" => ""), $atts));
	
	$result = '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" class="taxonomy-picker">';
	$result .= '<ul class="taxonomy-list">';
	$result .= "<li class='home search'><label>" . __("Search") .":</label><br/><input name='s' value='' style='width:90%;'></li>";  // Search text box
	
	$result .= "<style type='text/css'>.taxonomy-picker li {float:left; width: 180px; list-style:none;}</style>";
	
	$taxonomies = explode(',', $taxonomies );
	foreach($taxonomies as $tax_name):  // Loop through chosen list of taxonomies
		$taxonomy = get_taxonomy( $tax_name ); // Get the taxonomy object
		$terms = get_terms($tax_name);
		$tax_label = __($taxonomy->label);

		$result .= "<li><label>$tax_label:</label><br/><select name='$tax_name' class='taxonomy-label'>";
		$result .= "<option value='$taxonomy->name=all'>". taxonomy_picker_all_text($tax_label) ."</option>";

		foreach($terms as $term):  // Loop through terms in the taxonomy
			$t_name = __($term->name);
			$option_name = $taxonomy->name.'='.$term->name;
			$selected = ($tpicker_inputs[$taxonomy->name] == $term->slug) ? ' selected="selected"' : '';
			$result.= "<option value='$option_name' $selected>$t_name</option>"; 
		endforeach;
		$result .= "</select></li>";
	
	endforeach;
	$result .=  "</ul><input type='hidden' name='set_categories' value='$set_categories' />";
	$result .=  "</ul><input type='hidden' name='kate-phizackerley' value='taxonomy-picker' />";
	$result .=  '<br style="clear:both;"/><input type="submit" value="Search"/>';
	$result .=  '</form>';

	return $result;
}


class taonomy_picker_form {
	
	private $taxonomies; // The taxonomies to show - array [name of taxonomy] => Description text
	private $terms;	// Array of terms [name of taxonomy] => Array of term?? 
	private $options; // Taxonomy Picker Options in the database
	
	/**
	 * Constructor
	 *
	 * @param $tax_n_terms	Mixed	String: command separated list of taxonomy names, optionally with terms in brackets, 
	 										restricted to one term with =, or description with : e.g. 'color(red,blue),size=large,weight:Product Weight'
	 *								Array: same as an array of parts e.g  color=Red Items(dark red,light red) or size[0] or product[cars]
	 */
	 							
	function __construct( $tax_n_terms) {
		
		if( is_string($tax_n_terms) ) $tax_n_terms = explode(',', $tax_n_terms);
		self::$options = get_option('taxonomy-picker-options');
		$hide_empty = (self::$options['hide-empty'] == 'on') ? 1 : 0;
		
		
		foreach( $tax_n_terms as $t):
			
			$i = strpos( $t,'(');
			if($i): // Restrict terms for this taxonomy
				$terms = str_replace( ')', '', substr($t, $i+1) );
				$t = substr( $t, 0, $i);
			else:
				$terms = '';
			endif;
			
			$i = strpos( $t,'=');
			if($i): // Restrict terms for this taxonomy
				$desc = trim( substr($t, $i+1) );
				$t = trim( substr( $t, 0, $i) );
			else:
				$desc = trim( $t );
			endif;

			$i = strpos( $t,'[');
			if($i): // Restrict terms for this taxonomy
				$origin = str_replace( ']', '', substr($t, $i+1) );
				$name = substr( $t, 0, $i);
			else:
				$origin = '';
				$name = $t;
			endif;


			if( taxonomy_exists($name) ): 
				$args= array('orderby' => 'name', 'hide_empty' => $hide_empty );
				if( $origin <> '' ) $arg['parent'] = $origin;
				$all_terms = get_terms($name, $args);
				if($terms) $all_terms = array_intersect( $all_terms, explode(',', $terms) ); // Only terms which are specified and exist
				self::$taxonomies[$name] = $desc;
				self::$terms[$name] = $all_terms;
			endif;
			
		endforeach;
		
	}
}

?>