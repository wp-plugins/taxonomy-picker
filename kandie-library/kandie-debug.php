<?php
// Kandie Debug functions should never be loaded in a live environment
// Version: 1.0

/***
 * Debug function which logs error messages
 *
 * @param message				string	Text to log / echo
 * @param stream				string	'echo'  or 'log' (for PHP error log) or 'mixed' (default) which defaults to echo if kandie_debug = true, 'log' otherwise
 *										of 'simple' which is echo without extended error handling
 * @param temp_debug_status		logic	if set to true, forces debug message to appear even if debug set off
 *
 * @return 	none
 */
 
function kandie_debug_log($message, $stream = 'mixed', $temp_debug_status = false) {  // Can be turned on by either WP_DEBUG or by using kandie_deug( true )

	$stream = strtolower($stream);
	if($stream == 'mixed' ) $stream = kandie_debug_status();  // Variable default

	if( (WP_DEBUG !== true) and ( kandie_debug_status() === false ) and !$temp_debug_status ) return;  // Break out if no debug option is set on
	
	if( is_array($message) || is_object($message)) $message = print_r( $message, true ); // Convert to something printable
    if( $stream == 'echo' or $stream == 'backtrace'  or $stream == 'simple') echo $message ; else error_log($message); // Print or log it as demanded
}

/***
 * Extended error handling to making tracing errors quicker
 *
 */

function kandie_error_handler($errno, $errstr, $errfile, $errline ) {
	kandie_error_trace_handler($errno, $errstr, $errfile, $errline, false );  // Without backtrace
}

function kandie_trace_handler($errno, $errstr, $errfile, $errline ) {
	kandie_error_trace_handler($errno, $errstr, $errfile, $errline, true ); // With backtrce
}

function kandie_error_trace_handler($errno, $errstr, $errfile, $errline, $trace = false ) {
	if(!(error_reporting() & $errno)) return;  // This error code is not included in error_reporting

	$tidy_errfile = basename( dirname ( $errfile ) ) . '/'. basename( $errfile ); // Just the nice trailing bit!

    switch ($errno) {
    case E_USER_ERROR:
    	kandie_debug_log("<b>KANDIE PHP ERROR</b> [$errno] $errstr<br />\n");
        kandie_debug_log("  Fatal error on line $errline in file $tidy_errfile \n");
        if($trace) kandie_backtrace( kandie_debug_status(), 3 );
        kandie_debug_log("Aborting...<br />\n");
        exit(1);
        break;

    case E_USER_WARNING:
        kandie_debug_log("<b>KANDIE PHP WARNING</b> [$errno] $errstr on line $errline in file $tidy_errfile <br />\n");
        break;

    case E_USER_NOTICE:
        kandie_debug_log("<b>KANDIE NOTICE</b> [$errno] $errstr<br />\n");
        break;

    default:
        kandie_debug_log("Kandie unknown error type: [$errno] $errstr on line $errline in file $tidy_errfile<br />\n");
        break;
    }

    if($trace) kandie_backtrace( kandie_debug_status() , 3 );

    /* Don't execute PHP internal error handler */    
    return true;
}

// Unwind the error handler stack until we're back at the built-in error handler.
function kandie_unset_error_handler()
{
    while (set_error_handler(create_function('$errno,$errstr', 'return false;'))) {
        // Unset the error handler we just set.
        restore_error_handler();
        // Unset the previous error handler.
        restore_error_handler();
    }
    // Restore the built-in error handler.
    restore_error_handler();
}


function kandie_echo_backtrace($item, $key){
    $func = $item['function'];
    $line = $item['line'];
    $file = $item['file'];
    $tidy = trim(basename(dirname($file)) . '/' . basename($file));
    if($tidy == '/') $tidy = ''; else $tidy = " in <b style='color:blue;'>" . $tidy."[".$line."]</b>";
    
	echo  "<tr>&nbsp;<td><b style='color:red'>$func</b></td><td>{$tidy}<br/></tr>";
}

function kandie_log_backtrace($item, $key){
    $func = $item['function'];
    $line = $item['line'];
    $file = $item['file'];
    $tidy = trim(basename(dirname($file)) . '/' . basename($file));
    if($tidy == '/') $tidy = ''; else $tidy = $tidy."[".$line."]";
    
	kandie_debug_log( "$tidy - $func", "log");
}


?>