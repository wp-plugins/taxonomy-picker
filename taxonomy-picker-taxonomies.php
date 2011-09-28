<?php

//Version: 1.1

$taxonomy_picker_prepacks = explode( "," , "collection,colour,licence,nature,number,product,sex,shape,size,style,what,where,when,who,writer"); // Our prepack taxonomies

add_action( 'init', 'taxonomy_picker_create_taxonomies', 0 );
add_action( 'admin_init', 'taxonomy_picker_taxonomies_init', 20 ); 
add_action( 'admin_init', 'taxonomy_picker_admin_init', 21 ); 
add_action( 'admin_menu', 'taxonomy_picker_taxonomies_menu_initialisation', 30); // Kandie Menu added as 10, Main Taxomomy Picker as 20



/* Taxonomies
***************/
function taxonomy_picker_create_taxonomies() {
	global $taxonomy_picker_prepacks;
	
	$taxonomy_options = get_option('taxonomy-picker-taxonomies');
	
	foreach( $taxonomy_picker_prepacks as $tax):
		
		if( array_key_exists("tax:$tax-show", $taxonomy_options) ):
		
			$label = $taxonomy_options["tax:$tax-label"];
			if( !$label) $label = ucwords($tax) . ( ( $tax[0] == 'w' ) ? '?' : '' );
			if( isset($taxonomy_options["tax:$tax-hier"]) ) $hierarchical = ($taxonomy_options["tax:$tax-hier"])? true : false; else $hierarchical = false;
			$args = array( 'hierarchical' => $hierarchical , 'label' => $label, 'query_var' => true, 'rewrite' => true );
			register_taxonomy( $tax, 'post', $args );
			
		endif;
		
	endforeach;
}


/** Taxonomy Picker Admin Menu  **
**********************************/

// Adds the Taxonomy Picker admin menu in the Kandie section
function taxonomy_picker_taxonomies_menu_initialisation() {
	$page = add_submenu_page( 'kandie-admin-menu.php', 'Prepack Taxonomies', 'Prepack Taxonomies', 'administrator', __FILE__, 'taxonomy_picker_create_taxonomies_menu' );
	add_action( 'admin_print_styles-' . $page, 'kandie_girls_admin_styles' ); // Add our admin style sheet

	$help_text = '<div class="kandie-help-text">';
		
	// Help text for the main options
	$help_text .= "Specify any additional custom taxonomies you wish to use.  These are entirely option.  Taxonomy Picker works with any custom taxonmies you create yourself and these are just provided as a convenience for those who don't wish to write the code themeselves.";
		
	$help_text .= "<br/></div>";
	
	// Auto open the Help Text
	$options = get_option('taxonomy-picker-options');
	if( isset($options['auto-help']) ) {$help_text .= kandie_auto_open_help();}

	add_contextual_help( $page , $help_text );
}


function taxonomy_picker_taxonomies_init() {

	$tp = 'taxonomy-picker-taxonomies'; // just a convenient shorthand 
	register_setting( $tp, $tp,'taxonomy_picker_taxonomies_validate'); // Register settings
	
	global $taxonomy_picker_prepacks;

	foreach($taxonomy_picker_prepacks as $tax):
		
		add_settings_section( "$tp-$tax", ucfirst($tax) . " (prepack)", 'tpicker_nothing', __FILE__);		
		add_settings_field( "tax:$tax-show", "Enable", 
				create_function('',"kandie_admin_checkbox('$tp','tax:$tax-show');"), __FILE__, "$tp-$tax");
		add_settings_field( "tax:$tax-hier", "Hierarchical?", 
				create_function('',"kandie_admin_checkbox('$tp','tax:$tax-hier');"), __FILE__, "$tp-$tax");
		add_settings_field("tax:$tax-label","Label (default = " . ucwords($tax) . ( ( $tax[0] == 'w' ) ? '?' : '' ) . ")" , 
				create_function('', "kandie_admin_textbox( 'taxonomy-picker-taxonomies', 'tax:$tax-label', 20);"), __FILE__, "$tp-$tax");				
	endforeach;

}

function taxonomy_picker_taxonomies_validate($input) {
	global $taxonomy_picker_prepacks;
	
	$newinput = $input;
	foreach( $taxonomy_picker_prepacks as $tax) {$newinput["tax:$tax-label"] = strip_tags( $newinput["tax:$tax-label"] );}
	
	return $newinput;
}

function taxonomy_picker_create_taxonomies_menu(){
	global $taxonomy_picker_prepacks;

	$kandie_plugins = get_kandie_plugins();
	$taxonomy_plugin = $kandie_plugins['Taxonomy Picker']; // The readme.txt details for Taxonomy Picker
	$tp = 'taxonomy-picker-taxonomies'; // just a convenient shorthand 
	
	?>
	<style type="text/css">#prepacks tr {float:left;height:36px;border-bottom: 3px solid silver} # prepacks * {padding:0} #prepacks h3 {margin-bottom:0}</style>
	<div class="wrap">
	
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Taxonomy Picker from Kandie Girls (v<?php echo $taxonomy_plugin['Version']; ?>) - Pre-pack Taxonomies</h2>
		<form action="options.php" method="post">
		
			<?php  settings_fields( $tp ); ?>

			<div id='prepacks'>

				<?php do_settings_sections(__FILE__); ?>		
			
			</div>
			
		<p>&nbsp;</p><input name="Submit" type="submit" value="Save Changes" />
		</form>
		<p>After making any changes you <strong style="color:red;">MUST</strong> go to Settings&rArr;Permalinks and Save Changes</p>
		<p><strong>&copy; Kate Phizackerley, 2010,2011</strong></p>
	</div> <!-- Wrap -->
	<?php
}


?>