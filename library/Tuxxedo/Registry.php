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
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Registry class, this acts as a mixed singleton/registry 
	 * object.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Registry
	{
		/**
		 * Holds the main instance
		 *
		 * @var		\Tuxxedo\Registry
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
		 * Allows the usage of isset() on registry objects
		 *
		 * @param	string			The registry object to check
		 * @return	boolean			Returns true if the object exists otherwise false
		 */
		public function __isset($name)
		{
			return(isset(self::$instance->instances[$name]) && !empty(self::$instance->instances[$name]));
		}

		/**
		 * Allows the usge of unset() on registry objects
		 *
		 * @param	string			The registry object to unload
		 * @return	void			No value is returned
		 *
		 * @since	1.1.0
		 */
		public function __unset($name)
		{
			if(isset(self::$instance->instances[$name]))
			{
				unset(self::$instance->instances[$name]);
			}
		}

		/**
		 * Unloads a registry object
		 *
		 * @param	string			The registry object to unload
		 * @return	void			No value is returned
		 *
		 * @since	1.1.0
		 */
		public function unload($name)
		{
			self::$instance->__unset($name);
		}

		/**
		 * Initializes a new object instance, this implements the 
		 * singleton pattern and can be called from any context and 
		 * the same object is returned
		 *
		 * @param	array					The configuration array, this is only needed first time this is called
		 * @return	\Tuxxedo\Registry			An instance to the Tuxxedo object
		 */
		final public static function init(Array $configuration = NULL)
		{
			if(!(self::$instance instanceof self))
			{
				self::$instance = new self;
			}

			if(\is_array($configuration))
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
		 * use Tuxxedo\Registry;
		 *
		 * $registry = Registry::init();
		 * $registry->register('test', '\Full\Class\Path');
 		 *
		 * $test->Methodname(); // or $registry->test->Methodname();
		 * </code>
		 *
		 * Note that the class must be a full path, and not an alias as 
		 * the class is instanciated inside the registry, not the scope 
		 * where custom aliasing rules may be defined.
		 *
		 * @param	string					The name of this instance
		 * @param	string					The class to register, this supports the 'Invokable' interface
		 * @return	object					Returns a reference to the created instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic		Throws a basic exception in case of failure
		 */
		public function register($refname, $class)
		{
			if(isset($this->instances[$refname]) && $this->instances[$refname] instanceof $class)
			{
				return($this->instances[$refname]);
			}

			$instance = $this->invoke($class);

			$this->set($refname, $instance);

			return($instance);
		}

		/**
		 * Invokes a class, calling its preloading method if available
		 * and then returns the instance object
		 *
		 * @param	string					The class to register, this supports the 'Invokable' interface
		 * @return	object					Returns a reference to the created instance
		 *
		 * @since	1.1.0
		 */
		public function invoke($class)
		{
			if(!\class_exists($class))
			{
				throw new Exception\Basic('Passed object class (%s) does not exists', $class);
			}
			elseif(($ifaces = \class_implements($class, true)) !== false && isset($ifaces['Tuxxedo\Design\Invokable']))
			{
				$instance = \call_user_func(Array($class, 'invoke'), $this, $this->configuration);

				if(\is_object($class) && !\is_object($instance))
				{
					$instance = $class;
				}
			}

			if(!isset($instance) || !\is_object($instance))
			{
				$instance = new $class;
			}

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
			$refname 		= \strtolower($refname);
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
		protected static function symtable($symtable, $name, $value = NULL)
		{
			if(!self::$instance)
			{
				return;
			}

			if($value !== NULL)
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