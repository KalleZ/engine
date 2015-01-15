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
	 * Note that some drivers may not be fully supported, in paticular 
	 * SQLite is not supported by some of the table operational methods.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 *
	 * @changelog		1.2.0				This class now escapes all input unlike before where only some were escaped
	 * @changelog		1.2.0				This class now supports PostgreSQL unless stated otherwise (per method)
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
		 * Database driver helper -- Is MySQL?
		 *
		 * @var		boolean
		 * @since	1.2.0
		 */
		protected $driver_is_mysql		= false;

		/**
		 * Database driver helper -- Is PostgreSQL?
		 *
		 * @var		boolean
		 * @since	1.2.0
		 */
		protected $driver_is_pgsql		= false;

		/**
		 * Database driver helper -- Is SQLite?
		 *
		 * @var		boolean
		 * @since	1.2.0
		 */
		protected $driver_is_sqlite		= false;


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
			$this->instance 	= $instance;
			$this->driver		= \strtolower($instance->cfg('driver'));

			$this->driver_is_mysql	= $this->driver_is_pgsql = $this->driver_is_sqlite = false;

			if($this->driver == 'pdo' && ($subdriver = \strtolower($instance->cfg('subdriver'))) != false)
			{
				$this->driver .= '_' . $subdriver;
			}

			if($this->driver == 'mysql' || $this->driver == 'mysqli' || $this->driver == 'pdo_mysql')
			{
				$this->driver_is_mysql = true;
			}
			elseif($this->driver == 'pgsql' || $this->driver == 'pdo_pgsql')
			{
				$this->driver_is_pgsql = true;
			}
			elseif($this->driver == 'sqlite' || $this->driver == 'pdo_sqlite')
			{
				$this->driver_is_sqlite = true;
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
		 * Checks if the database instance is using a MySQL backend
		 *
		 * @return	boolean				Returns true if the backend is MySQL based, otherwise false
		 *
		 * @since	1.2.0
		 */
		public function isDriverMysql()
		{
			return($this->driver_is_mysql);
		}

		/**
		 * Checks if the database instance is using a PostgreSQL backend
		 *
		 * @return	boolean				Returns true if the backend is PostgreSQL based, otherwise false
		 *
		 * @since	1.2.0
		 */
		public function isDriverPgsql()
		{
			return($this->driver_is_pgsql);
		}

		/**
		 * Checks if the database instance is using a SQLite backend
		 *
		 * @return	boolean				Returns true if the backend is SQLite based, otherwise false
		 *
		 * @since	1.2.0
		 */
		public function isDriverSqlite()
		{
			return($this->driver_is_sqlite);
		}

		/**
		 * Truncates a database table
		 *
		 * @param	string				The table to truncate
		 * @return	boolean				Returns true on succes and false on error
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if the database operation failed
		 *
		 * @changelog	1.2.0				This method now supports PostgreSQL
		 */
		public function truncate($table)
		{
			if($this->driver_is_sqlite)
			{
				$sql = 'DELETE FROM "' . \TUXXEDO_PREFIX . '%s"';
			}
			else
			{
				$sql = 'TRUNCATE TABLE "' . \TUXXEDO_PREFIX . '%s"';
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
		public function count($table, $index = '*', Array $where = [])
		{
			if($index != '*')
			{
				$index = '"' . $this->instance->escape($index) . '"';
			}

			$whereclause = '';

			if($where)
			{
				$whereclause = 'WHERE ';

				foreach($where as $field => $value)
				{
					$whereclause .= '"' . $field . '" = \'' . $this->instance->escape($value) . '\', ';
				}

				$whereclause = rtrim($whereclause, ', ');
			}

			$query = $this->instance->query('
								SELECT 
									COUNT(%s) AS "total" 
								FROM 
									"%s"
								%s', $index, $this->instance->escape($table), $whereclause);

			if(!$query || !$query->getNumRows())
			{
				return(false);
			}

			return((integer) $query->fetchObject()->total);
		}

		/**
		 * Gets all tables within a database
		 *
		 * Note, some database systems like SQLite, may return system tables like sqlite_sequence 
		 * etc., so don't count on this being identical between two systems.
		 *
		 * @param	string				The database name, if differs from the current connection
		 * @return	array				Returns an array with a list of tables or false on error
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if the database operation failed
		 *
		 * @changelog	1.2.0				This method now supports PostgreSQL
		 * @changelog	1.2.0				This method now supports SQLite
		 * @changelog 	1.2.0				This method no longer returns a result object, but an array of tables for cross database compatibility
		 */
		public function getTables($database = NULL)
		{
			$retval = [];

			if($this->driver_is_sqlite)
			{
				$field	= 'name';
				$tables = $this->instance->query('
									SELECT 
										"name"
									FROM 
										"sqlite_master" 
									WHERE 
										"type" = \'table\' 
									ORDER BY 
										"name" ASC');
			}
			elseif($this->driver_is_mysql)
			{
				$field	= 'Name';
				$tables = $this->instance->equery('
									SHOW TABLE STATUS FROM 
										"%s"', ($database === NULL ? $this->instance->cfg('database') : $database));
			}
			elseif($this->driver_is_pgsql)
			{
			}
			else
			{
				return(false);
			}

			if(!$tables || !$tables->getNumRows())
			{
				return(false);
			}

			while($row = $tables->fetchAssoc())
			{
				$retval[] = $row[$field];
			}

			return(($retval ? $retval : false));
		}

		/**
		 * Table operation - optimize
		 *
		 * Unsupported drivers are:
		 *  - pgsql
		 *  - sqlite
		 *  - pdo_pgsql
		 *  - pdo_sqlite
		 *
		 * @param	string				The table name
		 * @return	string				Returns the status, and false if unsupported
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if the database operation failed
		 */
		public function tableOptimize($table)
		{
			if($this->driver_is_mysql)
			{
				return($this->instance->equery('OPTIMIZE TABLE "%s"', $table)->fetchObject()->Msg_text);
			}

			return(false);
		}

		/**
		 * Table operation - repair
		 *
		 * Unsupported drivers are:
		 *  - pgsql
		 *  - sqlite
		 *  - pdo_pgsql
		 *  - pdo_sqlite
		 *
		 * @param	string				The table name
		 * @return	string				Returns the status, and false if unsupported
		 */
		public function tableRepair($table)
		{
			if($this->driver_is_mysql)
			{
				return($this->instance->equery('REPAIR TABLE "%s"', $table)->fetchObject()->Msg_text);
			}

			return(false);
		}

		/**
		 * Gets all columns in from a table
		 *
		 * @param	string				The table name
		 * @return	array				Returns an array with all the column names for that table or false on error
		 *
		 * @since	1.2.0
		 */
		public function getColumns($table)
		{
			if($this->driver_is_sqlite)
			{
				$sql 	= 'PRAGMA table_info(' . $this->instance->escape($table) . ')';
				$field	= 'name';
			}
			elseif($this->driver_is_mysql)
			{
				$sql 	= 'SHOW COLUMNS FROM "' . $this->instance->escape($table) . '"';
				$field 	= 'Field';
			}
			elseif($this->driver_is_pgsql)
			{
			}
			else
			{
				return(false);
			}

			$columns = $this->instance->query($sql);

			if(!$columns || !$columns->getNumRows())
			{
				return(false);
			}

			$retval = [];

			foreach($columns as $column)
			{
				$retval[] = $column[$field];
			}

			return($retval);
		}
	}
?>