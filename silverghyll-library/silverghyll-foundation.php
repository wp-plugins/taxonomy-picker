<?php

/* This is a foundation for all Silverghyll developments adding versioning, best-library and debugging support
// Version: 3.0


/***
 * Get / set Silverghyll debug status
 *
 * @param 1st  false or '' to turn off; 'echo' or 'true' to default echo stream debugging; 'log' for PHP error log ; simple for echo without extended error handling
 *							or 'trace-echo' or 'trace-log' which turns extended error handling on, plus trace reporting!
 *							or 'squawk' to display with silverghyll_debug_status() is subsequently set on!
 *
 * @return mixed	false as logical or debug stream as string
 */

require_once( 'silverghyll-transients.php'); // Add transients support

function silverghyll_debug_status() {
	if( !defined( KANDIE_THEME_DIR ) ) return; // Ensure it doesn't run onlive systems

	static $silverghyll_debug_status_saved = false;	
	
	if( func_num_args() > 0 ):
		if($silverghyll_debug_status_saved == 'squawk'):
			debug_print_backtrace(); // Use to find where debug_status is being set by setting to squawk' early on
		endif;
		$silverghyll_debug_status_saved = func_get_arg(0);
		
		// Restrict the valid options
		if( ($silverghyll_debug_status_saved != 'echo') 
			and ($silverghyll_debug_status_saved !='log') 
			and ($silverghyll_debug_status_saved !='simple') 
			and ($silverghyll_debug_status_saved !='squawk')
			and ($silverghyll_debug_status_saved !='trace-echo')
			and ($silverghyll_debug_status_saved !='trace-log')
			and ($silverghyll_debug_status_saved !== false) )
				 $silverghyll_debug_status_saved = 'echo' ; // Restrict to backtrace, false, echo or log as only permitted values, defaulting to echo (e.g default true)

		// Turn extended error handling on or off
		if( ($silverghyll_debug_status_saved == 'log') or ($silverghyll_debug_status_saved == 'echo') ):
			require_once( 'silverghyll-debug.php' ); // Load up the debug functions
			set_error_handler("silverghyll_error_handler");  // set_error_handler
		elseif( ($silverghyll_debug_status_saved == 'trace-echo')  or ($silverghyll_debug_status_saved == 'trace-log') ):
			require_once( 'silverghyll-debug.php' ); // Load up the debug functions
			set_error_handler("silverghyll_trace_handler");  // set_error_handler
			$silverghyll_debug_status_saved = substr( $silverghyll_debug_status_saved, 6);
		elseif( ($silverghyll_debug_status_saved == 'echo-trace')  or ($silverghyll_debug_status_saved == 'log-trace') ): 
			require_once( 'silverghyll-debug.php' ); // Load up the debug functions
			set_error_handler("silverghyll_trace_handler");  // set_error_handler
			$i = strpos( $silverghyll_debug_status_saved , '-' ); 
			$silverghyll_debug_status_saved = substr( $silverghyll_debug_status_saved, 0, $i);
		elseif( function_exists('silverghyll_unset_error_handler') and ($silverghyll_debug_status_saved === false) ):
			silverghyll_unset_error_handler(); // Turn off extended error handling
		endif;
					 
	endif;
	
	return $silverghyll_debug_status_saved;
}

/***
 * Prints a neat backtrace - no return
 *
 * @param $stream		String		'echo', 'log' or 'mixed' - as used by silverghyll_debug_log()
 * @param $drop 		Integer		Number of calls to drop (so error handler isn't shown)
 */

function silverghyll_backtrace( $stream = 'echo', $drop = 0) {

	if( !defined( KANDIE_THEME_DIR ) ) return; // Ensure it doesn't run onlive systems

	require_once( 'silverghyll-debug.php' ); // Load up the debug functions
	$styles = "<style type='text/css'>.trace-indent {margin-left:18px}</style>";
	$dropped = false;
	
		
	if( $stream == 'mixed' ) $stream = silverghyll_debug_status();
	if( $stream != 'echo' and $stream != 'log' ) $stream = 'echo'; // Default to echo 

	$backtrace = debug_backtrace();
	
	
	while($drop):
		array_shift($backtrace);
		$drop--;
		$dropped = true;
	endwhile;
	if($dropped):
		$trace = reset($backtrace); 
		$fn_args = "<strong style='color:green;'>Args:<br>$styles<p class='trace-indent'>" . implode("<br/>,", $trace) . "</p></strong>";
	endif;



	if( $stream == 'echo' ):
		echo $fn_args;
		echo "<table><tbody>";
		array_walk( $backtrace, "silverghyll_echo_backtrace" ); // Output the backtrace
		echo "</tbody></table><br/<";
	else:
		array_walk( $backtrace, "silverghyll_log_backtrace" ); // Log the backtrace	
	endif;
}

/***
 * Return the version stored in comments within the specified file
 *
 * $param	$filename	string	(optional) filename to read - defaults to last file read
 *
 * @return	string	version info (if available), blank otherwise
 */

