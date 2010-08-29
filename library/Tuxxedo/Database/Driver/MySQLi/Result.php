<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @license		Apache License, Version 2.0
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	namespace Tuxxedo\Database\Driver\MySQLi;
    
    /**
	 * MySQL Improved result class for Tuxxedo
	 *
	 * This implements the result class for MySQL Improved for 
	 * Tuxxedo, this contains methods to fetch, count result rows and 
	 * such for working with a resultset.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	final class Result extends \Tuxxedo\Database\Result
	{
		/**
		 * The result resource
		 *
		 * @var		mysqli_result
		 */
		protected $result;


		/**
		 * Frees the result from memory, and makes it unusable
		 *
		 * @return	boolean			Returns true if the result was freed, otherwise false
		 */
		public function free()
		{
			if(is_object($this->result))
			{
				$this->result->free();
				$this->result = NULL;

				return(true);
			}

			return(false);
		}

		/**
		 * Checks whenever the result is freed or not
		 *
		 * @return	boolean			Returns true if the result is freed from memory, otherwise false
		 */
		public function isFreed()
		{
			return($this->result !== NULL);
		}

		/**
		 * Get the number of rows in the result
		 *
		 * @return	integer			Returns the number of rows in the result, and boolean false on error
		 */
		public function getNumRows()
		{
			if(!is_object($this->result))
			{
				return((isset($this->cached_num_rows) ? $this->cached_num_rows : 0));
			}

			return((integer) $this->result->num_rows);
		}

		/**
		 * Fetch result with both associative and indexed indexes array
		 *
		 * @return	array			Returns an array with the result
		 */
		public function fetchArray()
		{
			return($this->fetch(1));
		}

		/**
		 * Fetches the result and returns an associative array
		 *
		 * @return	array			Returns an associative array with the result
		 */
		public function fetchAssoc()
		{
			return($this->fetch(2));
		}

		/**
		 * Fetches the result and returns an indexed array
		 *
		 * @return	array			Returns an indexed array with the result
		 */
		public function fetchRow()
		{
			return($this->fetch(3));
		}

		/**
		 * Fetches the result and returns an object, with overloaded 
		 * properties for rows names
		 *
		 * @return	object			Returns an object with the result
		 */
		public function fetchObject()
		{
			return($this->fetch(4));
		}

		/**
		 * Quick reference for not repeating code when fetching a different type
		 *
		 * @param	integer			Result mode, 1 = array, 2 = assoc, 3 = row & 4 = object
		 * @return	array|object		Result type is based on result mode, boolean false is returned on errors
		 */
		private function fetch($mode)
		{
			if(!is_object($this->result) || !$this->result->num_rows)
			{
				return(false);
			}

			switch($mode)
			{
				case(1):
				{
					return($this->result->fetch_array());
				}
				case(2):
				{
					return($this->result->fetch_assoc());
				}
				case(3):
				{
					return($this->result->fetch_row());
				}
				case(4):
				{
					return($this->result->fetch_object());
				}
			}

			return(false);
		}
	}
?>