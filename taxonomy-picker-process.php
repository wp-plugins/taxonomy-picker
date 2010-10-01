<?php

// Tidy up the POST out put of the Taxonomy Widget form to pass to the blog homepage via URI to drive normal searching behaviour
// Version 1.0


function taxonomy_picker_process() {  // Build a URI form the data POSTed by the widget form

	if(count($_POST)>0):
		$post_vars = $_POST;
		if($post_vars['kate-phizackerley'] <> 'taxonomy-picker'):	
			return; // POSTED data wasn't for Taxonomy Picker
		endif;
		$custom_query=''; 
		foreach($post_vars as $item => $data):
			if($item <> 'set_categories' and $item <> 's' and $item <> 'kate-phizackerley'): // We have a result from a combo box						
				if(strpos($data,'=all') === false):  // Specific taxonomy picked
					$custom_query .= ( ($custom_query) ? '&' : '' ) . str_replace(' ','-',$data);  // eg add &writer=Kate-Phizackerley
				elseif($item == 'category'): // For All categories we need to restrict search to the specified in the dashbaord
					$custom_query .= $_POST['set_categories'];  // Already prepared for use as comma delim set of cat ids before POSTing
				endif;
			endif;
		endforeach;
		if($post_vars['s'] <> ''):
			 $custom_query = 's='.$post_vars['s'] . (($custom_query) ? '&' : ''). $custom_query;  // Add text search option into URI
		endif;
		if($custom_query):  // We have a search string
			$blog_url = get_bloginfo('url');
			$blog_url = (($blog_url[-1] == '/') ? $blog_url : $blog_url . '/').'?'.$custom_query;  // Our composite URL for searching
			wp_redirect($blog_url, 302 );  // Redirect to the built URI
			die();
		endif;
	endif;
return;
}
?>