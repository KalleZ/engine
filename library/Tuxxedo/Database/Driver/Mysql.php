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
	use Tuxxedo\Database\Driver\Mysql;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * MySQL driver for Tuxxedo Engine
	 *
	 * This driver enables access to a MySQL 3+ based database using 
	 * the mysql database extension. If using MySQL 4.1+ the MySQLi 
	 * driver should be used as a better alternative for talking to 
	 * MySQL
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Mysql extends Database
	{
		/**
		 * Driver name
		 *
		 * @var		string
		 */
		const DRIVER_NAME		= 'mysql';


		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		resource
		 */
		protected $link;


		/**
		 * Returns if the current system supports the driver, if this 
		 * method isn't called, a driver may start not function properly 
		 * on the system
		 *
		 * @return	boolean				True if dirver is supported, otherwise false
		 */
		public function isDriverSupported()
		{
			static $supported;

			if($supported === NULL)
			{
				$supported = \extension_loaded('mysql');
			}

			return($supported);
		}

		/**
		 * Get driver requirements, as an array that can be iterated to 
		 * see which requirements that passes, and which that do not
		 *
		 * Each driver may return their own set of keys, but built-in 
		 * drivers will remain consistent across each other
		 *
		 * @return	array				Returns an array containing elements of which requirements and their status
		 */
		public function getDriverRequirements()
		{
			static $requirements;

			if(!$requirements)
			{
				$requirements = Array(
							'extension'	=> \extension_loaded('mysql')
							);
			}

			return($requirements);
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

			\ini_set('mysql.connect_timeout', (($timeout = $this->configuration['timeout']) !== false ? $timeout : 3));

			$port			= $this->configuration['port'];
			$connect_function 	= ($this->configuration['persistent'] ? '\mysql_pconnect' : '\mysql_connect');
			$hostname		= (($socket = $this->configuration['socket']) ? $this->configuration['hostname'] . ':' . $socket : $this->configuration['hostname']);

			if(empty($socket) && $port)
			{
				$hostname .= ':' . $port;
			}

			Registry::globals('error_reporting', false);

			if(($link = $connect_function($hostname, $this->configuration['username'], $this->configuration['password'], false, ($this->configuration['ssl'] ? MYSQL_CLIENT_SSL : 0))) === false || !mysql_select_db($this->configuration['database'], $link))
			{
				Registry::globals('error_reporting', true);

				$format = 'Database error: failed to connect database';

				if(\TUXXEDO_DEBUG)
				{
					$format = 'Database error: [%d] %s';
				}

				throw new Exception\Basic($format, mysql_errno(), mysql_error());
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
			if(\is_resource($this->link))
			{
				$retval 	= (boolean) \mysql_close($this->link);
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
			return(\is_resource($this->link));
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
			return(\is_resource($link) && \get_resource_type($link) == 'mysql link');
		}

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean				True if the connection is persistent, otherwise false
		 */
		public function isPersistent()
		{
			return((boolean) $this->configuration['persistent']);
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
			return(\is_resource($result) && \get_resource_type($result) == 'mysql result');
		}

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string				The error message
		 */
		public function getError()
		{
			if(!\is_resource($this->link))
			{
				return(false);
			}

			return(\mysql_error($this->link));
		}

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer				The error number
		 */
		public function getErrno()
		{
			if(!\is_resource($this->link))
			{
				return(false);
			}

			return(\mysql_errno($this->link));
		}

		/**
		 * Get the last insert id from last executed SELECT statement
		 *
		 * @return	integer				Returns the last insert id, and boolean false on error
		 */
		public function getInsertId()
		{
			if(!\is_resource($this->link))
			{
				return(false);
			}

			return(\mysql_insert_id($this->link));
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

			if(!\is_resource($this->link))
			{
				return(false);
			}

			return(\mysql_real_escape_string((string) $data, $this->link));
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

			if(empty($sql) || !\is_resource($this->link))
			{
				return(false);
			}
			elseif(\func_num_args() > 1)
			{
				$sql = \call_user_func_array('\sprintf', \func_get_args());
			}

			Registry::globals('error_reporting', false);
			$query = \mysql_query($sql);
			Registry::globals('error_reporting', true);

			if($query === true)
			{
				$this->queries[] 	= $sql;
				$this->affected_rows 	= (integer) \mysql_affected_rows();

				return(true);
			}
			elseif(\is_resource($query))
			{
				$this->queries[] = $sql;

				return(new Mysql\Result($this, $query));
			}
			elseif(!\is_resource($query) && \mysql_errno($this->link))
			{
				throw new Exception\SQL($sql, self::DRIVER_NAME, \mysql_error($this->link), \mysql_errno($this->link));
			}

			return(false);
		}
	}
?>