<?php

// Version: 3.0

/***
 * Silverghyll transients class is used to combine our common, semi-permanent transients to reduce database calls by getting them just once in each session
 */
 
global $silverghyll_transients; 
$silverghyll_transients = new silverghyll_transient_class();
	 
class silverghyll_transient_class {

	private $transients;  // $this is what we are storing
	private $duration; // How long we will save transients for
	
	public function __construct() {
		$this->duration = 60 * 60 * 24;  // Default to one day
		if( is_admin() ) $this->flush(); else $this->transients = get_transient( 'silverghyll-transients' ); // Flush in admin() 			
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
		if( array_key_exists( $name, $this->transients ) ) return $this->transients[$name]; else return null;
	}
	
	// Set a transient
	public function set($name, $value) {
		$this->transients[$name] = $value;
		set_transient( 'silverghyll-transients', $this->transients, $this->duration );		
		return $value;
	}
	
	// Magic to string - return the transients array
	public function __toString() {
		return var_export( $this->transients , true );
    }
	
} // End of class definition
	

?>