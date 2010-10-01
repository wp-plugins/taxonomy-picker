<?php

// Builds the Taxonomy Picker widget

add_action('widgets_init','register_phiz_find_helper');

function register_phiz_find_helper() {
	register_widget('FindHelperWidget');
}

class FindHelperWidget extends WP_Widget {

	function FindHelperWidget(){
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'taxonomy-picker', 'description' => __('Presents taxonomies as drop-downs so reader can pick query', 'taxonomy_picker') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'taxonomy_picker' );

		/* Create the widget. */
		$this->WP_Widget( 'taxonomy_picker', __('Taxonomy Picker', 'taxonomy_picker'), $widget_ops, $control_ops );
	}

// Display the widget
	function widget($args, $instance ){ 
		
		// Check whether to disaply on this page or not
		global $post;
		$pgs = explode(',',$instance['set_pages']);	
		if($instance['choose_pages']=='I'):  // Only allow specified pages
			$allowed = false;
			foreach($pgs as $pg):
				if($pg == $post->ID): // Page is allowed 
					$allowed = true;
					break;
				endif;
			endforeach;
			if(!$allowed):  // Not matched to exit function without displaying page
				return;
			endif;
		elseif($instance['choose_pages']=='E'): // Reject specified pages
			foreach($pgs as $pg):
				if($pg == $post->ID): // page matches so disallowed - break out of function
					return; 
				endif;
			endforeach;							
			// No category match so allow to proceed
		endif;

		// Main display section starts here - builds a form which is passed via POST

		extract( $args);		
		$title = apply_filters('widget_title', $instance['title'] );		
		echo $before_widget;
		if($title) echo $before_title.$title.$after_title;	
		echo '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" class="taxonomy-picker"><ul class="taxonomy-list">';
		echo "<li class='home search'><label>" . __("Search") .":</label><br/><input name='s' value='' style='width:90%;'></li>";  // Search text box
		$counter=count($instance);  // Decrement to 2 to find last item
		$css_class=''; // Use on first <li>
		$instance['set_categories'] = str_replace(' ','',$instance['set_categories']);  // Remove any excess spaces
		foreach($instance as $key => $data_item):  // Loop through chosen list of taxonomies
			if( (strpos($key,'taxonomy_') === 0) && $data_item):
				$taxonomy = get_taxonomy( substr($key,9) ); // Get the taxonomy object
				$terms = get_terms($taxonomy->name);
				if($taxonomy->name == 'category'):  // Overwrite the title for category and link_category
					$taxonomy_label	= $instance['category_title']; // From the widget form in the dashboard
				elseif($taxonomy_label == 'link_category'):
					$taxonomy_label	= 'Link Categories';
				else:
					$taxonomy_label = $taxonomy->label;
				endif;
				if(2 == $counter--):
					$css_class = "class='last'"; // Set class=last on final <li>
				endif;
				$tax_label = __($taxonomy_label);
				echo "<li $css_class><label>$tax_label:</label><br/><select name='$taxonomy->name' style='width:100%;'>";
				$css_class=''; // After home reset to '' until set to last
				echo "<option value='$taxonomy->name=all' selected>".__("** All **")."</option>";
				foreach($terms as $term):  // Loop through terms in the taxonomy
					if($taxonomy->name=='category'):
						$option_name = 'cat='. $term->term_id; // Pass in a format which suits query_posts - for categories cat=id works best
						$cats = explode(',',$instance['set_categories']);
						if($instance['choose_categories']=='I'):  // Only allow specified categories
							$set_categories = 'cat=' . $instance['set_categories']; // We can pass it as is ot will become the list of all categories for query_posts
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
					else:
						$allowed = true;
						$option_name = $taxonomy->name.'='.$term->name;
					endif;
					$t_name = __($term->name);
					if($allowed) echo "<option value='$option_name' >$t_name</option>";
				endforeach;
				echo "</select></li>";
			endif;
		endforeach;
		echo "</ul><input type='hidden' name='set_categories' value='$set_categories' />";
		echo "</ul><input type='hidden' name='kate-phizackerley' value='taxonomy-picker' />";
		echo '<p style="text-align:center;margin-top:8px;"><input type="submit" value="Search"/></p></form>';
		echo $after_widget;
	}

	
	function update($new_instance, $old_instance) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category_title'] = strip_tags( $instance['category_title'] );
		$new_instance['set_categories'] = strip_tags( $new_instance['set_categories'] );
		$new_instance['set_pages'] = strip_tags( $new_instance['set_pages'] );
		return $new_instance;
	}
	
	function form ($instance) {
		// Set up some defaults
		$defaults = array( 'title' => __('Example', 'example'), 'choose_categories' => 'A', 'choose_pages' => 'A');
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		// Widget title
	    $title_id = $this->get_field_id( 'title' );
	    $title_name = $this->get_field_name('title');
	    $title_value = $instance['title'];
// Echo a Heredoc
echo <<<INPUT_FORM_TITLE
<p><fieldset id="taxonomy-picker-title">
	<label for="$title_id">Title:</label>
 	<input id="$title_id" name="$title_name" value="$title_value" style="width:100%;" /></fieldset></p><hr>
 	<fieldset id="taxonomy-picker-taxonomoies"><h3>Taxonomies</h3><!-- Select Taxonomies -->			     
INPUT_FORM_TITLE;
		
		$taxes = get_taxonomies('','names');
		if(count($taxes)>0):
			foreach($taxes as $tax):
				$tax_stem = 'taxonomy_'.$tax;
				$taxonomy = get_taxonomy($tax);
				$taxonomy_label = ($taxonomy->name=='link_category') ? 'Link Categories' : $taxonomy->label;
				$tax_id = $this->get_field_id($tax_stem);
				$tax_name = $this->get_field_name($tax_stem);
				$radio_checked = ($instance[$tax_stem]=='on') ? 'checked ' : '';
				echo "<p><input id='$tax_id' class='checkbox' type='checkbox' name='$tax_name' $radio_checked />";
				echo "&nbsp;<label for='$tax_id' title='$tax_stem'>$taxonomy_label</label></p>";
			endforeach;
		endif;

		// Select Categories
		
		$title_id = $this->get_field_id( 'category_title' );
	    $title_name = $this->get_field_name('category_title');
	    $title_value = $instance['category_title'];

		?>'</fieldset><hr><fieldset id="taxonomy-picker-categories"<p><h3>Categories</h3></p>
		<p style='float:left;'><label for="$cat_title_id"><b>Title:</b></label></p><p style="float:right;width:75%;"><?php
		echo "<input id='$title_id' name='$title_name' value='$title_value' style='width:90%;' />";
		?></p><br style="clear:both;"/><label><b>Select:&nbsp;&nbsp;</b></label><?php

		// Build radio buttons for All, Incl , Excl for categories	
		$radio_id = $this->get_field_id('choose_categories');
		$radio_name = $this->get_field_name('choose_categories');
		$radio_value = $instance['choose_categories'];
		$radio_checked = ($instance['choose_categories']=='A')?'checked':'';
		echo "All:&nbsp;<input type='radio' name='$radio_name' value='A' $radio_checked />&nbsp;|&nbsp;"; 
		$radio_checked = ($instance['choose_categories']=='I')?'checked':'';
		echo "Incl:&nbsp;<input type='radio' name='$radio_name' value='I' $radio_checked />&nbsp;|&nbsp;"; 
		$radio_checked = ($instance['choose_categories']=='E')?'checked':'';
		echo "Excl:&nbsp;<input type='radio' name='$radio_name' value='E' $radio_checked /><br/>"; 

		$input_id = $this->get_field_id('set_categories');
		$input_name = $this->get_field_name('set_categories');
		$input_value = $instance['set_categories'];
		echo "<input id='$input_id' name='$input_name'  value='$input_value' style='width:100%;margin-top:2px;'/>"
		?> <i style="font-size:75%">Enter category IDs separated by commas</i>
		
		</fieldset><hr><fieldset id="taxonomy-picker-pages"><p><h3>Pages</h3></p><label><b>Select:&nbsp;&nbsp;</b></label><?php // Select Pages
		$radio_id = $this->get_field_id('choose_pages');
		$radio_name = $this->get_field_name('choose_pages');
		$radio_value = $instance['choose_pages'];
		$radio_checked = ($instance['choose_pages']=='A')?'checked':'';
		echo "All:&nbsp;<input type='radio' name='$radio_name' value='A' $radio_checked />&nbsp;|&nbsp;"; 
		$radio_checked = ($instance['choose_pages']=='I')?'checked':'';
		echo "Incl:&nbsp;<input type='radio' name='$radio_name' value='I' $radio_checked />&nbsp;|&nbsp;"; 
		$radio_checked = ($instance['choose_pages']=='E')?'checked':'';
		echo "Excl:&nbsp;<input type='radio' name='$radio_name' value='E' $radio_checked /><br/>"; 

		$input_id = $this->get_field_id('set_pages');
		$input_name = $this->get_field_name('set_pages');
		$input_value = $instance['set_pages'];
		echo "<input id='$input_id' name='$input_name' value='$input_value' style='width:100%;margin-top:2px;'/>";
		?> <i style="font-size:75%">Enter page IDs separated by commas</i></fieldset></form><?php 
	}
}

?>