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
	 * @subpackage		Core
	 *
	 * =============================================================================
	 */


	/**
	 * Core engine namespace, the only dependency from the core is the 
	 * exceptions namespace.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	namespace \Tuxxedo\Core;

	/**
	 * Registry class, this acts as a mixed singleton/registry 
	 * object.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	class Registry
	{
		/**
		 * Holds the main instance
		 *
		 * @var		\Tuxxedo\Core\Registry
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
		final private function __construct()
		{
		}

		/**
		 * Disable the ability to clone the object
		 */
		final private function __clone()
		{
		}

		/**
		 * Magic get method, this handles overloading of registered 
		 * instances
		 *
		 * @param	string					Instance name
		 * @return	object					Returns the object instance if it exists, otherwise boolean false
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
		 * @param	array					The configuration array, this is only needed first time this is called
		 * @return	\Tuxxedo\Core\Registry			An instance to the Tuxxedo object
		 */
		final public static function init(Array $configuration = NULL)
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
		 * use \Tuxxedo\Core;
		 *
		 * $registry = Core\Registry::init();
		 * $registry->register('test', 'Classname');
 		 *
		 * $test->Methodname(); // or $tuxxedo->test->Methodname();
		 * </code>
		 *
		 * @param	string					The name of this instance
		 * @param	string					The class to register, this must implement a 'magic' method called invoke to work
		 * @return	object					Returns a reference to the created instance
		 *
		 * @throws	Tuxxedo_Basic_Exception			This a basic exception if the class doesn't exists or implements the magic invoke method
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
			elseif(($ifaces = class_implements($class, true)) !== false && isset($ifaces['\Tuxxedo\Core\Interfaces\Invokable']))
			{
				$instance = call_user_func(Array($class, 'invoke'), $this, $this->configuration);
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
		 * @param	string					The name of the reference
		 * @param	mixed					The value of the reference
		 * @return	void					No value is returned
		 */
		public function set($refname, $reference)
		{
			$refname 		= strtolower($refname);
			$GLOBALS[$refname]	= $this->instances[$refname] = $reference;
		}	

		/**
		 * Gets a registered object instance
		 *
		 * @param	string				The name of the object to get
		 * @return	object				Returns an instance to the object and boolean false on error
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
	 	 * @return	array				Returns the configuration array if defined, otherwise false
		 */
		public static function getConfiguration()
		{
			if(isset(self::$instance->configuration))
			{
				return(self::$instance->configuration);
			}

			return(false);
		}

		/**
		 * Sets or gets a new global
		 *
		 * @param	string					The name of the variable to set
		 * @param	mixed					A value, this can be of any type, this is only used if adding or editing a variable
		 * @return	mixed					Returns the value of variable on both set and get, and boolean false if trying to get an undefined variable
		 */
		public static function globals($name, $value = NULL)
		{
			return(self::symtable('globals', $name, $value));
		}

		/**
		 * Sets or gets a new value in a symbol table
		 *
		 * @param	string					The name of the symbol table to read from/write to
		 * @param	string					The name of the variable to set
		 * @param	mixed					A value, this can be of any type, this is only used if adding or editing a variable
		 * @return	mixed					Returns the value of variable on both set and get, and boolean false if trying to get an undefined variable
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
?>