function silverghyll_versioneer( $filename = '!last!', $comments_only = true ) {  // extracts the version 

	static $last_filename = '';
	static $file_vars = NULL;

	if( ($filename <> $last_filename) and ($filename <> '!last!') ):  // For efficiency, only read a file once if we are using it successively
		$file_vars = silverghyll_versioneer_read_vars($filename, $comments_only);
		$last_filename =$filename;
	endif;

	return $file_vars['version'];
}

// Read the vars in a file and return as an array of with the key based on the variable name in the comment
function silverghyll_versioneer_read_vars($filename, $comments_only = true){ 

	if(!file_exists($filename)):
		if( function_exists('silverghyll_debug_log') )
			if( function_exists('silverghyll_debug)_log') )
				silverghyll_debug_log("silverghyll_versioneer_read_vars cannot open $filename. \n\r" ); // Report failure to open file when in debug mode
		return; // Nothing to do
	endif;
			
	$lines = file($filename, FILE_SKIP_EMPTY_LINES);
	$comment_block = false;
	foreach($lines as $line):
	
		if( substr($line, 0, 8) == 'function' ) return $variables; // Assume no comments when we reach our first function
	
		$block_start = (strpos($line, '/*') !== FALSE);
		$comment_block = ( ($comment_block && !$block_end)  || $block_start);
		$block_end = (strpos($line, '*/') !== FALSE);

		if($comment_block or ( strpos($line, '//') !== false ) or !$comments_only ):
			if(preg_match('#(?P<var>[A-Za-z0-9\-_]+):\s*?(?P<value>[A-Za-z0-9\-_.]+)#',$line, $matches)):
				$variables[strtolower($matches['var'])] = $matches['value'];
			endif;
		endif;
	endforeach;
	return $variables;	
}
/***
 * Return path to latest version available or specified silverghyll file which must reside in silverghyll-library
 *
 * Uses the get_plugin_data to get details of all plugins and returns those attributed to Author = Kate Phizackerley
 * See http://phpdoc.wordpress.org/trunk/WordPress/Administration/_wp-admin---includes---plugin.php.html#functionget_plugin_data
 *
 * @param	$filename		string		name of the file we are looking for e.g. silverghyll-admin.php (default)
 * @param	$path_type		string		d, dir => return a directory (folder) path (default)
 *										u, url => return URL path
 * 
 * @return full path (dir or URL) to best version of the file we can find
 */
function silverghyll_include_best_library( $filename = 'silverghyll-admin-menu.php', $path_type = 'dir' ) {

	$transient_name = '!BEST LIBRARY!' . $filename . '&' . $path_type;
	
	global $silverghyll_transients;	
	if( $silverghyll_transients->get( $transient_name ) ) return $silverghyll_transients->get( $transient_name ); // Avoid parsing the files if we can!
	
	
	$path_type = strtolower($path_type);
	$silverghyll_plugins = silverghyll_plugin_library_dirs();
		
	// Test whether we have a theme version to test as well
	$silverghyll_options = get_option( 'silverghyll-girls-theme' );
	if( $silverghyll_options['theme_name'] == get_current_theme() ) 
			$silverghyll_plugins[ $silverghyll_options['theme_uri'] . 'silverghyll-library/' ] = $silverghyll_options['theme_dir'] . 'silverghyll-library/';

	$max_ver = 0; // The best version found
	$best_path = '';  // The path of the best version found
	$best_date = 0; // The date of the best version found as ymdHi format
	
	foreach($silverghyll_plugins as $plugin => $path):


		$file_path = $path . $filename;
		if( !file_exists($file_path) ):
			if( function_exists('silverghyll_debug_log') and silverghyll_debug_status() ) 
				silverghyll_debug_log( "Missing library $filename in $path<br/>" ); // If debugging, we need to know
		 	continue; //  Skip any old libraries which don't contain a version of the file we want
		endif;


		$ver = silverghyll_versioneer($file_path);
		if( (!$ver) and	function_exists('silverghyll_debug_log') ) silverghyll_debug_log('No version found in ' . $file_path . ' while finding best library');
		$major_tok = strtok($ver, ".");  // Major release
		$minor_tok = ($major_tok) ? strtok(".") : '';  // Minor release
		$patch_tok = ($minor_tok) ? strtok(".") : '';  // Patch release
		$num_ver = 10000 * $major_tok + 100 * $minor_tok + $patch_tok; // Build into a number
		$item_date = date( 'ymdHi', filemtime( $file_path ) );
		
		// Debugging aid - aomment out for live use
/*		echo "<p><strong>File: $filename</strong><br/>";
		echo "Bagged: ver=$max_ver($best_date) & path=$best_path<br/>";
		echo "Testing: ver=$num_ver($item_date) & path=$path<br/></p>"; */
		
		// Best is highest version or, if version is identical, the newest modified
		if( ($max_ver < $num_ver) or ( ($max_ver == $num_ver) and ($item_date > $best_date) ) ):
			$best_path = ( ( $path_type[0] == 'd')  ? $path : $plugin ) . $filename; // We have a better version!
			$best_date = $item_date;
			$max_ver = $num_ver;
		endif;
	endforeach;
	
	// Store in a transient to avoid iterating when not needed
	$silverghyll_transients->set( $transient_name, $best_path );
	
	return $best_path;
}

