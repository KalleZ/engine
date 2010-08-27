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

	namespace Tuxxedo;
	use Tuxxedo\Exception;

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
	abstract class Database implements Database\Driver, Invokable
	{
		/**
		 * Link pointer, this contains the internal link 
		 * to the database from the driver
		 *
		 * @var		mixed
		 */
		protected $link;

		/**
		 * Whether the database connection still is delayed
		 * or not
		 *
		 * @var		boolean
		 */
		protected $delayed			= true;

		/**
		 * List of executed queries during execution
		 *
		 * @var		array
		 */
		protected $queries			= array();

		/**
		 * Database specific configuration array
		 *
		 * @var		array
		 */
		protected $configuration		= array();

		/**
		 * List of shutdown queries that will be executed 
		 * when then destructor is called
		 *
		 * @var		array
		 */
		protected $shutdown_queries		= array();

		/**
		 * List of loaded drivers used for caching in the 
		 * special required cases where more than one driver 
		 * have to be loaded
		 *
		 * @var		array
		 */
		protected static $loaded_drivers 	= array();


		/**
		 * Default constructor for a new database instance
		 *
		 * @param	array			Database specific configuration array
		 *
		 * @throws	Tuxxedo_Basic_Exception	If the database connection fails, a basic exception will be thrown
		 */
		public function __construct(array $configuration)
		{
			if(get_class($this) == __CLASS__)
			{
				throw new Exception\Basic('Cannot call base constructor directly from a non-initalized instance');
			}

			$this->configuration 	= $configuration;
			$this->delayed		= $configuration['delay'];

			if(!$this->isDriverSupported())
			{
				throw new Exception\Basic('Unable to load database driver, one or more of the driver dependencies is missing');
			}

			if(!$configuration['delay'])
			{
				$this->connect();
			}
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
				catch(Exception\SQL $e)
				{
					tuxxedo_doc_error($e);
				}
			}
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
		final public static function invoke(Registry $registry, array $configuration, array $options)
		{
			if(!isset($configuration['database']) || !isset($configuration['database']['driver']))
			{
				throw new Exception\Basic('No database configuration found or no driver defined');
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
		final public static function factory($driver, array $configuration)
		{
			if(in_array($driver, self::$loaded_drivers))
			{
				return(new $class($configuration));
			}

			$class 		= 'Database\Driver\\' . $driver;
			$instance 	= new $class($configuration);

			if(!is_subclass_of($class, __CLASS__) || !is_subclass_of($class . '_Result', 'Database\Result'))
			{
				throw new Exception\Basic('Corrupt database driver, driver classes does not follow the driver specification');
			}

			self::$loaded_drivers[] = $driver;

			return($instance);
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
