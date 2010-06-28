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
		const VERSION			= '1.1.0';

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
		const VERSION_ID		= 10100;

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
		const VERSION_STRING		= '1.1.0 (development preview)';


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
			if(isset(self::$instance->instances[$refname]))
			{
				return;
			}
			elseif(!class_exists($class))
			{
				throw new Tuxxedo_Basic_Exception('Passed object class (%s) does not exists', $class);
			}
			elseif(method_exists($class, 'invoke'))
			{
				$instance = call_user_func(Array($class, 'invoke'), self::$instance, self::$instance->configuration, (array) self::getOptions());
			}

			if(!isset($instance) || !is_object($instance))
			{
				$instance = new $class;
			}

			self::$instance->set($refname, $instance);

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
			$GLOBALS[$refname]	= self::$instance->instances[$refname] = $reference;
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
			if(func_num_args() > 1)
			{
				self::$instance->globals[$name] = $value;
			}
			elseif(!isset(self::$instance->globals[$name]))
			{
				return(false);
			}

			return(self::$instance->globals[$name]);
		}
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
?>