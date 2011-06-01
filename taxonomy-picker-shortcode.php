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
?>