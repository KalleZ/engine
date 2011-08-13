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


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


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
	abstract class Result implements \Iterator, Result\Specification
	{
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
	}
?>