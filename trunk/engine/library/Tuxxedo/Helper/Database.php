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
	 * Helper namespace, this namespace is for standard helpers that comes 
	 * with Engine.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Helper;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Database utilities helper
	 *
	 * This helper assumes the 'db' key is registered to an instance of 
	 * \Tuxxedo\Database for usage, if not then it can be manually set 
	 * using the 'setInstance' method.
	 *
	 * Parameters used by this class are sent directly as a part of the 
	 * generated SQL query. Only parameters that specifically says so are 
	 * escaped using the database escaping mechanism assigned to the 
	 * database registry instance.
	 *
	 * Note that some drivers may not be fully supported, in paticular 
	 * SQLite is not supported by some of the table operational methods.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Database
	{
		/**
		 * Database instance
		 *
		 * @var		\Tuxxedo\Database
		 */
		protected $instance;

		/**
		 * Database driver
		 *
		 * @var		string
		 */
		protected $driver;


		/**
		 * Constructs the database helper
		 *
	 	 * @param	\Tuxxedo\Registry		The Tuxxedo object reference
		 */
		public function __construct(Registry $registry)
		{
			if($registry->db)
			{
				$this->setInstance($registry->db);
			}
		}

		/**
		 * Sets a new instance of a database object
		 *
		 * @param	\Tuxxedo\Database		The database object to apply operations on
		 * @return	void				No value is returned
		 */
		public function setInstance(\Tuxxedo\Database $instance)
		{
			$this->instance = $instance;
			$this->driver	= \strtolower($instance->cfg('driver'));

			if($this->driver == 'pdo' && ($subdriver = \strtolower($instance->cfg('subdriver'))) != false)
			{
				$this->driver .= '_' . $subdriver;
			}
		}

		/**
		 * Gets the canonical driver name
		 *
		 * @return	string				Returns the canonical driver name for the internal instance
		 */
		public function getDriver()
		{
			return($this->driver);
		}

		/**
		 * Truncates a database table
		 *
		 * @param	string				The table to truncate
		 * @return	boolean				Returns true on succes and false on error
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if the database operation failed
		 */
		public function truncate($table)
		{
			if($this->driver == 'sqlite' || $this->driver == 'pdo_sqlite')
			{
				$sql = 'DELETE FROM `' . \TUXXEDO_PREFIX . '%s`';
			}
			else
			{
				$sql = 'TRUNCATE TABLE `' . \TUXXEDO_PREFIX . '%s`';
			}

			return($this->instance->equery($sql, $table));
		}

		/**
		 * Counts the number of rows in a table
		 *
		 * @param	string				The table to count
		 * @param	string				Optionally an index, defaults to *
		 * @param	array				Key => value pairs for a WHERE, defaults to no 'where' clause, all values are escaped
		 * @return	integer				Returns the number of rows, and false on error
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if the database operation failed
		 *
		 * @changelog	1.2.0				Added the $where parameter
		 */
		public function count($table, $index = '*', Array $where = Array())
		{
			if($index != '*')
			{
				$index = '`' . $index . '`';
			}

			$whereclaus = '';

			if($where)
			{
				foreach($where as $field => $value)
				{
					$whereclause .= '`' . $field . '` = \'' . $this->instance->escape($value) . '\', ';
				}

				$whereclause = rtrim($whereclause, ', ');
			}

			$query = $this->instance->query('
								SELECT 
									COUNT(%s) as \'total\' 
								FROM 
									`%s`
								%s', $index, $table, $whereclause);

			if(!$query || !$query->getNumRows())
			{
				return(false);
			}

			return((integer) $query->fetchObject()->total);
		}

		/**
		 * Gets all table within a database
		 *
		 * Unsupported drivers are:
		 *  - sqlite
		 *  - pdo_sqlite
		 *
		 * @param	string				The database name, if differs from the current connection
		 * @return	\Tuxxedo\Database\Result	Returns a database result object, and false if unsupported or if no tables exists
		 */
		public function getTables($database = NULL)
		{
			if($this->driver == 'sqlite' || $this->driver == 'pdo_sqlite')
			{
				return(false);
			}

			$tables = $this->instance->equery('SHOW TABLE STATUS FROM `%s`', ($database === NULL ? $this->instance->cfg('database') : $database));

			if(!$tables || !$tables->getNumRows())
			{
				return(false);
			}

			return($tables);
		}

		/**
		 * Table operation - optimize
		 *
		 * Unsupported drivers are:
		 *  - sqlite
		 *  - pdo_sqlite
		 *
		 * @param	string				The table name
		 * @return	string				Returns the status, and false if unsupported
		 */
		public function tableOptimize($table)
		{
			if($this->driver == 'sqlite' || $this->driver == 'pdo_sqlite')
			{
				return(false);
			}

			return($this->instance->equery('OPTIMIZE TABLE `%s`', $table)->fetchObject()->Msg_text);
		}

		/**
		 * Table operation - repair
		 *
		 * Unsupported drivers are:
		 *  - sqlite
		 *  - pdo_sqlite
		 *
		 * @param	string				The table name
		 * @return	string				Returns the status, and false if unsupported
		 */
		public function tableRepair($table)
		{
			if($this->driver == 'sqlite' || $this->driver == 'pdo_sqlite')
			{
				return(false);
			}

			return($this->instance->equery('REPAIR TABLE `%s`', $table)->fetchObject()->Msg_text);
		}
	}
?>