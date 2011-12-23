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
	 * Database Access Layer implementation. This namespace controls 
	 * all access to the database, multiple drivers for the database 
	 * can be loaded at the same time, along with multiple database 
	 * connection, even to the same database.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Database;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Database;
	use Tuxxedo\Database\Result;
	use Tuxxedo\Design;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Abstract database result class
	 *
	 * Every driver result class must extend this class in order to be loadable 
	 * and to comply with the database access layer interface.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class Result extends Design\Iteratable implements Result\Specification
	{
		/**
		 * Fetch mode constant - row
		 *
		 * @var		integer
		 */
		const FETCH_ROW			= 1;

		/**
		 * Fetch mode constant - array
		 *
		 * @var		integer
		 */
		const FETCH_ARRAY		= 2;

		/**
		 * Fetch mode constant - assoc
		 *
		 * @var		integer
		 */
		const FETCH_ASSOC		= 3;

		/**
		 * Fetch mode constant - object
		 *
		 * @var		integer
		 */
		const FETCH_OBJECT		= 4;


		/**
		 * The database instance from where the result was created
		 *
		 * @var		\Tuxxedo\Database
		 */
		protected $instance;

		/**
		 * The result resource
		 *
		 * @var		mixed
		 */
		protected $result;

		/**
		 * Cached number of rows
		 *
		 * @var		integer
		 */
		protected $cached_num_rows	= 0;

		/**
		 * Current iterator position
		 *
		 * @var		integer
		 */
		protected $position		= 0;

		/**
		 * Fetch mode
		 *
		 * @var		integer
		 */
		protected $fetch_mode		= self::FETCH_ARRAY;

		/**
		 * Iterator data for drivers that need to emulate the iterator functionality
		 *
		 * @var		array
		 */
		protected $iterator_data	= Array();


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
			$this->result 		= $result;
			$this->cached_num_rows	= $this->getNumRows();

			if(!$this->cached_num_rows)
			{
				$this->result = NULL;
			}
		}

		/**
		 * Simple destructor to free result when the 
		 * result is unset.
		 */
		public function __destruct()
		{
			$this->free();
		}

		/**
		 * Sets the fetch mode
		 *
		 * @param	integer				One of the FETCH_* constants
		 * @return	void				No value is returned
		 */
		public function setFetchType($mode)
		{
			if($mode && $mode < 5)
			{
				$this->fetch_mode = (integer) $mode;
			}
		}

		/**
		 * Iterator method - key
		 *
		 * @return	integer				Returns the numeric position of the current row in the resultset
		 */
		public function key()
		{
			return($this->position);
		}

		/**
		 * Iterator method - next
		 *
		 * @return	void				No value is returned
		 */
		public function next()
		{
			++$this->position;
		}

		/**
		 * Iterator method - rewind
		 *
		 * @return	void				No value is returned
		 */
		public function rewind()
		{
			$this->position = 0;
		}

		/**
		 * Iterator method - valid
		 *
		 * @return	boolean				Returns true if its still possible to continue iterating
		 */
		public function valid()
		{
			return($this->cached_num_rows && $this->position >= 0 && $this->position < $this->cached_num_rows);
		}

		/**
		 * Iterator method - current
		 *
		 * @return	mixed				Returns the current result
		 */
		public function current()
		{
			if(!isset($this->iterator_data[$this->position]))
			{
				return(false);
			}

			return($this->iterator_data[$this->position]);
		}

		/**
		 * Countable method, this is essentially a wrapper for getNumRows() 
		 * but allows usage of:
		 *
		 * <pre>
		 * printf('Number of results: %d', sizeof($result));
		 * </pre>
		 *
		 * @return	integer			Returns the number of rows in the result, and 0 on error
		 */
		public function count()
		{
			return($this->cached_num_rows);
		}

		/**
		 * General fetch method, this method uses the FETCH_* constants 
		 * to determine in what format the returned data should be in
		 *
		 * @return	array|object		Returns an object or array based on the fetching mode
		 */
		public function fetch()
		{
			switch($this->fetch_mode)
			{
				case(1):
				{
					return($this->fetchArray());
				}
				case(2):
				{
					return($this->fetchAssoc());
				}
				case(3):
				{
					return($this->fetchRow());
				}
				case(4):
				{
					return($this->fetchObject());
				}
			}

			return(false);
		}
	}
?>