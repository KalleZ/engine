<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Ross Masters <ross@php.net>
	 * @version		1.0
	 * @copyright	Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */
	
	/**
	 * Request object
	 * Holds information relating to the requirements for the result
	 * @package	 Engine
	 */
	class Tuxxedo_Request
	{
		
	}
	
	/**
	 * HTTP Request
	 * Information specific to HTTP requests
	 */
	class Tuxxedo_Request_Http extends Tuxxedo_Request
	{
		/**
		 * @param   array   Variables sent in $_GET
		 */
		protected $getVars;
		
		/**
		 * @param   array   Variables sent in $_POST
		 */
		protected $postVars;
		
		/**
		 * @param   array   Variables sent in $_SESSION
		 */
		protected $sessionVars;
		
		/**
		 * @param   string  The client's remote-address (IP address)
		 */
		protected $remoteAddr;
		
		/**
		 * @param   string  The client's user-agent
		 * @todo	Create a parser for this string to give easier information
		 *		  (clean browser name & version, possibly capabilities etc.)
		 */
		protected $userAgent;
		
		/**
		 * @param   int	 A timestamp of when the request was sent
		 */
		protected $requestTime;
		
		/**
		 * Set an array of options. 
		 * If a method exists (in the form set{normalisedName}) then it will be
		 * called (allowing for pseudo properties); alternatively if a property
		 * exists in the class then that will be used. If neither is found an
		 * exception is thrown.
		 * @param   array   Array of options (try $_SERVER)
		 * @throws  Tuxxedo_Exception_Basic	 If an unknown property is given
		 */
		public function setOptions(array $options) {
			foreach ($options as $key => $value) {
				// Normalise the key name (e.g. for $_SERVER)
				$key = strtolower($key);
				
				// Convert "_*" to remove the _ and uppercase the next letter
				// E.g. remote_addr becomes remoteAddr
				while ($pos = strpos($key, "_")) {
					var_dump($pos);
					$key = substr($key, 0, $pos) . ucfirst(substr($key, $pos + 1));
					var_dump($key);
				}
			
				// Attempt to set the variable (using a setter method if 
				// available).
				$method = "set" . ucfirst($key);
				if (method_exists($this, $method)) {
					$this->$method($value);
				} elseif (property_exists($this, $key)) {
					$this->$key = $value;
				} else {
					throw new Tuxxedo_Exception_Basic("Invalid property $key.");
				}
			}
		}
	}
