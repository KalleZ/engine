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
	use Tuxxedo\Database\Driver\Pdo;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
	 * @subpackage		Library
	 */
	class Pdo extends Database
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
				$supported = \extension_loaded('pdo') && \extension_loaded('pdo_' . \strtolower($this->configuration['subdriver']));
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
							'extension'	=> \extension_loaded('pdo'), 
							'subdriver'	=> \extension_loaded('pdo_' . \strtolower($this->configuration['subdriver']))
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

			if(\is_object($this->link))
			{
				return(true);
			}

			$unix_socket = '';

			switch($this->configuration['subdriver'] = \strtolower($this->configuration['subdriver']))
			{
				case('mysql'):
				{
					if($unix_socket = $this->configuration['socket'])
					{
						$unix_socket = \sprintf('unix_socket=%s;', $unix_socket);
					}
				}
				break;
			}

			if($port = $this->configuration['port'])
			{
				$port = \sprintf('port=%d;', $port);
			}

			if($this->configuration['subdriver'] == 'sqlite')
			{
				$dsn = \sprintf('sqlite:%s', $this->configuration['database']);
			}
			else
			{
				$dsn = \sprintf('%s:host=%s;dbname=%s;%s%s%s', ($prefix = $this->configuration['dsnprefix'] ? $prefix : $this->configuration['subdriver']), $this->configuration['hostname'], $this->configuration['database'], $port, $unix_socket, $configuration['dsnsuffix']);
			}

			try
			{
				$link = new \PDO($dsn, $this->configuration['username'], $this->configuration['password'], Array(
																	\PDO::ATTR_ERRMODE	=> \PDO::ERRMODE_EXCEPTION, 
																	\PDO::ATTR_PERSISTENT 	=> $this->configuration['persistent'], 
																	\PDO::ATTR_TIMEOUT	=> $this->configuration['timeout']
																));
			}
			catch(\PDOException $e)
			{
				$format = 'Database error: failed to connect database';

				if(\TUXXEDO_DEBUG)
				{
					$format = 'Database error: [%d] %s';
				}

				throw new Exception\Basic($format, $e->getCode(), $e->getMessage());
			}

			$this->persistent 	= $link->getAttribute(\PDO::ATTR_PERSISTENT);
			$this->link 		= $link;
		}

		/**
		 * Close a database connection
		 *
		 * @return	boolean					True if the connection was closed, otherwise false
		 */
		public function close()
		{
			if(\is_object($this->link))
			{
				$this->link = NULL;

				return(true);
			}

			return(false);
		}

		/**
		 * Checks if a connection is active
		 *
		 * @return	boolean					True if a connection is currently active, otherwise false
		 */
		public function isConnected()
		{
			return(\is_object($this->link));
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
			return(\is_object($link) && $link instanceof \PDO);
		}

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean				True if the connection is persistent, otherwise false
		 */
		public function isPersistent()
		{
			return($this->persistent);
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
			return(\is_object($result) && $result instanceof \PDOStatement);
		}

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string				The error message
		 */
		public function getError()
		{
			if(!\is_object($this->link))
			{
				return(false);
			}

			$errorinfo = $this->link->errorInfo();

			return(isset($errorinfo[2]) ? $errorinfo[2] : 'Unknown error');
		}

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer				The error number
		 */
		public function getErrno()
		{
			if(!\is_object($this->link))
			{
				return(false);
			}


			$errorinfo = $this->link->errorInfo();

			return(isset($errorinfo[1]) ? $errorinfo[1] : 0);
		}

		/**
		 * Get the last insert id from last executed SELECT statement
		 *
		 * @return	integer				Returns the last insert id, and boolean false on error
		 */
		public function getInsertId()
		{
			if(!\is_object($this->link))
			{
				return(false);
			}

			return((integer) $this->link->lastInsertId());
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

			if(!\is_object($this->link))
			{
				return(false);
			}
			elseif(!\method_exists($this->link, 'quote'))
			{
				throw new Exception\Basic('PDO driver does not implement an safe escaping method');
			}

			return(\substr($this->link->quote($data), 1, -1));
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

			if(empty($sql) || !\is_object($this->link))
			{
				return(false);
			}
			elseif(\func_num_args() > 1)
			{
				$sql = \call_user_func_array('\sprintf', \func_get_args());
			}

			try
			{
				$query = $this->link->query($sql);
			}
			catch(\PDOException $e)
			{
				throw new Exception\SQL($sql, self::DRIVER_NAME, $e->getMessage(), $e->getCode());
			}

			if($query !== false && (!$query->columnCount() && $query->num_rows))
			{
				$this->queries[] = $sql;

				return(true);
			}
			elseif($query instanceof \PDOStatement && $query->columnCount())
			{
				$this->queries[] = $sql;

				return(new Pdo\Result($this, $query));
			}

			if($query instanceof \PDOStatement)
			{
				$this->affected_rows = (integer) $query->rowCount();

				return(true);
			}

			return(false);
		}
	}
?>