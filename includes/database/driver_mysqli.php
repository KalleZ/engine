<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * MySQL Improved driver for Tuxxedo Engine
	 *
	 * This driver enables access to a MySQL 4.1+ based database using 
	 * the mysqli database extension. 
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	final class Tuxxedo_Database_Driver_MySQLi extends Tuxxedo_Database
	{
		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		mysqli
		 */
		protected $link;

		/**
		 * Check if persistent connections is active, this 
		 * is to "fake" the isPersistent() method and make 
		 * the return value correct
		 *
		 * @var		boolean
		 */
		protected $persistent = false;


		/**
		 * Returns if the current system supports the  driver, if this 
		 * method isn't called, a driver may start shutting down or 
		 * throwing random exceptions unexpectedly
		 *
		 * @return	boolean			True if dirver is supported, otherwise false
		 */
		public function isDriverSupported()
		{
			return(extension_loaded('mysqli'));
		}

		/**
		 * Connect to a database, if no connection isn't already 
		 * active
		 *
		 * @param	array			Change the configuration and use this new configuration to connect with
		 * @return	boolean			True if a successful connection was made
	 	 *
		 * @throws	Tuxxedo_Basic_Exception	If a database connection fails
		 */
		public function connect(Array $configuration = NULL)
		{
			if(!is_null($configuration))
			{
				$this->configuration = $configuration;
			}

			if(is_object($this->link))
			{
				return(true);
			}

			EG('error_reporting', false);

			$hostname 		= $this->configuration['hostname'];
			$this->persistent 	= false;

			if(TUXXEDO_PHP_VERSION >= 50300 && $this->configuration['persistent'])
			{
				$host 			= 'p:' . $hostname;
				$this->persistent	= true;
			}

			$link = mysqli_init();
			$link->options(MYSQLI_OPT_CONNECT_TIMEOUT, (($timeout = $this->configuration['timeout']) !== false ? $timeout : 3));
			$link->real_connect($hostname, $this->configuration['username'], $this->configuration['password'], $this->configuration['database'], (($port = $this->configuration['port']) ? $port : 3306), (($unix_socket = $this->configuration['socket']) ? $unix_socket : ''), ($this->configuration['ssl'] ? MYSQLI_CLIENT_SSL : 0));

			EG('error_reporting', true);

			if($link->connect_errno)
			{
				$format = 'Database error: failed to connect database';

				if(TUXXEDO_DEBUG)
				{
					$format = 'Database error: [%d] %s';
				}

				throw new Tuxxedo_Basic_Exception($format, $link->connect_errno, $link->connect_error);
			}

			$this->link = $link;
		}

		/**
		 * Close a database connection
		 *
		 * @return	boolean			True if the connection was closed, otherwise false
		 */
		public function close()
		{
			if(is_object($this->link))
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
		 * @return	boolean			True if a connection is currently active, otherwise false
		 */
		public function isConnected()
		{
			return(is_object($this->link));
		}

		/**
		 * Checks if a variable is a connection of the same type 
		 * as the one used by the driver
		 *
		 * @param	mixed			The variable to check
		 * @return	boolean			True if the variable type matches, otherwise false
		 */
		public function isLink($link)
		{
			return(is_object($link) && $link instanceof MySQLi);
		}

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean			True if the connection is persistent, otherwise false
		 */
		public function isPersistent()
		{
			return($this->persistent);
		}

		/**
		 * Checks if a variable is a result of the same type as 
		 * the one used by the driver
		 *
		 * @param	mixed			The variable to check
		 * @return	boolean			True if the variable type matches, otherwise false
		 */
		public function isResult($result)
		{
			return(is_object($result) && $result instanceof MySQLi_Result);
		}

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string			The error message
		 */
		public function getError()
		{
			if(!is_object($this->link))
			{
				return(false);
			}

			return($this->link->error);
		}

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer			The error number
		 */
		public function getErrno()
		{
			if(!is_object($this->link))
			{
				return(false);
			}

			return($this->link->errno);
		}

		/**
		 * Get the last insert id from last executed SELECT statement
		 *
		 * @return	integer			Returns the last insert id, and boolean false on error
		 */
		public function getInsertId()
		{
			if(!is_object($this->link))
			{
				return(false);
			}

			return((integer) $this->link->insert_id);
		}

		/**
		 * Get the number of affected rows from last INSERT INTO/UPDATE/DELETE 
		 * operation. Due to internal reasons this driver also counts number of 
		 * rows like the {@link Tuxxedo_Database_Driver_MySQLi_Result::getNumRows()} 
		 * on SELECT statements, this is only for this driver and should NOT be 
		 * relied on.
		 *
		 * @param	Tuxxedo_Database_Result	The result used to determine how many affected rows there were
		 * @return	integer			Returns the number of affected rows, and 0 on error
		 */
		public function getAffectedRows($result)
		{
			if(!is_object($this->link))
			{
				return(0);
			}

			return((integer) $this->link->affected_rows);
		}

		/**
		 * Escape a piece of data using the database specific 
		 * escape method
		 *
		 * @param	mixed			The data to escape
		 * @return	string			Escaped data
		 */
		public function escape($data)
		{
			if(!is_object($this->link))
			{
				return(false);
			}

			return($this->link->real_escape_string((string) $data));
		}

		/**
		 * Executes a query and returns the result on SELECT 
		 * statements
		 *
		 * @param	string			SQL to execute
		 * @param	mixed			Genetic parameter for formatting, if two or more parameters are passed to the method, the sql will be formatted using sprintf
		 * @return	boolean|object		Returns a result object on SELECT statements, and boolean true otherwise if the statement was executed
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the SQL should fail for whatever reason, an exception is thrown
		 */
		public function query($sql)
		{
			$sql = (string) $sql;

			if(empty($sql) || !is_object($this->link))
			{
				return(false);
			}
			elseif(func_num_args() > 1)
			{
				$args 		= func_get_args();
				$args[0]	= $sql;
				$sql 		= call_user_func_array('sprintf', $args);
			}

			EG('error_reporting', false);
			$query = $this->link->query($sql);
			EG('error_reporting', true);

			if($query === true)
			{
				$this->queries[] = $sql;

				return(true);
			}
			elseif(is_object($query))
			{
				$this->queries[] = $sql;

				return(new Tuxxedo_Database_Driver_MySQLi_Result($this, $query));
			}
			elseif($this->link->errno)
			{
				$format = 'Database error: failed to execute query';

				if(TUXXEDO_DEBUG)
				{
					$format = 'Failed to execute query: [%d] \'%s\' (SQLSTATE: %d)';
				}

				throw new Tuxxedo_Basic_Exception($format, $this->link->errno, $this->link->error, $this->link->sqlstate);
			}

			return(false);
		}
	}

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
	final class Tuxxedo_Database_Driver_MySQLi_Result extends Tuxxedo_Database_Result
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
			return(is_null($this->result));
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
				return($this->cached_num_rows);
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