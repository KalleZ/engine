<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Main Tuxxedo class, this acts as a mixed singleton/registry 
	 * object.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	final class Tuxxedo
	{
		/**
		 * Tuxxedo simple version, this contains the current 
		 * release in the form of:
		 *
		 * major.minor.release
		 *
		 * For example, 1.0, 1.0.1 ect.
	 	 *
		 * @var		string
		 */
		const VERSION			= '1.0.0';

		/**
		 * Major version number
		 *
		 * @var		integer
		 */
		const VERSION_MAJOR		= 1;

		/**
		 * Minor version number
		 *
		 * @var		integer
		 */
		const VERSION_MINOR		= 0;

		/**
		 * Release version number
		 *
		 * @var		integer
		 */
		const VERSION_RELEASE		= 0;

		/**
		 * Tuxxedo version ID, this contains the version id in the form 
		 * of:
		 *
		 * id = (major_version * 10000) + (minor_version * 100) + release_version
		 *
		 * Examples of the version id string can be:
		 *
		 * 1.0.0	10000
		 * 1.1.0	10100
		 * 1.2.2	10202
		 *
		 * @var		integer
		 */
		const VERSION_ID		= 10000;

		/**
		 * Tuxxedo version string, this is the full version string, which 
		 * includes the pre-release name, version and the version number 
		 * of the upcoming version if pre-release. For example:
		 *
		 * 1.0.0 Alpha 1
		 * 1.0.3 Release Candidate 2
		 * 1.0.4
		 *
		 * @var		string
		 */
		const VERSION_STRING		= '1.0.0 (development preview)';


		/**
		 * Holds the main instance
		 *
		 * @var		Tuxxedo
		 */
		private static $instance;

		/**
		 * Holds the configuration array
		 *
		 * @var		array
		 */
		private $configuration		= Array();

		/**
		 * Holds an array of the instances registered
		 *
		 * @var		array
		 */
		private $instances		= Array();


		/**
		 * Holds the list of global variables across 
		 * Engine
		 *
		 * @var		array
		 */
		private $globals		= Array();

		/**
		 * Holds a list of components that can be loaded 
		 * without specifying the class or reference point 
		 * like the register and set method does
		 *
		 * @var		array
		 */
		private $components		= Array(
							'db'			=> 'Tuxxedo_Database', 
							'cache'			=> 'Tuxxedo_Datastore', 
							'filter'		=> 'Tuxxedo_Filter', 
							'intl'			=> 'Tuxxedo_Internationalization', 
							'style'			=> 'Tuxxedo_Style'
							);

		/**
		 * Disable the ability to construct the object
		 */
		private function __construct()
		{
		}

		/**
		 * Disable the ability to clone the object
		 */
		private function __clone()
		{
		}

		/**
		 * Magic get method, this handles overloading of registered 
		 * instances
		 *
		 * @param	string			Instance name
		 * @return	object			Returns the object instance if it exists, otherwise boolean false
		 */
		public function __get($name)
		{
			if(isset(self::$instance->instances[$name]))
			{
				return(self::$instance->instances[$name]);
			}

			return(false);
		}

		/**
		 * Initializes a new object instance, this implements the 
		 * singleton pattern and can be called from any context and 
		 * the same object is returned
		 *
		 * @param	array			The configuration array, this is only needed first time this is called
		 * @return	Tuxxedo			An instance to the Tuxxedo object
		 */
		public static function init(Array $configuration = NULL)
		{
			if(!(self::$instance instanceof self))
			{
				self::$instance = new self;
			}

			if(is_array($configuration))
			{
				self::$instance->configuration = $configuration;
			}

			return(self::$instance);
		}

		/**
		 * Registers a new instance and makes it accessable through 
		 * the name defined by the first parameter in the global scope 
		 * like the example below:
		 *
		 * <code>
		 * $tuxxedo = Tuxxedo::init();
		 * $tuxxedo->register('test', 'Classname');
 		 *
		 * $test->Methodname(); // or $tuxxedo->test->Methodname();
		 * </code>
		 *
		 * @param	string			The name of this instance
		 * @param	string			The class to register, this must implement a 'magic' method called invoke to work
		 * @return	object			Returns a reference to the created instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	This a basic exception if the class doesn't exists or implements the magic invoke method
		 */
		public function register($refname, $class)
		{
			if(isset($this->instances[$refname]))
			{
				return;
			}
			elseif(!class_exists($class))
			{
				throw new Tuxxedo_Basic_Exception('Passed object class (%s) does not exists', $class);
			}
			elseif(($ifaces = class_implements($class, true)) !== false && isset($ifaces['Tuxxedo_Invokable']))
			{
				$instance = call_user_func(Array($class, 'invoke'), $this, $this->configuration, (array) self::getOptions());
			}

			if(!isset($instance) || !is_object($instance))
			{
				$instance = new $class;
			}

			$this->set($refname, $instance);

			return($instance);
		}

		/**
		 * Sets a new reference in the registry
		 *
		 * @param	string			The name of the reference
		 * @param	mixed			The value of the reference
		 * @return	void			No value is returned
		 */
		public function set($refname, $reference)
		{
			$refname 		= strtolower($refname);
			$GLOBALS[$refname]	= $this->instances[$refname] = $reference;
		}

		/**
		 * Loads a component, or multiple if the component is an array with 
		 * component names as values
		 *
		 * @param	string		The component name, either builtin or user defined thru the component static method
		 * @param	boolean		Whether to re-use an already existing instance, defaults to true
		 * @param	object		Returns an array with values as references to the created instances
		 */
		public function load($loadable, $reuse = true)
		{
			$multiple	= is_array($loadable);
			$retval 	= Array();

			if(!$multiple)
			{
				$loadable = Array($loadable);
			}

			foreach($loadable as $component)
			{
				if($reuse && isset($this->instances[$component]))
				{
					$retval[] = $this->instances[$component];
				}
				elseif(isset($this->components[$component]))
				{
					$retval[] = $this->register($component, $this->components[$component]);
				}
			}

			if(!$multiple)
			{
				$retval = $retval[0];
			}

			return($retval);
		}	

		/**
		 * Gets a registered object instance
		 *
		 * @param	string		The name of the object to get
		 * @return	object		Returns an instance to the object and boolean false on error
		 */
		public static function get($obj)
		{
			if(isset(self::$instance->instances[$obj]))
			{
				return(self::$instance->instances[$obj]);
			}

			return(false);
		}

		/**
		 * Gets the configuration array
		 *
	 	 * @return	array		Returns the configuration array if defined, otherwise false
		 */
		public static function getConfiguration()
		{
			if(isset(self::$instance->configuration))
			{
				return(self::$instance->configuration);
			}
			elseif(isset($GLOBALS['configuration']))
			{
				return($GLOBALS['configuration']);
			}

			return(false);
		}

		/**
		 * Gets the options from the datastore
		 *
	 	 * @return	array		Returns an array if the datastore is loaded and the options are cached, otherwise false
		 */
		public static function getOptions()
		{
			static $options;

			if(is_array($options) || isset(self::$instance->instances['cache']) && ($options = self::$instance->instances['cache']->options) !== false)
			{
				return($options);
			}

			return(false);
		}

		/**
		 * Sets or gets a new global
		 *
		 * @param	string			The name of the variable to set
		 * @param	mixed			A value, this can be of any type, this is only used if adding or editing a variable
		 * @return	mixed			Returns the value of variable on both set and get, and boolean false if trying to get an undefined variable
		 */
		public static function globals($name, $value = NULL)
		{
			return(self::symtable('globals', $name, $value));
		}

		/**
		 * Sets or gets a new component
		 *
		 * @param	string			The name of the component to set
		 * @param	mixed			The class associated with the name above
		 * @return	mixed			Returns the class value on both set and get, and boolean false if trying to get an undefined comonent
		 */
		public static function component($component, $class = NULL)
		{
			if($class != NULL && !class_exists($class, false))
			{
				return(false);
			}

			return(self::symtable('components', $component, $class));
		}

		/**
		 * Sets or gets a new value in a symbol table
		 *
		 * @param	string			The name of the symbol table to read from/write to
		 * @param	string			The name of the variable to set
		 * @param	mixed			A value, this can be of any type, this is only used if adding or editing a variable
		 * @return	mixed			Returns the value of variable on both set and get, and boolean false if trying to get an undefined variable
		 */
		private static function symtable($symtable, $name, $value = NULL)
		{
			if($value != NULL)
			{
				self::$instance->{$symtable}[$name] = $value;
			}
			elseif(!isset(self::$instance->{$symtable}[$name]))
			{
				return(false);
			}

			return(self::$instance->{$symtable}[$name]);
		}
	}

	/**
	 * Interface for requring the registry to pass certain information 
	 * before the constructor is called.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface Tuxxedo_Invokable
	{
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
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration, Array $options);
	}

	/**
	 * Information access, enables the ability for classes 
	 * to access their loaded information through the array-alike 
	 * syntax.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	abstract class Tuxxedo_InfoAccess implements ArrayAccess
	{
		/**
		 * Information array
		 * 
		 * @var		array
		 */
		protected $information		= Array();


		/**
		 * Checks whether an information is available 
		 *
		 * @param	scalar			The information row name to check
		 * @return	boolean			Returns true if the information is stored, otherwise false
		 */
		public function offsetExists($offset)
		{
			if(is_object($this->information))
			{
				return(isset($this->information->{$offset}));
			}

			return(isset($this->information[$offset]));
		}

		/**
		 * Gets a value from the information store
		 * 
		 * @param	scalar			The information row name to get
		 * @return	mixed			Returns the information value, and NULL if the value wasn't found
		 */
		public function offsetGet($offset)
		{
			if(is_object($this->information))
			{
				return($this->information->{$offset});
			}
			else
			{
				return($this->information[$offset]);
			}
		}

		/**
		 * Sets a new information value
		 *
		 * @param	scalar			The information row name to set
		 * @param	mixed			The new/update value for this row
		 * @return	void			No value is returned
		 */
		public function offsetSet($offset, $value)
		{
			if(is_object($this->information))
			{
				$this->information->{$offset} = $value;
			}
			else
			{
				$this->information[$offset] = $value;
			}
		}

		/**
		 * Deletes an information value
		 *
		 * @param	scalar			The information row name to delete
		 * @return	void			No value is returned
		 */
		public function offsetUnset($offset)
		{
			if(is_object($this->information))
			{
				unset($this->information->{$offset});
			}
			else
			{
				unset($this->information[$offset]);
			}
		}
	}

	/**
	 * Autoloader mapping class, this class provides the ability to 
	 * map custom classes, or redirect class locations at runtime. 
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Autoloader
	{
		/**
		 * Class autoloader mappings
		 *
		 * @var		array
		 */
		protected static $classes	= Array(
							/* Core classes, always available */
							'tuxxedo'					=> 'core', 
							'tuxxedo_invokable'				=> 'core', 
							'tuxxedo_infoaccess'				=> 'core', 
							'tuxxedo_autoloader'				=> 'core', 

							/* Exceptions */
							'tuxxedo_exception'				=> 'exceptions', 
							'tuxxedo_formdata_exception'			=> 'exceptions', 
							'tuxxedo_named_formdata_exception'		=> 'exceptions', 
							'tuxxedo_basic_exception'			=> 'exceptions', 

							/* Database core */
							'tuxxedo_database'				=> 'database', 
							'tuxxedo_database_result'			=> 'database', 
							'tuxxedo_database_driver'			=> 'database', 
							'tuxxedo_database_driver_result'		=> 'database', 
							'tuxxedo_sql_exception'				=> 'database', 

							/* Caching utilities */
							'tuxxedo_datastore'				=> 'cache', 

							/* Data filtering */
							'tuxxedo_filter'				=> 'filter', 

							/* Data managers */
							'tuxxedo_datamanager'				=> 'datamanager', 
							'tuxxedo_datamanager_api_cache'			=> 'datamanager', 
							
							/* Configuration */
							'tuxxedo_config'				=> 'config', 
							'tuxxedo_config_xml'				=> 'config', 

							/* Style API */
							'tuxxedo_style'					=> 'styles', 
							'tuxxedo_style_storage'				=> 'styles', 
							'tuxxedo_style_storage_database'		=> 'styles', 
							'tuxxedo_style_storage_filesystem'		=> 'styles', 

							/* Template compiler */
							'tuxxedo_template_compiler'			=> 'template_compiler', 
							'tuxxedo_template_compiler_exception'		=> 'template_compiler', 
							'tuxxedo_template_compiler_dummy'		=> 'template_compiler', 

							/* Internationalization API */
							'tuxxedo_internationalization'			=> 'internationalization', 
							'tuxxedo_internationalization_phrasegroup'	=> 'internationalization', 

							/* Users and sessions API */
							'tuxxedo_session'				=> 'session', 
							'tuxxedo_user'					=> 'user',
							
							/* MVC components */
							'tuxxedo_controller'				=> 'controller',
							'tuxxedo_router'				=> 'router',
							'tuxxedo_router_uri'				=> 'router',
							'tuxxedo_model'					=> 'model'
							);

		/**
		 * Driver autoloader mappings
		 *
		 * @var		array
		 */
		protected static $drivers	= Array(
							/* Database drivers */
							'tuxxedo_database_driver_mysql'			=> Array('database', 'mysql'), 
							'tuxxedo_database_driver_mysql_result'		=> Array('database', 'mysql'), 
							'tuxxedo_database_driver_mysqli'		=> Array('database', 'mysqli'), 
							'tuxxedo_database_driver_mysqli_result'		=> Array('database', 'mysqli'), 
							'tuxxedo_database_driver_pdo'			=> Array('database', 'pdo'), 
							'tuxxedo_database_driver_pdo_result'		=> Array('database', 'pdo'), 

							/* Data managers */
							'tuxxedo_datamanager_session'			=> Array('datamanagers', 'session'), 
							'tuxxedo_datamanager_style'			=> Array('datamanagers', 'style'), 
							'tuxxedo_datamanager_user'			=> Array('datamanagers', 'user'), 
							'tuxxedo_datamanager_usergroup'			=> Array('datamanagers', 'usergroup')
							);


		/**
		 * Defines a new class mapping
		 *
		 * @param	string			The name of the class to add to the autoloader
		 * @param	string			The file name in the includes directory prefixed with class to be autoloaded
		 * @return	boolean			True if the file was added to the map, otherwise false
		 */
		public static function setClassMap($class, $file)
		{
			if(empty($class) || empty($file))
			{
				return(false);
			}

			self::$classes[strtolower($class)] = $file;

			return(true);
		}

		/**
		 * Defines a new driver mapping
		 *
		 * @param	string			The name of the driver class to add to the autoloader
		 * @param	string			The name of the driver, aka. the directory within the includes folder to load from
		 * @param	string			The name of the driver file
		 * @param	string			Optionally a file name prefix, if specified, then while loading it will be suffixed with an underscore
		 * @return	boolean			True if the file was added to the map, otherwise false
		 */
		public static function setDriverMap($class, $driver, $file)
		{
			if(empty($class) || empty($driver) || empty($file))
			{
				return(false);
			}

			self::$drivers[strtolower($class)] = Array($driver, $file);

			return(true);
		}

		/**
		 * Attempts to autoload a class
		 *
		 * @param	string			The class to autoload
		 * @return	void			No value is returned
		 */
		public static function load($class)
		{
			if(class_exists($class, false))
			{
				return;
			}

			$class = strtolower($class);

			if(isset(self::$drivers[$class]))
			{
				require(TUXXEDO_LIBRARY . '/' . self::$drivers[$class][0] . '/' . self::$drivers[$class][1] . '.php');

				return;
			}
			elseif(isset(self::$classes[$class]))
			{
				require(TUXXEDO_LIBRARY . '/' . self::$classes[$class] . '.php');

				return;
			}

			require(TUXXEDO_LIBRARY . '/' . $class . '.php');
		}
	}
?>
