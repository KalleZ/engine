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
	 * PDO driver namespace, for driver components such as result statements 
	 * and the like.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Database\Driver\Pdo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Database;
	use Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * PDO abstraction result driver
	 *
	 * This implements the result class for PDO based subdrivers for 
	 * Tuxxedo, this contains methods to fetch, count result rows and 
	 * such for working with a resultset.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 *
	 * @changelog		1.1.0			The casing of this class' namespace was changed to comply with the autoloader rules
	 */
	class Result extends Database\Result
	{
		/**
		 * The result resource
		 *
		 * @var		\PDOStatement
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

			$this->instance	= $instance;

			while($row = $result->fetch(\PDO::FETCH_NAMED))
			{
				$this->iterator_data[] = $row;
			}

			if($num_rows = \sizeof($this->iterator_data))
			{
				$this->cached_num_rows 	= $num_rows;
				$this->result		= $result;
			}
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
				$this->result = $this->iterator_data = NULL;

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
			if(!$this->result || !isset($this->iterator_data[$this->position]))
			{
				return(false);
			}

			return($this->iterator_data[$this->position++]);
		}

		/**
		 * Fetches the result and returns an associative array
		 *
		 * @return	array				Returns an associative array with the result
		 */
		public function fetchAssoc()
		{
			if(!$this->result || !isset($this->iterator_data[$this->position]))
			{
				return(false);
			}

			$row		= Array();
			$void_keys 	= \range(0, \sizeof($this->iterator_data[$this->position]) - 1);

			foreach($this->iterator_data[$this->position] as $key => $value)
			{
				if(\is_numeric($key) && \in_array($key, $void_keys))
				{
					continue;
				}

				$row[$key] = $value;
			}

			++$this->position;

			return($row);
		}

		/**
		 * Fetches the result and returns an indexed array
		 *
		 * @return	array				Returns an indexed array with the result
		 */
		public function fetchRow()
		{
			if(!$this->result || !isset($this->iterator_data[$this->position]))
			{
				return(false);
			}

			return(\array_values($this->iterator_data[$this->position++]));
		}

		/**
		 * Fetches the result and returns an object, with overloaded 
		 * properties for rows names
		 *
		 * @return	object				Returns an object with the result
		 */
		public function fetchObject()
		{
			if(!$this->result || !isset($this->iterator_data[$this->position]))
			{
				return(false);
			}

			return((object) $this->iterator_data[$this->position++]);
		}
	}
?>