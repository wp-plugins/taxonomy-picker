<?php

// Version: 1.6
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

// Display the widget on the front of the site
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

		// Check whether we displaying the results of a prevous use (ie. kandie_tpicker is set)
		$tpicker_inputs = taxonomy_picker_tpicker_array();
		
		// Get the configuration options from the database
		$tpicker_options = get_option('taxonomy-picker-options');

		// Upgrade defence for v1.8 - won't be needed long term.  If taxonomies haven't been set, process the instance
		iF( empty($instance['taxonomies']) )  { $instance = taxonomy_picker_taxonomies_array( $instance ); } // Pre-process the instance for efficiency

		// Main display section starts here - builds a form which is passed via POST

		extract( $args);		
		$title = apply_filters('widget_title', $instance['title'] );		
		echo $before_widget;
		if($title) echo $before_title.$title.$after_title;	
		echo '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" class="taxonomy-picker" id="taxonomy-picker"><ul class="taxonomy-list">';
		
		if( !$instance['hidesearch'] ):
			$jfns = " onblur='this.value=removeSpaces(this.value);' onfocus='this.value=restoreSpaces(this.value);' ";
			$search_text = ($tpicker_options['search-text']) ? $tpicker_options['search-text'] : __('Search');
			echo "<li class='home search first'><label>$search_text:</label><br/><input name='s' value='' type='text' style='width:90%;' $jfns></li>";  // Search text box
			$css_class="";
		else:
			$css_class="class='first home'";
		endif;
		

		
		foreach($instance['taxonomies'] as $taxonomy_name => $data_item):  // Loop through chosen list of taxonomies 
			$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
			$tax_label = __( ( $taxonomy_name == 'category' ) ? $instance['category_title'] : $taxonomy->label ) . $tpicker_options['punctuation']; 
			$taxies[$tax_label] = $data_item;
		endforeach;
		ksort( $taxies ); //Put taxonomies into alpha label order
		
		foreach($taxies as $tax_label => $data_item):  // Loop through chosen list of taxonomies (by string detection on all items in the array)
			$taxonomy_name = $data_item['name'];
			$taxonomy = get_taxonomy( $taxonomy_name ); // Get the taxonomy object
			$terms = get_terms($taxonomy_name, array( 'orderby' => $data_item['orderby'], 'order' => strtoupper($data_item['sort']) ));

			if( $data_item['hidden'] ):
				echo "<input type='hidden' name='$taxonomy_name' value='" . $data_item['value'] . "' />";
					
			elseif( taxonomy_picker_all_text($tax_label) <> 'N/A' ): 
				
				echo "<li $css_class><label>$tax_label</label><br/><select name='$taxonomy_name' style='width:100%;'>"; 
				
				echo "<option value='$taxonomy_name=all'>". taxonomy_picker_all_text($tax_label) ."</option>";
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
						$selected = ($data_item['value'] == ($taxonomy_name . '=' . $term->slug) ) ? 'selected="selected"' : '';
					else:
						$selected = ($tpicker_inputs[$taxonomy_name] == $term->slug) ? 'selected="selected"' : '';
					endif;
					
					
					if($tpicker_options['show-count'] and $allowed): 
						$post_count = taxonomy_picker_count_posts($taxonomy_name, $term->name);
						if($post_count):
							echo "<option value='$option_name' $selected>$t_name ({$post_count})</option>";
						endif;
					elseif($allowed):
						 echo "<option value='$option_name' $selected>$t_name</option>";
					endif;
				endforeach;
	
				echo "</select></li>";
				
			endif; // Hidden?
			
		
		endforeach;
		unset($taxies);
		
		echo "<input type='hidden' name='set_categories' value='$set_categories' />";
		echo "<input type='hidden' name='kate-phizackerley' value='taxonomy-picker' />";
		echo '<li style="height:8px;" class="last"></li></ul><p style="text-align:center;margin:0 auto;">';
		
		if($tpicker_options['remember']):
			// echo "<p onclick='document.getElementById(\"taxonomy-picker\").reset()';>Clear</p>";  // Sort out in v1.9
		else:
			echo '<input type="reset" value="Reset" style="margin-right:10%;" />';
		endif;
				
		echo "<input type='submit' value='$search_text' /></p></form>";
		echo "<script language='javascript' type='text/javascript'>function removeSpaces(string){return string.split(' ').join('%20');}</script>";
		echo "<script language='javascript' type='text/javascript'>function restoreSpaces(string){return string.split('%20').join(' ');}</script>";
		echo $after_widget;	
	}

	/**
	 * Updates the $instance of the widget in the database on Save
	 *
	 * @param $new_instance		array	New instance proposed for save
	 * @param $old_instance		array	Old version of the instance
	 *
	 * return array 	Cleansed $instance with pre-processed taxonomies field added to save processing when displayed
	 */
	function update($new_instance, $old_instance) {
	
		// Tidy up inputs
		$instance = $new_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category_title'] = strip_tags( $new_instance['category_title'] );
		$instance['set_categories'] = str_replace(' ','', strip_tags( $new_instance['set_categories'] ) );
		$instance['set_pages'] = strip_tags( $new_instance['set_pages'] );
		return taxonomy_picker_taxonomies_array( $instance ); // Pre-process the instance for efficiency
		
	}
	
	
	
	function form ($instance) {
	
		// Set up some defaults
		$defaults = array( 'title' => __('Example', 'example'), 'choose_categories' => 'A', 'choose_pages' => 'A');
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		// Widget title
	    $title_id = $this->get_field_id( 'title' );
	    $title_name = $this->get_field_name( 'title' );
	    $title_value = $instance['title'];
		//Show search?
	 	$search_id = $this->get_field_id( 'hidesearch' );
	    $search_name = $this->get_field_name( 'hidesearch' );
		$radio_checked = ($instance['hidesearch']=='on') ? 'checked ' : '';
	    
	    ?><p><fieldset id="taxonomy-picker-title">
	    	<label for='<?php echo $title_id;  ?>' style="float:left;">Title:&nbsp;</label>
	    	<input id='<?php echo $title_id;  ?>' name='<?php echo $title_name;  ?>' value='<?php echo $title_value;  ?>' style='width:60%;' /> 
	    <?php

		echo "<table><tbody><tr><td><input id='$earch_id' class='checkbox' type='checkbox' name='$search_name' $radio_checked />";
		echo "&nbsp;<label for='$search_id' title='showsearch'><span  style='font-size:85%;'>Hide text search?</span></label></td></tr>";
		echo "</tbody></table></fieldset></p><hr>";


	 	unset($title_id, $title_name, $title_value,$search_id,$search_value);

		// Build taxonomy selection boxes	 	
		$taxes = get_taxonomies('','names');
		if(count($taxes)>0): ?>
			
			<fieldset id="taxonomy-picker-taxonomoies"><h3>Taxonomies</h3><div style="width:240px;overflow-x:scroll;">
			<table  style="width:400px;"><thead><tr>
				<td><strong>Taxonomy</strong></td>
				<td><strong>Fix/Initial</strong></td>
				<td><strong>Order By</strong></td>
				<td><strong>Sort</strong></td>
			</tr></thead><tbody><?php
			
				foreach($taxes as $tax):
					if( ($tax=='link_category') or ($tax=='nav_menu') or ($tax=='post_format') ) continue;
					$tax_stem = 'taxonomy_'.$tax;
					$taxonomy = get_taxonomy($tax);
					$tax_id = $this->get_field_id($tax_stem);
					$tax_name = $this->get_field_name($tax_stem);
					$radio_checked = ($instance[$tax_stem]=='on') ? 'checked ' : '';
					
					if($tax <> 'category'): // Custom taxonomy - build fix/initial value combobox
						$terms = get_terms($taxonomy->name, array('orderby'=>'name'));

						$select_name = $this->get_field_name("fix_".$tax);
						$tax_select  = "<select name='$select_name' style='width:90%;font-size:85%;'>";
						$tax_select .= "<option value='$taxonomy->name=all'>".taxonomy_picker_all_text($tax_label)."</option>";
						foreach($terms as $term): // Loop through the terms to build the options
							$option_name = $taxonomy->name.'='.$term->slug;
							$selected = ($instance['fix_'.$tax] == $option_name) ? 'selected="selected"' : '';
							$tax_select .= "<option value='$option_name' $selected>$term->name</option>";
						endforeach;
						$tax_select .= "</select>";
						
						// Orderby comboboxes
						$select_name = $this->get_field_name("orderby_".$tax);
						$order_select  = "<select name='$select_name' style='width:90%;font-size:90%;'>";
						foreach( array('name','slug','id','count') as $term):
							$selected = ($instance['orderby_'.$tax] == $term) ? 'selected="selected"' : '';
							$order_select .= "<option value='$term' $selected>" . ucwords( ($term=='name') ? 'Label' : $term  ) . "</option>";
						endforeach;

						// Sort order comboboxes
						$select_name = $this->get_field_name("sort_".$tax);
						$sort_select  = "<select name='$select_name' style='width:90%;font-size:90%;'>";
						foreach( array('Asc','Desc') as $term):
							$selected = ($instance['sort_'.$tax] == $term) ? 'selected="selected"' : '';
							$sort_select .= "<option value='$term' $selected>$term</option>";
						endforeach;
						
					endif;
					
					echo "<tr><td><input id='$tax_id' class='checkbox' type='checkbox' name='$tax_name' $radio_checked />";
					echo "&nbsp;<label for='$tax_id' title='$tax_stem'><span  style='font-size:85%;'>$taxonomy->label</span></label></td>";
					echo "<td>$tax_select</td><td>$order_select</td><td>$sort_select</td></tr>";
				endforeach;
			echo '</tbody></table><i style="font-size:75%">If on, the value is the initial one; if off, value is fixed to restrict search</i></div></fieldset><hr>';
		endif;
		
		// Select Categories		
		$title_id = $this->get_field_id( 'category_title' );
	    $title_name = $this->get_field_name('category_title');
	    $title_value = $instance['category_title'];

		echo '<fieldset id="taxonomy-picker-categories"<p><h3>Categories</h3></p>';
			echo '<p style="float:left;"><label for="$cat_title_id"><b>Title:</b></label></p>';
			echo '<p style="float:right;width:75%;">';
				echo "<input id='$title_id' name='$title_name' value='$title_value' style='width:90%;' />";
			echo '</p>';
			echo '<br style="clear:both;"/><label><b>Select:&nbsp;&nbsp;</b></label>';

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
			echo "<input id='$input_id' name='$input_name'  value='$input_value' style='width:100%;margin-top:2px;'/>";
			echo '<i style="font-size:75%">Enter category IDs separated by commas</i>';
		echo '</fieldset><hr>';

		echo '<fieldset id="taxonomy-picker-pages">';
			echo '<p><h3>Pages</h3></p><label><b>Select:&nbsp;&nbsp;</b></label>';
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
			echo '<i style="font-size:75%">Enter page IDs separated by commas</i>';
		echo '</fieldset>';
		
	}
	
} // End class
?>