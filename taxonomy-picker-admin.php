<?php

// PHP for the Taxonomy Picker Admin menu

add_action( 'admin_menu', 'kandie_tpicker_menu_initialisation', 20); // Kandie Menu added as 10
add_action( 'admin_init', 'taxonomy_picker_admin_init', 20 ); 
add_filter('plugin_action_links', 'taxonomy_picker_plugin_action_links', 10, 2);  // Filter to add action settings

/** Taxonomy Action Settings  **
**********************************/

function taxonomy_picker_plugin_action_links($links, $file) { // Add 'Settings" to action links

    static $this_plugin;
    if( !isset($this_plugin) ) $this_plugin = 'taxonomy-picker/taxonomy-picker.php';

    if ($file == $this_plugin):
        $settings_link = '<a href="' . admin_url() . 'admin.php?page=' . basename(dirname(__FILE__)) . '/'. basename(__FILE__) . '">Settings</a>';
        array_unshift($links, $settings_link);
    endif;
    return $links;
}


/** Taxonomy Picker Admin Menu  **
**********************************/

// Adds the Taxonomy Picker admin menu in the Kandie section
function kandie_tpicker_menu_initialisation() {
	$page = add_submenu_page( 'kandie-admin-menu.php', 'Taxonomy Picker', 'Taxonomy Picker', 'administrator', __FILE__, 'Kandie_create_tpicker_menu'  );
	add_action( 'admin_print_styles-' . $page, 'kandie_girls_admin_styles' ); // Add our admin style sheet
	taxonomy_picker_default(); // ** TEMPORARY MEASURE TO FORCE DEFAULTS IF SOMEBODY HAS UPGRADED **
}

// Register and define settings
function taxonomy_picker_admin_init() {

	register_setting( 'taxonomy-picker-options', 'taxonomy-picker-options','taxonomy_picker_options_validate'); // Register settings
	
	add_settings_section( "tpicker-processing", 'Query Processing', 'tpicker_nothing', "tpicker-processing-sec");
				
	$fn_txt = "kandie_admin_combobox('taxonomy-picker-options','all-format',array('** All **','All Items','Everything','Every {name}','All {name}', 'All {name}s'));";
	str_replace($fn_txt,array('All','Items','Every','Everything'), array(__('All'),__('Items'),__('Every'),__('Everything')) );			
	add_settings_field( "all-format", 'Text for \'all\' option', create_function('',$fn_txt),	"tpicker-processing-sec","tpicker-processing");

	add_settings_field( "show-count", 'Show item count', 
				create_function('',"kandie_admin_checkbox('taxonomy-picker-options','show-count');"), "tpicker-processing-sec", "tpicker-processing");

	add_settings_field( "remember", 'Remember the user query?', 
				create_function('',"kandie_admin_checkbox('taxonomy-picker-options','remember');"), "tpicker-processing-sec", "tpicker-processing");

	add_settings_field('miss-url','Redirect to URL if no match', 'tpfn', "tpicker-processing-sec","tpicker-processing");				


	add_settings_section( "tpicker-housekeeping", 'Housekeeping Options', 'tpicker_nothing', "tpicker-housekeeping-sec" );

				
}
function tpicker_nothing(){
	// Nothing to do!
} 

function tpfn() {
	$options = get_option('taxonomy-picker-options');
	echo "<input id='miss-url' name='taxonomy-picker-options[miss-url]' size='40' type='text' value='{$options['miss-url']}' />";
}

function taxonomy_picker_options_validate($input) {
	$newinput = $input;
	$newinput['miss-url'] = esc_url( $newinput['miss-url'] ); // Sanitize URL
	return $newinput;
}


function kandie_create_tpicker_menu(){
	$kandie_plugins = get_kandie_plugins();
	$taxonomy_plugin = $kandie_plugins['Taxonomy Picker']; // The readme.txt details for Taxonomy Picker
	$tp = 'taxonomy-picker'; // just a convenient shorthand 
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Taxonomy Picker from Kandie Girls (v<?php echo $taxonomy_plugin['Version']; ?>)</h2>
		<p><?php _e($taxonomy_plugin['Description']); ?></p>
		<form action="options.php" method="post"><table><tbody><tr>
		
			<?php  settings_fields( "$tp-options" ); ?>
 			
 			<td style="vertical-align:top;"><?php do_settings_sections("tpicker-processing-sec"); ?></td>
 			
 			<!-- Defer housekeeping sections to v1.6 -->
 			<td style="vertical-align:top;"> <?php // do_settings_sections("tpicker-housekeeping-sec"); ?></td>
					
 		</tr></tbody></table><p>&nbsp;</p>
		<input name="Submit" type="submit" value="Save Changes" />
		</form>
		<p><strong>&copy; Kate Phizackerley, 2011</strong></p>
	</div> <!-- Wrap -->
	<?php
	}

?>