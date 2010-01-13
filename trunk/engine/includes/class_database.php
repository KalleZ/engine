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
	 * Base database driver interface
	 *
	 * This defines the driver structure of which functions a driver 
	 * must implement and how they should be called.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface Tuxxedo_Database_Driver
	{
		/**
		 * Returns if the current system supports the  driver, if this 
		 * method isn't called, a driver may start shutting down or 
		 * throwing random exceptions unexpectedly
		 *
		 * @return	boolean			True if dirver is supported, otherwise false
		 */
		public function isDriverSupported();

		/**
		 * Connect to a database, if no connection isn't already 
		 * active
		 *
		 * @param	array			Change the configuration and use this new configuration to connect with
		 * @return	boolean			True if a successful connection was made
	 	 *
		 * @throws	Tuxxedo_Basic_Exception	If a database connection fails
		 */
		public function connect(Array $configuration = NULL);

		/**
		 * Close a database connection
		 *
		 * @return	boolean			True if the connection was closed, otherwise false
		 */
		public function close();

		/**
		 * Checks if a connection is active
		 *
		 * @return	boolean			True if a connection is currently active, otherwise false
		 */
		public function isConnected();

		/**
		 * Checks if a variable is a connection of the same type 
		 * as the one used by the driver
		 *
		 * @param	mixed			The variable to check
		 * @return	boolean			True if the variable type matches, otherwise false
		 */
		public function isLink($link);

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean			True if the connection is persistent, otherwise false
		 */
		public function isPersistent();

		/**
		 * Checks if a variable is a result of the same type as 
		 * the one used by the driver
		 *
		 * @param	mixed			The variable to check
		 * @return	boolean			True if the variable type matches, otherwise false
		 */
		public function isResult($result);

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string			The error message
		 */
		public function getError();

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer			The error number
		 */
		public function getErrno();

		/**
		 * Get the last insert id from last executed SELECT statement
		 *
		 * @return	integer			Returns the last insert id, and boolean false on error
		 */
		public function getInsertId();

		/**
		 * Get the number of affected rows from last INSERT INTO/UPDATE/DELETE 
		 * operation.
		 *
		 * @return	integer			Returns the number of affected rows, and 0 on error
		 */
		public function getAffectedRows($result);

		/**
		 * Escape a piece of data using the database specific 
		 * escape method
		 *
		 * @param	mixed			The data to escape
		 * @return	string			Escaped data
		 */
		public function escape($data);

		/**
		 * Executes a query and returns the result on SELECT 
		 * statements
		 *
		 * @param	string			SQL to execute
		 * @return	boolean|object		Returns a result object on SELECT statements, and boolean true otherwise if the statement was executed
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the SQL should fail for whatever reason, an exception is thrown
		 */
		public function query($sql);
	}

	/**
	 * Base database driver result interface
	 *
	 * This defines the driver structure of which functions a driver 
	 * must implement in the result object and how they should be called.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface Tuxxedo_Database_Driver_Result
	{
		/**
		 * Frees the result from memory, and makes it unusable
		 *
		 * @return	boolean			Returns true if the result was freed, otherwise false
		 */
		public function free();

		/**
		 * Checks whether the result is freed or not
		 *
		 * @return	boolean			Returns true if the result is freed from memory, otherwise false
		 */
		public function isFreed();

		/**
		 * Get the number of rows in the result
		 *
		 * @return	integer			Returns the number of rows in the result, and 0 on error
		 */
		public function getNumRows();

		/**
		 * Fetch result with both associative and indexed indexes array
		 *
		 * @return	array			Returns an array with the result
		 */
		public function fetchArray();

		/**
		 * Fetches the result and returns an associative array
		 *
		 * @return	array			Returns an associative array with the result
		 */
		public function fetchAssoc();

		/**
		 * Fetches the result and returns an indexed array
		 *
		 * @return	array			Returns an indexed array with the result
		 */
		public function fetchRow();

		/**
		 * Fetches the result and returns an object, with overloaded 
		 * properties for rows names
		 *
		 * @return	object			Returns an object with the result
		 */
		public function fetchObject();
	}

	/**
	 * Abstract database class
	 *
	 * Every main driver class must extend this class in order to be loadable 
	 * and to comply with the database access layer interface. This also contains 
	 * the factory method used to instanciate a new database driver instance.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	abstract class Tuxxedo_Database implements Tuxxedo_Database_Driver
	{
		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		mixed
		 */
		protected $link;

		/**
		 * List of executed queries during execution
		 *
		 * @var		array
		 */
		protected $queries			= Array();

		/**
		 * Database specific configuration array
		 *
		 * @var		array
		 */
		protected $configuration		= Array();

		/**
		 * List of shutdown queries that will be executed 
		 * when then destructor is called
		 *
		 * @var		array
		 */
		protected $shutdown_queries		= Array();

		/**
		 * List of loaded drivers used for caching in the 
		 * special required cases where more than one driver 
		 * have to be loaded
		 *
		 * @var		array
		 */
		protected static $loaded_drivers 	= Array();


		/**
		 * Default constructor for a new database instance
		 *
		 * @param	array			Database specific configuration array
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the database connection fails, a basic exception will be thrown
		 */
		public function __construct(Array $configuration)
		{
			if(get_class($this) == __CLASS__)
			{
				throw new Tuxxedo_Basic_Exception('Cannot call base constructor directly from a non-initalized instance');
			}

			$this->configuration = $configuration;

			if(!$this->isDriverSupported())
			{
				throw new Tuxxedo_Basic_Exception('Unable to load database driver, one or more of the driver dependencies is missing');
			}

			$this->connect();
		}

		/**
		 * Default destructor, this simply closes a database connection 
		 * without anything else. A driver may extend the destructor 
		 * to shutdown other required services.
		 */
		public function __destruct()
		{
			if(!$this->isConnected() || !sizeof($this->shutdown_queries))
			{
				return;
			}

			foreach($this->shutdown_queries as $n => $sql)
			{
				try
				{
					$this->query($sql);
				}
				catch(Tuxxedo_Basic_Exception $e)
				{
					if(Tuxxedo_DEBUG)
					{
						tuxxedo_doc_error($e);
					}
				}
			}

			$this->close();
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	Only thrown on poorly a configured database section in the configuration file
		 */
		final public static function invoke(Tuxxedo $tuxxedo, Array $configuration, Array $options)
		{
			if(!isset($configuration['database']) || !isset($configuration['database']['driver']))
			{
				throw new Tuxxedo_Basic_Exception('No database configuration found or no driver defined');
			}

			return(self::factory($configuration['database']['driver'], $configuration['database']));
		}

		/**
		 * Constructs a new database instance
		 *
		 * @param	string			Driver name
		 * @param	array			Database specific configuration array
		 * @return	Tuxxedo_Database		Returns a new database instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if loading of a driver should fail for some reason
		 */
		final public static function factory($driver, Array $configuration)
		{
			$driver 	= (string) $driver;
			$path		= TUXXEDO_DIR . '/includes/database/driver_' . $driver . '.php';
			$class		= 'Tuxxedo_Database_Driver_' . $driver;
			$result_class	= $class . '_Result';

			if(in_array($driver, self::$loaded_drivers))
			{
				return(new $class($configuration));
			}

			if(!is_file($path))
			{
				throw new Tuxxedo_Basic_Exception('Unable to find database driver file for \'%s\'', $driver);
			}

			require($path);

			if(!class_exists($class) || !class_exists($result_class))
			{
				throw new Tuxxedo_Basic_Exception('Corrupt database driver, driver class(es) not found for \'%s\'', $driver);
			}

			if(!is_subclass_of($class, __CLASS__) || !is_subclass_of($result_class, 'Tuxxedo_Database_Result'))
			{
				throw new Tuxxedo_Basic_Exception('Corrupt database driver, driver class(es) does not follow the driver specification');
			}

			self::$loaded_drivers[] = $driver;

			return(new $class($configuration));
		}

		/**
		 * Sets a new query to execute at shutdown
		 *
		 * @param	string			The SQL string to execute
		 * @return	void			No value is returned
		 */
		public function setShutdownQuery($sql)
		{
			if(func_num_args() > 1)
			{
				$args 		= func_get_args();
				$args[0]	= $sql;
				$sql 		= call_user_func_array('sprintf', $args);
			}

			$this->shutdown_queries[] = (string) $sql;
		}

		/**
		 * Gets the number of queries executed during this request
		 *
		 * @return	integer			Number of queries executed
		 */
		final public function getNumQueries()
		{
			return(sizeof($this->queries));
		}

		/**
		 * Gets the executed queries during this request
		 *
		 * @return	array			A list of executed SQL queries
		 */
		final public function getQueries()
		{
			return($this->queries);
		}
	}

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
	abstract class Tuxxedo_Database_Result implements Tuxxedo_Database_Driver_Result
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
		public function __construct(Tuxxedo_Database $instance, $result)
		{
			if(!$instance->isResult($result))
			{
				throw new Tuxxedo_Basic_Exception('Passed result resource is not a valid result');
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