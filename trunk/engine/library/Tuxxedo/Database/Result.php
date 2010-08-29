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
	
	namespace Tuxxedo\Database;

	use Tuxxedo\Exception;

	/**
	 * Abstract database result class
	 *
	 * Every driver result class must extend this class in order to be loadable 
	 * and to comply with the database access layer interface.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	abstract class Result implements Result\Specification
	{
		/**
		 * The database instance from where the result was created
		 *
		 * @var		Tuxxedo_Database
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
		private $cached_num_rows	= 0;


		/**
		 * Constructs a new result object
		 *
		 * @param	Tuxxedo_Database	A database instance
		 * @param	mixed			A database result, this must be delivered from the driver it was created from
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the result passed is from a different driver type, or if the result does not contain any results
		 */
		public function __construct(\Tuxxedo\Database $instance, $result)
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
	}
?>