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

	$kandie_plugins = get_kandie_plugins();
	$taxonomy_plugin = $kandie_plugins['Taxonomy Picker']; // The readme.txt details for Taxonomy Picker
	$plugin_home = $taxonomy_plugin["PluginURI"];	
	$options = get_option('taxonomy-picker-options');


	// Add donate button
	$help_text = '<div class="kandie-help-text"><form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;width:120px;"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="GA7SPX4C9S64Q"><input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online."><img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1"></form>';

	// Standard plugin text
	$help_text .= "<p>" .  __($taxonomy_plugin['Description']) . "<p></p><a href='$plugin_home'>Plugin Home (support and documentation)</a></p><br />";
		
	// Help text for the main options
	$help_text .= "<dl><dt>Text for 'all' option<dt>";
	$help_text .= "<dd>Chose the text for all items.  {name} expands to the name of the taxonomy and {name}s replaces ys with ies at the end</dd>";

	$help_text .= "<dl><dt>Override for 'all' option<dt>";
	$help_text .= "<dd>Generally leave blank but you may optionally enter your own text for the ** all ** option.  This will be used if set.  Use the text you want.  Identify the name of the taxonomy by using {name} or {name}s at the END of the text</dd>";


	$help_text .= "<dl><dt>Show item count<dt>";
	$help_text .= "<dd>Turn on if you wish to show item counts in the drop downs.  These are the total count and don't update on drill down.</dd>";
	
 	$help_text .= "<dl><dt>Remember the User Count<dt>";
	$help_text .= "<dd>If turned in then the widget will be pre-populated with the query just run.  To keep the setup stateless, this is passed in the URL and will be lost as soon as the user visits another page than the immediate results page.</dd>";
	
	$help_text .= "<dl><dt>Redirect to URL on null search<dt>";
	$help_text .= "<dd>If the user selects ** All ** (or equivalent) for all taxonomies, output will be redirected to this URL.  Point it to the page which shows all content or your front page etc.  Enter the full URL e.g. http://www.glyphs.info</dd>";
	
	$help_text .= "</dl><br/></div>";
	
	// Auto open the Help Text
	if($options['auto-help'] == 'on' ) $help_text .= kandie_auto_open_help();

	add_contextual_help( $page , $help_text );
}

// Register and define settings
function taxonomy_picker_admin_init() {

	register_setting( 'taxonomy-picker-options', 'taxonomy-picker-options','taxonomy_picker_options_validate'); // Register settings
	
	add_settings_section( "tpicker-processing", 'Query Processing', 'tpicker_nothing', "tpicker-processing-sec");
				
	$fn_txt = "kandie_admin_combobox('taxonomy-picker-options','all-format',array('** All **','All Items','Everything','Every {name}','All {name}', 'All {name}s', 'N/A'));";
	str_replace($fn_txt,array('All','Items','Every','Everything'), array(__('All'),__('Items'),__('Every'),__('Everything')) );			
	add_settings_field( "all-format", 'Text for \'all\' option', create_function('',$fn_txt),	"tpicker-processing-sec","tpicker-processing");

	add_settings_field('all-override','Override text for ** all ** (optional)', 'taxonomy_picker_tpfn2', "tpicker-processing-sec","tpicker-processing");				

	add_settings_field( "show-count", 'Show item count', 
				create_function('',"kandie_admin_checkbox('taxonomy-picker-options','show-count');"), "tpicker-processing-sec", "tpicker-processing");

	add_settings_field( "remember", 'Remember the user query?', 
				create_function('',"kandie_admin_checkbox('taxonomy-picker-options','remember');"), "tpicker-processing-sec", "tpicker-processing");

	add_settings_field('miss-url','Redirect to URL on null search', 'taxonomy_picker_tpfn', "tpicker-processing-sec","tpicker-processing");				


	add_settings_section( "tpicker-housekeeping", 'Housekeeping Options', 'tpicker_nothing', "tpicker-housekeeping-sec" );

	add_settings_field( "remember", 'Auto show help?', 
				create_function('',"kandie_admin_checkbox('taxonomy-picker-options','auto-help');"), "tpicker-housekeeping-sec", "tpicker-housekeeping");

}

function tpicker_nothing(){
	// Nothing to do!
} 

function taxonomy_picker_tpfn() {kandie_admin_textbox( 'taxonomy-picker-options', 'miss-url', 40); }
function taxonomy_picker_tpfn2() {kandie_admin_textbox( 'taxonomy-picker-options', 'all-override', 20); }

function taxonomy_picker_options_validate($input) {
	
	$newinput = $input;
	
	
	$newinput['miss-url'] = esc_url( $input['miss-url'] ); // Sanitize URL
	$newinput['all-override'] = strip_tags( $input['all-override'] ); // Sanitize URL

	// Save the current version of the plugin in our options so that we can test for updates
	$kandie_plugins = get_kandie_plugins();
	$taxonomy_plugin = $kandie_plugins['Taxonomy Picker'];
	$newinput['version'] = $taxonomy_plugin['Version'];
	
	return $newinput;
}



function kandie_create_tpicker_menu(){
	$kandie_plugins = get_kandie_plugins();
	$taxonomy_plugin = $kandie_plugins['Taxonomy Picker']; // The readme.txt details for Taxonomy Picker
	$tp = 'taxonomy-picker'; // just a convenient shorthand 
	
	?>
	<div class="wrap">
	
		<?php $options = get_option('taxonomy-picker-options'); ?>
		<?php var_dump($options); ?>

	
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Taxonomy Picker from Kandie Girls (v<?php echo $taxonomy_plugin['Version']; ?>)</h2>
		<form action="options.php" method="post"><table><tbody><tr>
		
			<?php  settings_fields( "$tp-options" ); ?>
 			
 			<td style="vertical-align:top;"><?php do_settings_sections("tpicker-processing-sec"); ?></td>
 			
 			<!-- Defer housekeeping sections to v1.6 -->
 			<td style="vertical-align:top;"> <?php do_settings_sections("tpicker-housekeeping-sec"); ?></td>
					
 		</tr></tbody></table><p>&nbsp;</p>
		<input name="Submit" type="submit" value="Save Changes" />
		</form>
		<p><strong>&copy; Kate Phizackerley, 2011</strong></p>
	</div> <!-- Wrap -->
	<?php
}

?>