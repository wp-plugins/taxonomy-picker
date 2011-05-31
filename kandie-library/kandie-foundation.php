<?php

/* This is a foundation for all Kandie Girls developments adding versioning, best-library and debugging support
// Version: 2.1


/***
 * Get / set Kandie debug status
 *
 * @param 1st  false or '' to turn off; 'echo' or 'true' to default echo stream debugging; 'log' for PHP error log ; simple for echo without extended error handling
 *							or 'trace-echo' or 'trace-log' which turns extended error handling on, plus trace reporting!
 *							or 'squawk' to display with kandie_debug_status() is subsequently set on!
 *
 * @return mixed	false as logical or debug stream as string
 */


function kandie_debug_status() {

	static $kandie_debug_status_saved = false;	
	
	if( func_num_args() > 0 ):
		if($kandie_debug_status_saved == 'squawk'):
			debug_print_backtrace(); // Use to find where debug_status is being set by setting to backtrace' early on
		endif;
		$kandie_debug_status_saved = func_get_arg(0);
		
		// Restrict the valid options
		if( ($kandie_debug_status_saved != 'echo') 
			and ($kandie_debug_status_saved !='log') 
			and ($kandie_debug_status_saved !='simple') 
			and ($kandie_debug_status_saved !='squawk')
			and ($kandie_debug_status_saved !='trace-echo')
			and ($kandie_debug_status_saved !='trace-log')
			and ($kandie_debug_status_saved !== false) )
				 $kandie_debug_status_saved = 'echo' ; // Restrict to backtrace, false, echo or log as only permitted values, defaulting to echo (e.g default true)

		// Turn extended error handling on or off
		if( ($kandie_debug_status_saved == 'log') or ($kandie_debug_status_saved == 'echo') ):
			require_once( 'kandie-debug.php' ); // Load up the debug functions
			set_error_handler("kandie_error_handler");  // set_error_handler
		elseif( ($kandie_debug_status_saved == 'trace-echo')  or ($kandie_debug_status_saved == 'trace-log') ):
			require_once( 'kandie-debug.php' ); // Load up the debug functions
			set_error_handler("kandie_trace_handler");  // set_error_handler
			$kandie_debug_status_saved = substr( $kandie_debug_status_saved, 6);
		elseif( function_exists('kandie_unset_error_handler') and ($kandie_debug_status_saved === false) ):
			kandie_unset_error_handler(); // Turn off extended error handling
		endif;
					 
	endif;
	
	return $kandie_debug_status_saved;
}

/***
 * Prints a neat backtrace - no return
 *
 * @param $stream		String		'echo', 'log' or 'mixed' - as used by kandie_debug_log()
 * @param $drop 		Integer		Number of calls to drop (so error handler isn't shown)
 */

function kandie_backtrace( $stream = 'echo', $drop = 0) {
	require_once( 'kandie-debug.php' ); // Load up the debug functions
		
	if( $stream == 'mixed' ) $stream = kandie_debug_status();
	if( $stream != 'echo' and $stream != 'log' ) $stream = 'echo'; // Default to echo 

	$backtrace = debug_backtrace();

	while($drop):
		array_shift($backtrace);
		$drop--;
	endwhile;

	if( $stream == 'echo' ):
		echo "<table><tbody>";
		array_walk( $backtrace, "kandie_echo_backtrace" ); // Output the backtrace
		echo "</tbody></table><br/<";
	else:
		array_walk( $backtrace, "kandie_log_backtrace" ); // Log the backtrace	
	endif;
}

/***
 * Return the version stored in comments within the specified file
 *
 * $param	$filename	string	(optional) filename to read - defaults to last file read
 *
 * @return	string	version info (if available), blank otherwise
 */

function kandie_versioneer( $filename = '!last!', $comments_only = true ) {  // extracts the version 

	static $last_filename = '';
	static $file_vars = NULL;

	if( ($filename <> $last_filename) and ($filename <> '!last!') ):  // For efficiency, only read a file once if we are using it successively
		$file_vars = kandie_versioneer_read_vars($filename, $comments_only);
		$last_filename =$filename;
	endif;

	return $file_vars['version'];
}

