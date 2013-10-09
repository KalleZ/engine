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
	 * Database driver namespace, this contains all the main driver files. All 
	 * sub classes are stored in their relevant sub namespace named after the 
	 * database driver.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Database\Driver;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Database;
	use Tuxxedo\Database\Driver\Sqlite;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * SQLite driver
	 *
	 * This driver enables access to a Sqlite 3+ based database using 
	 * the sqlite3 database extension. Engine does not in any way support 
	 * older versions of sqlite as only 3+ is supported.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.1.0
	 */
	class Sqlite extends Database
	{
		/**
		 * Driver name
		 *
		 * @var		string
		 */
		const DRIVER_NAME		= 'sqlite';


		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		resource
		 */
		protected $link;


		/**
		 * Checks if the current system supports the driver, if this 
		 * method isn't called, a driver may start not function properly 
		 * on the system
		 *
		 * @return	boolean				True if driver is supported, otherwise false
		 */
		public function isDriverSupported()
		{
			static $supported;

			if($supported === NULL)
			{
				$supported = \extension_loaded('sqlite3');
			}

			return($supported);
		}

		/**
		 * Connect to a database, if no connection isn't already 
		 * active
		 *
		 * @param	array				Change the configuration and use this new configuration to connect with
		 * @return	boolean				True if a successful connection was made
	 	 *
		 * @throws	\Tuxxedo\Exception\Basic	If a database connection fails
		 */
		public function connect(Array $configuration = NULL)
		{
			if($configuration !== NULL)
			{
				$this->configuration = $configuration;
			}

			if(\is_resource($this->link))
			{
				return(true);
			}

			Registry::globals('error_reporting', false);

			try
			{
				if(!empty($this->configuration['password']))
				{
					$link = new \SQLite3($this->configuration['database'], \SQLITE3_OPEN_READWRITE, $this->configuration['password']);
				}
				else
				{
					$link = new \SQLite3($this->configuration['database'], \SQLITE3_OPEN_READWRITE);
				}
			}
			catch(\Exception $e)
			{
				Registry::globals('error_reporting', true);

				if($this->debug)
				{
					throw new Exception\Basic('Database error: %s', $e->getMessage());
				}

				throw new Exception\Basic('Database error: failed to connect database');
			}

			Registry::globals('error_reporting', true);

			$this->link = $link;
		}

		/**
		 * Close a database connection
		 *
		 * @return	boolean				True if the connection was closed, otherwise false
		 */
		public function close()
		{
			if($this->link instanceof \SQLite3)
			{
				$retval 	= (boolean) $this->link->close();
				$this->link 	= NULL;

				return($retval);
			}

			return(false);
		}

		/**
		 * Checks if a connection is active
		 *
		 * @return	boolean				True if a connection is currently active, otherwise false
		 */
		public function isConnected()
		{
			return($this->link instanceof \SQLite3);
		}

		/**
		 * Checks if a variable is a connection of the same type 
		 * as the one used by the driver
		 *
		 * @param	mixed				The variable to check
		 * @return	boolean				True if the variable type matches, otherwise false
		 */
		public function isLink($link)
		{
			return(\is_object($link) && $link instanceof \SQLite3);
		}

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean				True if the connection is persistent, otherwise false
		 */
		public function isPersistent()
		{
			return(false);
		}

		/**
		 * Checks if a variable is a result of the same type as 
		 * the one used by the driver
		 *
		 * @param	mixed				The variable to check
		 * @return	boolean				True if the variable type matches, otherwise false
		 */
		public function isResult($result)
		{
			return(\is_object($result) && $result instanceof \SQLite3Result);
		}

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string				The error message
		 */
		public function getError()
		{
			if(!($this->link instanceof \SQLite3))
			{
				return(false);
			}

			return($this->link->lastErrorMsg());
		}

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer				The error number
		 */
		public function getErrno()
		{
			if(!($this->link instanceof \SQLite3))
			{
				return(false);
			}

			return($this->link->lastErrorCode());
		}

		/**
		 * Get the last insert id from last executed INSERT statement
		 *
		 * @return	integer				Returns the last insert id, and boolean false on error
		 */
		public function getInsertId()
		{
			if(!($this->link instanceof \SQLite3))
			{
				return(false);
			}

			return($this->link->lastInsertRowID());
		}

		/**
		 * Escape a piece of data using the database specific 
		 * escape method
		 *
		 * @param	mixed				The data to escape
		 * @return	string				Escaped data
		 */
		public function escape($data)
		{
			if($this->delayed)
			{
				$this->delayed = false;

				$this->connect();
			}

			if(!($this->link instanceof \SQLite3))
			{
				return(false);
			}

			return($this->link->escapeString($data));
		}

		/**
		 * Executes a query and returns the result on SELECT 
		 * statements
		 *
		 * @param	string				SQL to execute
		 * @param	mixed				Genetic parameter for formatting, if two or more parameters are passed to the method, the sql will be formatted using sprintf
		 * @return	boolean|object			Returns a result object on SELECT statements, and boolean true otherwise if the statement was executed
		 *
		 * @throws	\Tuxxedo\Exception\SQL		If the SQL should fail for whatever reason, an exception is thrown
		 */
		public function query($sql)
		{
			$sql = (string) $sql;

			if($this->delayed)
			{
				$this->delayed = false;

				$this->connect();
			}

			if(empty($sql) || !($this->link instanceof \SQLite3))
			{
				return(false);
			}
			elseif(\func_num_args() > 1)
			{
				$sql = \call_user_func_array('\sprintf', \func_get_args());
			}

			if($this->registry->trace)
			{
				$this->registry->trace->start();
			}

			Registry::globals('error_reporting', false);
			$query = $this->link->prepare($sql);
			Registry::globals('error_reporting', true);

			if($query)
			{
				$query = $query->execute();
			}

			if(!$query)
			{
				if($this->registry->trace)
				{
					$this->registry->trace->end();
				}

				throw new Exception\SQL($sql, self::DRIVER_NAME, $this->link->lastErrorMsg(), $this->link->lastErrorCode());
			}

			$sql = Array(
					'sql'	=> $sql, 
					'trace'	=> false
					);

			if($this->registry->trace)
			{
				$sql['trace'] = $this->registry->trace->end();
			}

			$this->queries[] 	= $sql;
			$this->affected_rows 	= (integer) $this->link->changes();

			if(!$query->numColumns())
			{
				return(true);
			}

			return(new Sqlite\Result($this, $query));
		}
	}
?>