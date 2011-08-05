<?php
// Display the widget

// tpicker[taxonomies='category,who,what'/]

add_shortcode( 'tpicker', 'taxonomy_picker_shortcode') ;

function taxonomy_picker_shortcode($atts, $content = ''){ 
		
	extract(shortcode_atts(array("taxonomies" => ""), $atts));
	
	$instance['taxonomies'] = implode( ',', $taxonomies );
	
}
?>