// Read the vars in a file and return as an array of with the key based on the variable name in the comment
function kandie_versioneer_read_vars($filename, $comments_only = true){ 

	if(!file_exists($filename)):
		kandie_debug_log("kandie_versioneer_read_vars cannot open $filename. \n\r" ); // Report failure to open file when in debug mode
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
 * Return path to latest version available or specified Kandie file which must reside in kandie-library
 *
 * Uses the get_plugin_data to get details of all plugins and returns those attributed to Author = Kate Phizackerley
 * See http://phpdoc.wordpress.org/trunk/WordPress/Administration/_wp-admin---includes---plugin.php.html#functionget_plugin_data
 *
 * @param	$filename		string		name of the file we are looking for e.g. kandie-admin.php (default)
 * @param	$path_type		string		d, dir => return a directory (folder) path (default)
 *										u, url => return URL path
 * 
 * @return full path (dir or URL) to best version of the file we can find
 */
function kandie_include_best_library( $filename = 'kandie-admin-menu.php', $path_type = 'dir' ) {
	$path_type = strtolower($path_type);
	$kandie_plugins = kandie_plugin_library_dirs();
		
	// Test whether we have a theme version to test as well
	$theme_library = get_stylesheet_directory() . '/kandie-library/';
	if( file_exists($theme_library . $filename) ) $kandie_plugins[ get_stylesheet_directory_uri() . '/kandie-library/' ] = $theme_library; 

	$max_ver = 0; // The best version found
	$best_path = '';  // The path of the best version found
	$best_date = 0; // The date of the best version found as ymdHi format
	
	foreach($kandie_plugins as $plugin => $path):
		$ver = kandie_versioneer($path . $filename);
		if(!$ver) kandie_debug_log('No version found in ' . $path . $filename . ' while finding best library');
		$major_tok = strtok($ver, ".");  // Major release
		$minor_tok = ($major_tok) ? strtok(".") : '';  // Minor release
		$patch_tok = ($minor_tok) ? strtok(".") : '';  // Patch release
		$num_ver = 10000 * $major_tok + 100 * $minor_tok + $patch_tok; // Build into a number
		$item_date = date( 'ymdHi', filemtime( $path ) );
		
		// Best is highest version or, if version is identical, the newest modified
		if( ($max_ver < $num_ver) or ( ($max_ver == $num_ver) and ($item_date < $best_date) ) ):
			$best_path = ( ( $path_type[0] == 'd')  ? $path : $plugin ) . $filename; // We have a better version!
			$max_ver = $num_ver;
		endif;
	endforeach;
		
	return $best_path;
}

/***
 * Return array of all Kandie plugins on the system
 *
 * Uses the get_plugin_data to get details of all plugins and returns those attributed to Author = Kate Phizackerley
 * See http://phpdoc.wordpress.org/trunk/WordPress/Administration/_wp-admin---includes---plugin.php.html#functionget_plugin_data
 *
 * @return array array of plugins details as strings in form used by get_plugins()
 */

function get_kandie_plugins() {

	$plugins = get_plugins();
	foreach($plugins as $plugin) { if( $plugin['Author'] == 'Kate Phizackerley') { $kandie_plugins[ $plugin['Name'] ] = $plugin; }}
	return $kandie_plugins;

}


/***
 * Return array of all paths to Kandie plugin library on the system
 *
 * @return array  string 	key => URL to library stylesheet => full DIR path to kandie-library with trailing slash
 */


function kandie_plugin_library_dirs() {

	$folder = WP_PLUGIN_DIR .'/';
	foreach (new DirectoryIterator($folder) as $file):
   		if ( (!$file->isDot()) && ($file->getFilename() != basename($_SERVER['PHP_SELF'])) ):
      		if($file->isDir()):
      			if( file_exists( $folder . $file->getFilename() . "/kandie-library/kandie-admin-menu.php" ) ):
      				$installed_plugins[plugins_url('/kandie-library/', basename(__FILE__) )] = $folder . $file->getFilename() . "/kandie-library/";
     			endif;
      		endif;
      	endif;
    endforeach;
	return $installed_plugins;
}

?>