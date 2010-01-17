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
	 * PDO abstraction driver for Tuxxedo Engine
	 *
	 * This driver lets you use PDO as backend instead of the vendor 
	 * specific extensions. Any loaded PDO driver may be used to create 
	 * a new instance.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	final class Tuxxedo_Database_Driver_PDO extends Tuxxedo_Database
	{
		/**
		 * Driver name
		 *
		 * @var		string
		 */
		const DRIVER_NAME		= 'pdo';


		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		PDO
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
			return(extension_loaded('pdo') && extension_loaded('pdo_' . strtolower($this->configuration['subdriver'])));
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

			$unix_socket = '';

			switch(strtolower($this->configuration['subdriver']))
			{
				case('mysql'):
				{
					if($unix_socket = $this->configuration['socket'])
					{
						$unix_socket = sprintf('unix_socket=%s;', $unix_socket);
					}
				}
				break;
			}

			if($port = $this->configuration['port'])
			{
				$port = sprintf('port=%d;', $port);
			}

			$dsn = sprintf('%s:host=%s;dbname=%s;%s%s%s', ($prefix = $this->configuration['dsnprefix'] ? $prefix : $this->configuration['subdriver']), $this->configuration['hostname'], $this->configuration['database'], $port, $unix_socket, $configuration['dsnsuffix']);

			try
			{
				$link = new PDO($dsn, $this->configuration['username'], $this->configuration['password'], Array(
																PDO::ATTR_ERRMODE	=> PDO::ERRMODE_EXCEPTION, 
																PDO::ATTR_PERSISTENT 	=> $this->configuration['persistent'], 
																PDO::ATTR_TIMEOUT	=> $this->configuration['timeout']
																));
			}
			catch(PDOException $e)
			{
				$format = 'Database error: failed to connect database';

				if(TUXXEDO_DEBUG)
				{
					$format = 'Database error: [%d] %s';
				}

				throw new Tuxxedo_Basic_Exception($format, $e->getCode(), $e->getMessage());
			}

			$this->persistent 	= $link->getAttribute(PDO::ATTR_PERSISTENT);
			$this->link 		= $link;
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
				$this->link = NULL;

				return(true);
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
			return(is_object($link) && $link instanceof PDO);
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
			return(is_object($result) && $result instanceof PDOStatement);
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

			$errorinfo = $this->link->errorInfo();

			return(isset($errorinfo[2]) ? $errorinfo[2] : 'Unknown error');
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


			$errorinfo = $this->link->errorInfo();

			return(isset($errorinfo[1]) ? $errorinfo[1] : 0);
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

			return((integer) $this->link->lastInsertId());
		}

		/**
		 * Get the number of affected rows from last INSERT INTO/UPDATE/DELETE 
		 * operation.
		 *
		 * @param	Tuxxedo_Database_Result	The result used to determine how many affected rows there were
		 * @return	integer			Returns the number of affected rows, and 0 on error
		 */
		public function getAffectedRows($result)
		{
			if(!$this->isResult($result) || $result->isFreed())
			{
				return(0);
			}

			return($result->rowCount());
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

			return(addslashes($data));
		}

		/**
		 * Executes a query and returns the result on SELECT 
		 * statements
		 *
		 * @param	string			SQL to execute
		 * @param	mixed			Genetic parameter for formatting, if two or more parameters are passed to the method, the sql will be formatted using sprintf
		 * @return	boolean|object		Returns a result object on SELECT statements, and boolean true otherwise if the statement was executed
		 *
		 * @throws	Tuxxedo_SQL_Exception	If the SQL should fail for whatever reason, an exception is thrown
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

			try
			{
				$query = $this->link->query($sql);
			}
			catch(PDOException $e)
			{
				throw new Tuxxedo_SQL_Exception($e->getMessage(), $e->getCode());
			}

			if($query !== false && (!$query->columnCount() && $query->num_rows))
			{
				$this->queries[] = $sql;

				return(true);
			}
			elseif($query instanceof PDOStatement && $query->columnCount())
			{
				$this->queries[] = $sql;

				return(new Tuxxedo_Database_Driver_PDO_Result($this, $query));
			}

			return(false);
		}
	}

	/**
	 * PDO abstraction result driver for Tuxxedo
	 *
	 * This implements the result class for PDO based subdrivers for 
	 * Tuxxedo, this contains methods to fetch, count result rows and 
	 * such for working with a resultset.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	final class Tuxxedo_Database_Driver_PDO_Result extends Tuxxedo_Database_Result
	{
		/**
		 * The result resource
		 *
		 * @var		PDOStatement
		 */
		protected $result;


		/**
		 * Constructs a new result object
		 *
		 * @param	Tuxxedo_Database	A database instance
		 * @param	mixed			A database result, this must be delivered from the driver it was created from
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the result passed is from a different driver type, or if the result does not contain any results
		 */
		public function __construct(Tuxxedo_Database $instance, $result)
		{
			if(!$instance->isResult($result))
			{
				throw new Tuxxedo_Basic_Exception('Passed result resource is not a valid result');
			}

			$this->instance		= $instance;
			$this->result		= $result;
			$this->cached_num_rows 	= $result->columnCount();

			if(!$this->cached_num_rows)
			{
				$this->result = NULL;
			}
		}

		/**
		 * Frees the result from memory, and makes it unusable
		 *
		 * @return	boolean			Returns true if the result was freed, otherwise false
		 */
		public function free()
		{
			if(is_object($this->result))
			{
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

			return((integer) $this->result->rowCount());
		}

		/**
		 * Fetch result with both associative and indexed indexes array
		 *
		 * @return	array			Returns an array with the result
		 */
		public function fetchArray()
		{
			return($this->result->fetch());
		}

		/**
		 * Fetches the result and returns an associative array
		 *
		 * @return	array			Returns an associative array with the result
		 */
		public function fetchAssoc()
		{
			return($this->result->fetch(PDO::FETCH_ASSOC));
		}

		/**
		 * Fetches the result and returns an indexed array
		 *
		 * @return	array			Returns an indexed array with the result
		 */
		public function fetchRow()
		{
			return($this->result->fetch(PDO::FETCH_NUM));
		}

		/**
		 * Fetches the result and returns an object, with overloaded 
		 * properties for rows names
		 *
		 * @return	object			Returns an object with the result
		 */
		public function fetchObject()
		{
			return($this->result->fetchObject());
		}
	}
?>