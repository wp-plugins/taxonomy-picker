<?php

// Version: 1.11.5
// Builds the Premium TPicker widget

add_action('widgets_init','register_phiz_find_helper');

function register_phiz_find_helper() {
	register_widget('FindHelperWidget');
}

class FindHelperWidget extends WP_Widget {

	function FindHelperWidget(){
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'taxonomy-picker', 'description' => __('Presents taxonomies as drop-downs so reader can pick query', 'taxonomy_picker') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 525,  'id_base' => 'taxonomy_picker' );

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

		echo taxonomy_picker_display_widget( $instance, $args ); // Display the widget
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
	
	function form ($instance) { // Form for configuring the widget instance
	
		// Set up some defaults
		$defaults = array( 'title' => __('Example', 'example'), 'choose_categories' => 'A', 'choose_pages' => 'A');
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		$options = get_option('taxonomy-picker-options');

		// Widget title
	   $title_id = $this->get_field_id( 'title' );
	   $title_name = $this->get_field_name( 'title' );
		$title_value = $instance['title'];
		
		//Show search?
	 	$search_id = $this->get_field_id( 'hidesearch' );
	   $search_name = $this->get_field_name( 'hidesearch' );
		$radio_checked = ($instance['hidesearch']=='on') ? 'checked ' : '';
	    
	    ?><p><fieldset id="taxonomy-picker-title">
	    	<table><tbody><tr><td><label for='<?php echo $title_id;  ?>' style="float:left;">Title:&nbsp;</label>
	    	<input id='<?php echo $title_id;  ?>' name='<?php echo $title_name;  ?>' value='<?php echo $title_value;  ?>' style='width:60%;' /></td> 


	   <?php
		echo "<td><input id='$search_id' class='checkbox' type='checkbox' name='$search_name' $radio_checked />";
		echo "&nbsp;<label for='$search_id' title='showsearch'><span  style='font-size:85%;'>Hide text search?</span></label></td></tr></tbody></table></fieldset></p><hr>";


		/*********** Premium options *********/
		
		// Radio buttons alternative
		?><p><fieldset id="tpicker_premium"><h3>Premium Options</h3><table><tbody><tr><td><?php

		// Date Match option
		echo "Match date:</td><td><select name='" . $this->get_field_name('date_match') . "'>";
		foreach( array('N/A','Y','YM','YMD','M','D') as $item ):
			$selected = ( $instance['date_match'] == $item ) ? 'selected=selected' : '' ;
			echo "<option value='$item' $selected>{$item}</option>";
		endforeach;
		?></select></td><?php

		
		// Code for sort results options
		$selected = ( $instance['results_orderby'] == '_default' ) ? 'selected=selected' : '' ;
		echo "<td>Sort results by:</td><td><select name='" . $this->get_field_name('results_orderby') . "'>
					<option value='_default' $selected>** Default **</option>";
		$user_choice = false;
		foreach( array('author', 'comment_count', 'date','ID', 'modified', 'title') as $item):
			if( $options["results_sort_$item"] ):
				$item_text = str_replace( '_', ' ', ucfirst( $item) );
				$selected = ( $instance['results_orderby'] == $item ) ? 'selected=selected' : '' ;
				echo "<option value='$item' $selected>$item_text</option>";
				$user_choice = true; // We have at least one option to allow a user choice
			endif;
		endforeach;
	
		if( $user_choice ): // There are enough options for user_choice to be sensible
			$selected = ( $instance['results_orderby'] == '_choice' ) ? 'selected=selected' : '' ;
			echo "<option value='_choice' $selected>** Visitors' Choice **</option>";
		endif;
			 
		echo "</select></td><td><select name='" . $this->get_field_name('results_order') . "'>
						<option value='ASC'>Ascending</option><option value='DSC'>Descending</option></select></td></tr>";

		echo "<tr><td>Combobox type:</td><td><select name='" . $this->get_field_name('combo') . "'>";
		foreach( array('flat','radio') as $combo ):
			$selected = ( $instance['combo'] == $combo ) ? 'selected=selected' : '' ;
			echo "<option value='$combo' $selected>" .  ucwords($combo) . "</option>";
		endforeach;
		?></select></td><?php
		
		?></tr></tbody></table></fieldset></p><hr><?php

	 	unset($title_id, $title_name, $title_value,$search_id,$search_value,$combo, $selected,$item,$item_text,$combo,$user_choice);
	 	
	 	/************ Taxonomies Section ************/

		// Build taxonomy selection boxes	 	
		$taxes = get_taxonomies('','names');
		if( isset($options['post_type'] ) ) $taxes[] = "post_type"; // Option to add post_type
		if(count($taxes)>0): 
			
			$priority_name = ( isset($options['sort-priority'] ) ) ? '<td><strong>Priority</strong></td>' : '';

			?>
			<fieldset id="taxonomy-picker-taxonomoies"><h3>Taxonomies</h3><div>
			<table  style="width:400px;"><thead><tr>
				<td><strong>Taxonomy</strong></td>
				<td><strong>Fix/Initial</strong></td>
				<td><strong>Order By</strong></td>
				<td><strong>Sort</strong>
				</td><?php echo $priority_name; ?>
			</tr></thead><tbody><?php
			
			foreach($taxes as $tax):
				if( ($tax=='link_category') or ($tax=='nav_menu') or ( ($tax=='post_format') and !isset($options['post_format']) ) ) continue;
				$tax_stem = 'taxonomy_'.$tax;
				$taxonomy = get_taxonomy($tax);
				$tax_id = $this->get_field_id($tax_stem);
				$tax_name = $this->get_field_name($tax_stem);
				$radio_checked = ($instance[$tax_stem]=='on') ? 'checked ' : '';
				
				if($tax <> 'category'): // Custom taxonomy - build fix/initial value combobox
					$terms = get_terms($taxonomy->name, array('orderby'=>'name'));

					$select_name = $this->get_field_name("fix_".$tax);
					$tax_select  = "<select name='$select_name' style='width:90%;font-size:85%;'>";
					$tax_select .= "<option value='$taxonomy->name=tp-all'>".taxonomy_picker_all_text($tax_label)."</option>";
					foreach($terms as $term): // Loop through the terms to build the options
						$option_name = $taxonomy->name.'='.$term->slug;
						$selected = ($instance['fix_'.$tax] == $option_name) ? 'selected="selected"' : '';
						$tax_select .= "<option value='$option_name' $selected>$term->name</option>";
					endforeach;
					$tax_select .= "</select>";
					
					// Orderby comboboxes
					$select_name = $this->get_field_name("orderby_".$tax);
					$order_select  = "<select name='$select_name' style='width:90%;font-size:90%;'>";
										
					$orders =array('name','slug','id','count','tree');  
					if( $options['beta-widget'] ) $orders[]='pruned_tree';
					foreach( $orders as $order):
						$selected = ($instance['orderby_'.$tax] == $order) ? 'selected="selected"' : '';
						$select_label = ($order=='name') ? 'Label' : ucwords( str_replace('_',' ',$order) );
						$order_select .= "<option value='$order' $selected>$select_label</option>";
					endforeach;
					unset($orders, $order);

					// Sort order comboboxes
					$select_name = $this->get_field_name("sort_".$tax);
					$sort_select  = "<select name='$select_name' style='width:90%;font-size:90%;'>";
					foreach( array('Asc','Desc') as $term):
						$selected = ($instance['sort_'.$tax] == $term) ? 'selected="selected"' : '';
						$sort_select .= "<option value='$term' $selected>$term</option>";
					endforeach;

					// Priority inputs
					if( isset($options['sort-priority'] ) ): // Only show priority field if required
						$select_name = $this->get_field_name("priority_".$tax);
						$priority_input  = "<td><input name='$select_name' style='width:60px;font-size:90%;'></td>";
					else:
						$priority_input = '';
					endif;						
				endif;
				
				echo "<tr><td><input id='$tax_id' class='checkbox' type='checkbox' name='$tax_name' $radio_checked />";
				echo "&nbsp;<label for='$tax_id' title='$tax_stem'><span  style='font-size:85%;'>$taxonomy->label</span></label></td>";
				echo "<td>$tax_select</td><td>$order_select</td><td>$sort_select</td>$priority_input</tr>";
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