/***
 * Return array of all silverghyll plugins on the system
 *
 * Uses the get_plugin_data to get details of all plugins and returns those attributed to Author = Kate Phizackerley
 * See http://phpdoc.wordpress.org/trunk/WordPress/Administration/_wp-admin---includes---plugin.php.html#functionget_plugin_data
 *
 * @param $name	String	optional - only use when setting information about a plugins directory - the name of the plugin
 * @param $dir	String	optional - only use when setting information about a plugins directory - full folder (basename) of the main plugin file
 * @param $url	String	optional - only use when setting information about a plugins directory - url to the main plugin folder
 *
 *
 * @return array array of plugins details as strings in form used by get_plugins()
 */

function get_silverghyll_plugins($name = '', $dir = '', $url = '') {

	static $silverghyll_plugins, $parsed;

	if( !$parsed and function_exists( 'get_plugins' ) ): // Only parse plugin details once but wait untol get_plugins() becomes available - also protects non-WP installs

		// Add some standard text to advertise any plugins which are not installed
		$silverghylls = array( 
			'Taxonomy Picker' => 'Interactive search builder widget for your custom taxonomies',  
			'Phiz Feeds' => 'FORTHCOMIMG - Include newsfeeds in your posts and pages by using a flexible shortcode',
			'Egyptological Hieroglyphs' => 'FORTHCOMIMG - Adds a shortcode which displays Egyptian Hieroglyphs by parsing basic Manuel de Codage syntax', 
			'Egyptological New Gardiner Hieroglyphs' => 'Adds a shortcode which displays Egyptian Hieroglyphs based on Dr Mark-Jan Nederhof\'s New Gardiner font' );
		foreach($silverghylls as $name => $description):
			$silverghyll_plugins[ $name ][ 'Description' ] = $description;
			$silverghyll_plugins[ $name ][ 'Name' ] = $name;
		endforeach;

		$plugins = get_plugins();
		foreach($plugins as $plugin): 
			if( ($plugin['Author'] == 'Kate Phizackerley') or (substr( $plugin['Author'], 0 , 11 ) == 'Silverghyll') ):
				$silverghyll_plugins[ $plugin['Name'] ] = array_merge( (array)$silverghyll_plugins[ $plugin['Name'] ] , $plugin ); // Store the plugin details
			endif;
		endforeach;
		$parsed = true; // Flag we have built the array
	endif;
	
	if( func_num_args() > 1 ):
		$silverghyll_plugins[ $name ][ 'dir' ] = $dir; // Add info on the dir into our array.
		$silverghyll_plugins[ $name ][ 'url' ] = $url; // Add info on the url into our array.
	endif;
	
	return $silverghyll_plugins;

}


/***
 * Return array of all paths to silverghyll plugin library on the system
 *
 * @return array  string 	key => URL to library stylesheet => full DIR path to silverghyll-library with trailing slash
 */


function silverghyll_plugin_library_dirs() {

	static $installed_plugins; //Expensive in time so only run once
	if( !empty($installed_plugins) ) return $installed_plugins;
	
	$folder = WP_PLUGIN_DIR .'/';
	foreach (new DirectoryIterator($folder) as $file):
   		if ( (!$file->isDot()) && ($file->getFilename() != basename($_SERVER['PHP_SELF'])) ):
      		if($file->isDir()):
      			if( file_exists( $folder . $file->getFilename() . "/silverghyll-library/silverghyll-admin-menu.php" ) ):
      				$installed_plugins[ trailingslashit(plugins_url()). $file->getFilename() . "/silverghyll-library/" ] = $folder . $file->getFilename() . "/silverghyll-library/";
     			endif;
      		endif;
      	endif;
    endforeach;		
	
	return $installed_plugins;

}
// Identical to get_term() but sorted in tree order
function silverghyll_get_terms_tree($taxonomies, $args) {
	
	if( is_array( $taxonomies ) ):
		foreach( $taxonomies as $taxonomy ) $terms[] = silverghyll_get_terms_tree( $taxonomy, $args ); // Recurse
		return $terms;
	endif;
	
	$args['parent'] = 0; // Get top level only
	$args['orderby'] = 'name'; // Want alphabetically within our tree

	$terms = get_terms($taxonomies, $args ); //Get top level terms
	
	$result = array();
	if( $terms ) foreach( $terms as $term ) $result = array_merge( $result, silverghyll_get_term_subtree($taxonomies, $term, $args) );  // Recurse sub-trees
	return $result;
	
}
// Inner function for the recursion
function silverghyll_get_term_subtree($taxonomy, $term, $args) {

		static $depth = 0;

		$args['parent'] = $term->term_id;
		$kids = get_terms( $taxonomy, $args);
			
		$result[] = $term; // Seed the array			

		$depth++ ;
		if( 5 >= $depth ) if( !empty($kids) ) foreach($kids as $kid ) $result = array_merge( $result, silverghyll_get_term_subtree($taxonomy, $kid, $args) ); // Recurse
		$depth-- ;
		
		return $result; // Will always return an array with at least one item
}


?>