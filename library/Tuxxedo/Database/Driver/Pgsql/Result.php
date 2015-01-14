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
	 * @subpackage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * PostgreSQL driver namespace, for driver components such as result statements 
	 * and the like.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Database\Driver\Pgsql;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Database;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * PostgreSQL result class
	 *
	 * This implements the result class for PostgreSQL for Tuxxedo, 
	 * this contains methods to fetch, count result rows and 
	 * such for working with a resultset
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Result extends Database\Result
	{
		/**
		 * The result resource
		 *
		 * @var		resource
		 */
		protected $result;



		/**
		 * Constructs a new result object
		 *
		 * @param	\Tuxxedo\Database		A database instance
		 * @param	mixed				A database result, this must be delivered from the driver it was created from
		 *
		 * @throws	\Tuxxedo\Exception\Basic	If the result passed is from a different driver type, or if the result does not contain any results
		 */
		public function __construct(Database $instance, $result)
		{
			if(!$instance->isResult($result))
			{
				throw new Exception\Basic('Passed result resource is not a valid result');
			}

			$this->instance		= $instance;
			$this->result		= $result;
			$this->cached_num_rows 	= (integer) \pg_num_rows($result);
		}

		/**
		 * Frees the result from memory, and makes it unusable
		 *
		 * @return	boolean				Returns true if the result was freed, otherwise false
		 */
		public function free()
		{
			if($this->result)
			{
				\pg_free_result($this->result);

				$this->result = NULL;

				return(true);
			}

			return(false);
		}

		/**
		 * Get the number of rows in the result
		 *
		 * @return	integer				Returns the number of rows in the result, or zero on error
		 */
		public function getNumRows()
		{
			if(!$this->result)
			{
				return(0);
			}

			return($this->cached_num_rows);
		}

		/**
		 * Fetch result with both associative and indexed indexes array
		 *
		 * @return	array				Returns an array with the result
		 */
		public function fetchArray()
		{
			if(!$this->result)
			{
				return(false);
			}

			return(\pg_fetch_array($this->result));
		}

		/**
		 * Fetches the result and returns an associative array
		 *
		 * @return	array				Returns an associative array with the result
		 */
		public function fetchAssoc()
		{
			if(!$this->result)
			{
				return(false);
			}

			return(\pg_fetch_assoc($this->result));
		}

		/**
		 * Fetches the result and returns an indexed array
		 *
		 * @return	array				Returns an indexed array with the result
		 */
		public function fetchRow()
		{
			if(!$this->result)
			{
				return(false);
			}

			return(\pg_fetch_row($this->result));
		}

		/**
		 * Fetches the result and returns an object, with overloaded 
		 * properties for rows names
		 *
		 * @return	object				Returns an object with the result
		 */
		public function fetchObject()
		{
			if(!$this->result)
			{
				return(false);
			}

			return(\pg_fetch_object($this->result));
		}

		/**
		 * Iterator method - current
		 *
		 * @return	mixed				Returns the current result
		 */
		public function current()
		{
			if(!$this->result)
			{
				return(false);
			}

			switch($this->fetch_mode)
			{
				case(1):
				{
					return(\pg_fetch_array($this->result, $this->position));
				}
				case(2):
				{
					return(\pg_fetch_assoc($this->result, $this->position));
				}
				case(3):
				{
					return(\pg_fetch_row($this->result, $this->position));
				}
				case(4):
				{
					return(\pg_fetch_object($this->result, $this->position));
				}
			}

			return(false);
		}
	}
?>