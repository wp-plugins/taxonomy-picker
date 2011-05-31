<?php

// Standard module to initialise the Kandie Menu on the dashboard of nothing else has done it.  Copy to all plugins

// Version: 2.2

add_action( 'admin_menu', 'kandie_girls_top_menu', 10);  // Add Kandie admin menu support
add_action( 'admin_init', 'kandie_girls_admin_menu_init' );  // Initialise Kandie admin menu

/* Add Kandie Girls top menu */
function kandie_girls_top_menu() {
	$page = add_menu_page('Kandie from Kate Phizackerley', 'Kandie Girls', 'administrator',  basename(__FILE__), 'Kandie_create_admin_menu'  );
	add_action( 'admin_print_styles-' . $page, 'kandie_girls_admin_styles' );
}

/* Register our stylesheet. */
function kandie_girls_admin_menu_init() { 
	
	// Find the best Kandie admin stylesheet (highest version)
	$kandie_admin_stylesheet_path = kandie_include_best_library('kandie-admin-style.css'); 
	$kandie_admin_stylesheet_url  = kandie_include_best_library('kandie-admin-style.css','url');
	if( $kandie_admin_stylesheet_path == '' ) kandie_debug_log('Failed to locate kandie-admin-style.css<b/>');  // In debug mode, report failure, otherwise silent

	// Register it, adding in filedate as a modifier
	$last_modified = date( 'ymdHi', filemtime( $kandie_admin_stylesheet_path ) );
	wp_register_style( 'kandie_girls_admin_stylesheet', $kandie_admin_stylesheet_url, false, $last_modified);
}
/* Enque stylesheet */
function kandie_girls_admin_styles() { // It will be called only on your plugin admin page, enqueue our stylesheet here
	wp_enqueue_style( 'kandie_girls_admin_stylesheet');
}

function kandie_create_admin_menu(){
	
	$kandie_plugins = get_kandie_plugins();
	$plugin_data = get_plugin_data( plugin_dir_path(__FILE__) . '../readme.txt' );  // Path to this plugins readme 
	$plugin_name = $plugin_data['Name'];
	if(!$plugin_name) $plugin_name =  'Kandie Girls'; // If not a plugin, then it is the Kandie Girls theme!
	
	?>
	<h3>Kandie Girls Packages</h3>
	<p>This sub-menu is used for packages (plugins and themes) authored by Kate Phizackerley under the Kandie Girls brand.</p>
	<table id="kandie-inventory" class="widefat">
	<thead>
		<tr>
			<td>Name</td>
			<td>Type</td>
			<td>Installed?</td>
			<td>Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><a href="http://wordpress.org/extend/plugins/taxonomy-picker/" title="Taxonomy Picker">Taxonomy Picker</a></td>
			<td>Plugin</td>
			<td>
				<?php 
				$item_installed = function_exists('kandie_tpicker_menu_initialisation') ? 'Yes' : 'No';
				_e($item_installed); 
				if($item_installed == 'Yes') echo ' (v' . $kandie_plugins['Taxonomy Picker']['Version'] . ')';
				?>
			</td>
			<td>Interactive search builder widget, across multiple custom taxonomies</td>
		</tr>
		<?php  
		if( defined('KANDIE_THEME_DIR') ):
			echo "<tr><td>Kandie Girls</td><td>Theme</td><td>Yes (v". kandie_versioneer( trailingslashit( KANDIE_THEME_DIR) . 'style.css' ); 
			echo ")</td><td>Kandie Girls theme developed for Egyptological</td></tr>";
		endif; 
		?>
	</tbody>
	</table><br style="clear:both;">
	Packages &copy;Kate Phizackerley 2009 - 2011.  Please refer to each package for license details. <br/>
	
	<?php 
	
	echo "<br/><h3>Kandie Library Versions and Paths</h3><style type='text/css'>.widefat thead td {font-weight:bold;font-size;120%;}</style>";
	echo "<table class='widefat'><thead><b><tr><td>Library Item</td><td>Path</td><td>Version</td><td>Date</td></tr></b></thead><tbody>";
	$lib_contents = kandie_admin_library_versions();
	foreach($lib_contents as $item => $path):
		$tidy_path = dirname( str_replace( $_SERVER['DOCUMENT_ROOT'], '', $path ) ); // Strip out the leading stuff and the item name
		echo "<tr><td>$item</td><td>$tidy_path</td><td>" . kandie_versioneer($path)."</td><td>".date( 'd/m/Y', filemtime( $path ) )."</td></tr>";
	endforeach;
	echo "</tbody></table><br/><p>(Printed by $plugin_name using PHP v" . phpversion() .")</p>";
}

/**
 * Return list of the best version of all files in the Kandie Admin library, with path
 * 
 * @return array key => string, DIR of file 	data => version
 */

function kandie_admin_library_versions() {
	$lib = trailingslashit( dirname(__FILE__) );
	foreach (new DirectoryIterator($lib) as $file):
   		if ( (!$file->isDot()) && ($file->getFilename() != basename($_SERVER['PHP_SELF'])) ):
      		if( !($file->isDir()) and ( ($fname = $file->getFilename()) != 'bare.php' ) ):
      			$library_versions[$fname] = kandie_include_best_library($fname, 'dir');
      		endif;
      	endif;
    endforeach;
	return $library_versions;
}

/**
 * Settings API support function - display a checkbox
 * 
 * @param 	$option_name	string	Name of option in the database
 * @param	$item_name		string	Name of the item within the $option array
 */

function kandie_admin_checkbox($option_name, $item_name) {
	$options = get_option( $option_name );	
	if($options[$item_name]) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='$item_name' name='{$option_name}[{$item_name}]' type='checkbox' />";
}

/**
 * Settings API support function - display a combobox
 * 
 * @param 	$option_name	string		Name of option in the database
 * @param	$item_name		string		Name of the item within the $option array
 * @param	$valid			mixed		Array of strings of the valid items, or a comma separated list of items to arrayify
 */

function kandie_admin_combobox($option_name, $item_name, $valid) {
	$options = get_option( $option_name );	//Read in the options		
	echo "<select id='$item_name' name='{$option_name}[{$item_name}]'/>";
	if( is_string($valid) ) $valid = explode(',',$valid); // Arrayify
	foreach($valid as $item):
		$selected = ($options[$item_name]==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	endforeach;
	echo "</select>";
}


?>