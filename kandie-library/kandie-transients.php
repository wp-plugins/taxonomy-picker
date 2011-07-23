<?php

// Version: 1.0.2

/***
 * Kandie transients class is used to combine our common, semi-permanent transients to reduce database calls by getting them just once in each session
 */
 
global $kandie_transients; 
$kandie_transients = new kandie_transient_class();
	 
class kandie_transient_class {

	private $transients;  // $this is what we are storing
	private $duration; // How long we will save transients for
	
	public function __construct() {
		$this->duration = 60 * 60 * 24;  // Default to one day
		if( is_admin() ) $this->flush(); else $this->transients = get_transient( 'kandie-transients' ); // Flush in admin() 			
	}
	
	// Return time transients were last saved in time() format
	public function timestamp() {
		return $this->transients['timestamp'];
	}
	
	// Clear all transients and reset timestamp
	public function flush() {
		unset( $this->transients );
		$this->set( 'timestamp', time() );
	}
	
	// Get a transient
	public function get($name) {
		return $this->transients[$name];
	}
	
	// Set a transient
	public function set($name, $value) {
		$this->transients[$name] = $value;
		set_transient( 'kandie-transients', $this->transients, $this->duration );		
		return $value;
	}
	
	// Magic to string - return the transients array
	public function __toString() {
		return var_export( $this->transients , true );
    }
	
} // End of class definition
	

?>