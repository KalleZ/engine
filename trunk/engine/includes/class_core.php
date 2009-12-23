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
		const VERSION			= '1.0.0';

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
		const VERSION_ID		= '10000';

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
		const VERSION_STRING		= '1.0.0 Release Candidate 7 (dev)';

		/**
		 * For if we're in debug mode, this must be uncommented in order to 
		 * disable debug mode.
		 *
		 * @var		boolean
		 */
		const DEBUG			= true;


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
			if(array_key_exists($name, self::$instance->instances))
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
			if(!is_object(self::$instance))
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
		 * @return	void			No value is returned
		 *
		 * @throws	Tuxxedo_Basic_Exception	This a basic exception if the class doesn't exists or implements the magic invoke method
		 */
		public function register($refname, $class)
		{
			if(array_key_exists($refname, self::$instance->instances))
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

			self::$instance->set($refname, (isset($instance) ? $instance : new $class));
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
			if(!array_key_exists($obj, self::$instance->instances))
			{
				return(false);
			}

			return(self::$instance->instances[$obj]);
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

			if(is_array($options) || isset(self::$instance->instances['cache']) && ($options = self::$instance->instances['cache']->fetch('options')))
			{
				return($options);
			}

			return(false);
		}
	}

	/**
	 * Default exception, mainly used for general errors. All 
	 * Tuxxedo specific exceptions extend this exception.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Exception extends Exception
	{
		/**
		 * Indicates whenever this is a fatal error or not
		 *
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct()
		{
			$args = func_get_args();

			if(!sizeof($args))
			{
				$args[0] = 'Unknown error';
			}

			parent::__construct(call_user_func_array('sprintf', $args));
		}
	}

	/**
	 * Form data exception, this exception is used to carry form data 
	 * so it can be displayed in a form if an error should occur while 
	 * processing the request
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_FormData_Exception extends Tuxxedo_Exception
	{
		/**
		 * Holds the current stored form data
		 *
		 * @var		array
		 */
		protected $formdata		= Array();


		/**
		 * Constructs a new formdata exception from an extended class
		 *
		 * @param	string			The exception error message
		 * @param	array			Form data to store as an array if any
		 */
		public function __construct($message, Array $formdata = NULL)
		{
			parent::__construct($message);

			if(is_array($formdata))
			{
				$this->formdata = $formdata;
			}
		}

		/**
		 * Gets the form data for a specific field
		 *
		 * @param	string			The field name to get
		 * @return	string			Returns the value of the form field, or false if field does not exists
		 */
		public function getField($name)
		{
			if(!isset($this->formdata[$name]))
			{
				return(false);
			}

			return((string) $this->formdata[$name]);
		}

		/**
		 * Gets all the fields within the form data exception
		 *
		 * @return	array			Returns an array with all the registered elements
		 */
		public function getFields()
		{
			return($this->formdata);
		}
	}

	/**
	 * Named form data exception, just like the regular formdata exception 
	 * this is used to identicate field names instead of values as the 
	 * regular one does.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Named_Formdata_Exception extends Tuxxedo_FormData_Exception
	{
		/**
		 * Constructor
		 *
		 * @param	array			Named form data to store
		 */
		public function __construct(Array $formdata)
		{
			parent::__construct('Form validation failed', $formdata);
		}
	}

	/**
	 * Basic exception type, this is used for errors that 
	 * should act as fatal errors. If an exception of this 
	 * is caught by the default exception handler it will 
	 * terminate the execution.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Basic_Exception extends Tuxxedo_Exception
	{
	}

	/**
	 * Datastore cache, this enables datastore caching for 
	 * databases. This assumes the datastore table and 
	 * everything else required for a database based 
	 * datastore is setup.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Datastore
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * Holds the cached elements from the datastore
		 *
		 * @var		array
		 */
		protected $cache	= Array();

		/**
		 * True if the datastore is ready to use
		 *
		 * @var		boolean
		 */
		protected $ready	= false;


		/**
		 * Constructor
		 *
		 * @throws	Tuxxedo_Basic_Exception	Throws a regular exception if the database interface isn't loaded
		 */
		public function __construct()
		{
			$this->tuxxedo = Tuxxedo::init();

			if($this->tuxxedo->db === false)
			{
				throw new Tuxxedo_Basic_Exception('A database driver must be initalized before the datastore can be constructed');
			}

			$this->ready = true;
		}

		/**
		 * Quick reference for overloading of a loaded 
		 * element in the datastore.
		 *
		 * @param	string			The datastore element to load
		 * @return	array			An array is returned, otherwise boolean false on error
		 */
		public function __get($name)
		{
			if(isset($this->cache[$name]))
			{
				return($this->cache[$name]);
			}

			return(false);
		}

		/**
		 * Fetches a new item from the datastore cache
		 *
		 * @param	string			The datastore element to load
		 * @return	array			An array is returned, otherwise boolean false on error
		 */
		public function fetch($name)
		{
			return($this->{$name});
		}

		/**
		 * Frees a datastore from the loaded cache
		 *
		 * @param	string			The datastore element to free from cache
		 * @return	void			No value is returned
		 */
		public function free($name)
		{
			if(isset($this->cache[$name]))
			{
				unset($this->cache[$name]);
			}
		}

		/**
		 * Rebuilds a datastore element if it already exists, or adds 
		 * a new entry in the datastore if no elements with that name 
		 * already exists. To delete a datastore element completely,  
		 * the data parameter must be set to NULL. If the delay 
		 * parameter is set to true, then the current cached data 
		 * will not be updated with the new data.
		 *
		 * @param	string			The datastore element
		 * @param	mixed			This can be either an array or object, if this is NULL then the datastore is deleted completely
		 * @param	boolean			Should this action be delayed until shutdown? (Defaults to true)
		 * @return	boolean			True on success, otherwise false on error
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail (only if the delay parameter is set to false)
		 */
		public function rebuild($name, Array $data = NULL, $delay = true)
		{
			if(!$this->ready)
			{
				return(false);
			}
			elseif(is_null($data))
			{
				$sql = sprintf('
						DELETE FROM 
							`' . TUXXEDO_PREFIX . 'datastore` 
						WHERE 
							`name` = \'%s\';', $this->tuxxedo->db->escape($name));
			}
			else
			{
				$sql = sprintf('
						REPLACE INTO 
							`' . TUXXEDO_PREFIX . 'datastore` 
							(
								`name`, 
								`data`
							) 
						VALUES 
							(
								\'%s\', 
								\'%s\'
							);', $this->tuxxedo->db->escape($name), $this->tuxxedo->db->escape(serialize($data)));
			}

			if($delay)
			{
				$this->tuxxedo->db->setShutdownQuery($sql);

				return(true);
			}

			$retval = $this->tuxxedo->db->query($sql);

			if($retval)
			{
				if(is_null($data))
				{
					unset($this->cache[$name]);
				}
				else
				{
					$this->cache[$name] = $data;
				}
			}

			return($retval);
		}

		/**
		 * Caches a set of elements from the datastore into 
		 * the current cache.
		 *
		 * @param	array			An array, where the values are the datastore element names
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			True on success, otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $elements, Array &$error_buffer = NULL)
		{
			if(!$this->ready || !sizeof($elements))
			{
				return(false);
			}

			$result = $this->tuxxedo->db->query('
								SELECT 
									`name`, 
									`data` 
								FROM 
									`' . TUXXEDO_PREFIX . 'datastore` 
								WHERE 
									`name` 
									IN
									(
										\'%s\'
									);', join('\', \'', array_map(Array($this->tuxxedo->db, 'escape'), $elements)));

			if($result === false)
			{
				if(!is_null($error_buffer))
				{
					$error_buffer = $elements;
				}

				return(false);
			}

			$loaded = Array();

			while($row = $result->fetchObject())
			{
				$row->data = @unserialize($row->data);

				if($row->data !== false)
				{
					$loaded[] 			= $row->name;
					$this->cache[$row->name] 	= $row->data;
				}
			}

			if(!is_null($error_buffer))
			{
				$diff = array_diff($elements, $loaded);

				if(sizeof($diff))
				{
					$error_buffer = $diff;
				}

				return(false);
			}

			return(true);
		}
	}

	/**
	 * Data filter class, this class cleans data 
	 * with magic quotes in mind. It will use the filter 
	 * extension if its available or use its own filtering 
	 * functions otherwise.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_DataFilter
	{
		/**
		 * Whether the filter extension is available
		 *
		 * @var		boolean
		 */
		protected static $have_filter_ext	= false;

		/**
		 * Whether magic_quotes_gpc is enabled or not
		 *
		 * @var		boolean
		 */
		protected static $have_magic_quotes	= false;


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration, Array $options)
		{
			self::$have_filter_ext 		= extension_loaded('filter');
			self::$have_magic_quotes	= get_magic_quotes_gpc();
		}

		/**
		 * Validates data using a user specified callback method
		 *
		 * @param	mixed			The data to validate
		 * @param	callback		A callback thats used to validate the data
		 * @return	boolean			Returns true if the callback returned success, otherwise false
		 */
		public function validate($data, $callback)
		{
			if(is_callable($callback))
			{
				return(call_user_func($callback, $data));
			}

			return(false);
		}

		/**
		 * Filters 'GET' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function get($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(1, $field, $type, $options));
		}

		/**
		 * Filters 'POST' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function post($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(2, $field, $type, $options));
		}

		/**
		 * Filters 'COOKIE' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function cookie($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(3, $field, $type, $options));
		}

		/**
		 * Filters 'user' data, as passed to this method
		 *
		 * @param	string			The data to clean
		 * @param	integer			Type of input filtering performed
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function user($field, $type = TYPE_STRING)
		{
			return($this->filter(4, $field, $type, 0));
		}

		/**
		 * Private filter method used by the GPC methods 
		 * to filter data.
		 *
		 * @param	integer			Where the data to filter is coming from (1 = GET, 2 = POST, 3 = COOKIE & 4 = User)
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		private function filter($source, $field, $type = TYPE_STRING, $options = 0)
		{
			switch($source)
			{
				case(1):
				{
					$data = (self::$have_filter_ext ? INPUT_GET : $_GET);
				}
				break;
				case(2):
				{
					$data = (self::$have_filter_ext ? INPUT_POST : $_POST);
				}
				break;
				case(3):
				{
					$data = (self::$have_filter_ext ? INPUT_COOKIE : $_COOKIE);
				}
				break;
				case(4):
				{
					$data = $field;

					if(!self::$have_filter_ext)
					{
						$data 	= Array($field);
						$field	= 0;
					}
				}
				break;
				default:
				{
					return;
				}
				break;
			}


			if(self::$have_filter_ext)
			{
				if($source != 4 && !filter_has_var($data, $field))
				{
					return;
				}

				if($options & INPUT_OPT_RAW)
				{
					return(filter_input($data, $field, FILTER_UNSAFE_RAW));
				}

				switch($type)
				{
					case(TYPE_NUMERIC):
					{
						$flags = FILTER_VALIDATE_INT;
					}
					break;
					case(TYPE_EMAIL):
					{
						$flags = FILTER_VALIDATE_EMAIL;
					}
					break;
					case(TYPE_BOOLEAN):
					{
						$flags = FILTER_VALIDATE_BOOLEAN;
					}
					break;
					default:
					{
						$flags = FILTER_DEFAULT;
					}
					break;
				}

				if($source == 4)
				{
					$input = filter_var($data, $flags, 0);
				}
				else
				{
					$input = filter_input($data, $field, $flags, ($options & INPUT_OPT_ARRAY ? FILTER_REQUIRE_ARRAY | FILTER_FORCE_ARRAY : 0));
				}

				return($input);
			}
			else
			{
				if($source != 4 && !isset($data[$field]))
				{
					return;
				}

				if($options & INPUT_OPT_RAW)
				{
					return($data[$field]);
				}

				if($options & INPUT_OPT_ARRAY)
				{
					$data[$field] = (array) $data[$field];

					if(self::$have_magic_quotes)
					{
						$data[$field] = array_map('stripslashes', $data[$field]);
					}

					if(sizeof($data[$field]))
					{
						foreach($data[$field] as $var => $tmp)
						{
							switch($type)
							{
								case(TYPE_NUMERIC):
								{
									$data[$field][$var] = (integer) $tmp;
								}
								break;
								case(TYPE_EMAIL):
								{
									$data[$field][$var] = (is_valid_email($data[$field]) ? $data[$field] : false);
								}
								break;
								case(TYPE_BOOLEAN):
								{
									$data[$field][$var] = (boolean) $tmp;
								}
								break;
								default:
								{
									$data[$field][$var] = (string) $tmp;
								}
								break;
							}
						}
					}
				}
				else
				{
					if($source != 4 && self::$have_magic_quotes)
					{
						$data[$field] = stripslashes($data[$field]);
					}

					switch($type)
					{
						case(TYPE_NUMERIC):
						{
							$data[$field] = (integer) $data[$field];
						}
						break;
						case(TYPE_EMAIL):
						{
							$data[$field] = (is_valid_email($data[$field]) ? $data[$field] : false);
						}
						break;
						case(TYPE_BOOLEAN):
						{
							$data[$field] = (boolean) $tmp;
						}
						break;
						default:
						{
							$data[$field] = (string) $data[$field];
						}
						break;
					}
				}

				if($type == TYPE_NUMERIC && $data[$field] == 0)
				{
					$data[$field] = NULL;
				}

				return($data[$field]);
			}
		}
	}

	/**
	 * Styling API, this enables basic styling frontend for 
	 * caching templates and fetching them for execution.
	 *
	 * To compile templates thats loadable through this class 
	 * you should look at the {@link Tuxxedo_Template_Compiler} 
	 * class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Style
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * Holds the current style data
		 *
		 * @var		array
		 */
		protected $styleinfo	= Array();

		/**
		 * Holds the current loaded templates
		 *
		 * @var		array
		 */
		protected $templates	= Array();


		/**
		 * Constructs a new style object
		 *
		 * @param	array			The style data to use
		 */
		public function __construct(Array $styleinfo)
		{
			$this->tuxxedo		= Tuxxedo::init();
			$this->styleinfo 	= $styleinfo;
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
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if an invalid (or not cached) style id was used
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
			$styledata 	= $tuxxedo->cache->styleinfo;
			$styleid	= ($options ? (!empty($tuxxedo->userinfo->id) && $tuxxedo->userinfo->style_id != $options['style_id'] ? $tuxxedo->userinfo->style_id : $options['style_id']) : 0);

			if($styleid && isset($styledata[$styleid]))
			{
				return(new self($styledata[$styleid]));
			}

			throw new Tuxxedo_Basic_Exception('Invalid style id, try rebuild the datastore or use the repair tools');
		}

		/**
		 * Gets the style information
		 *
		 * @param	string			If set, then a the style info value is returned
		 * @return	array			Returns an array with information about the current style
		 */
		public function getStyleinfo($varname = NULL)
		{
			if(is_scalar($varname) && array_key_exists($varname, $this->styleinfo))
			{
				return($this->styleinfo[$varname]);
			}

			return($this->styleinfo);
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array			A list of templates to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			if(!sizeof($templates))
			{
				return(false);
			}

			$result = $this->tuxxedo->db->query('
								SELECT 
									`title`, 
									`compiledsource` 
								FROM 
									`' . TUXXEDO_PREFIX . 'templates` 
								WHERE 
										`styleid` = %d 
									AND 
										`title` IN (
											\'%s\'
										);', 
								$this->styleinfo['id'], join('\', \'', array_map(Array($this->tuxxedo->db, 'escape'), $templates)));

			if($result === false || !sizeof($result))
			{
				if(!is_null($error_buffer))
				{
					$error_buffer = $templates;
				}

				return(false);
			}

			$loaded = Array();

			while($row = $result->fetchObject())
			{
				$loaded[] 			= $row->title;
				$this->templates[$row->title] 	= $row->compiledsource;
			}

			if(!is_null($error_buffer) && ($diff = array_diff($templates, $loaded)) && sizeof($diff))
			{
				$error_buffer = $diff;

				return(false);
			}

			return(true);
		}

		/**
		 * Fetches a cached template
		 *
		 * @param	string			The name of the template to fetch
		 * @return	string			Returns the compiled template code for execution, and boolean false on error
		 */
		public function fetch($template)
		{

			$template = strtolower($template);

			if(!array_key_exists($template, $this->templates))
			{
				return(false);
			}

			return($this->templates[$template]);
		}
	}

	/**
	 * User session class, this class manages the current user 
	 * session information and permission bitfields.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_UserSession
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * User information
		 *
		 * @var		stdClass
		 */
		protected $userinfo;

		/**
		 * Usergroup information
		 *
		 * @var		array
		 */
		protected $usergroupinfo;

		/**
		 * The session options, such as prefix, path etc.
		 *
		 * @var		array
		 */
		protected static $options	= Array(
							'expires'	=> 1800, 
							'prefix'	=> '', 
							'domain'	=> '', 
							'path'		=> ''
							);

		/**
		 * Constructor, instanciates a new user session. It detects 
		 * the session data automaticlly so it can be created instantly
		 *
		 * @param	string			Email address of the user, if creating a new user session only
		 * @param	string			Raw password associated with the above email, if creating a new user session only
		 *
		 * @throws	Tuxxedo_Exception	Throws a regular exception with no error message if the email and password supplied was invalid
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if the usergroup information fails to load from the datastore or if one of the database queries should fail
		 */
		public function __construct($email = NULL, $password = NULL)
		{
			$this->tuxxedo = Tuxxedo::init();

			if(!is_null($email) && !is_null($password))
			{
				if(!$email || empty($password))
				{
					throw new Tuxxedo_Exception('Invalid email or password');
				}

				$userinfo = fetch_userinfo($email, true);

				if(!$userinfo || !$email || !is_valid_password($password, $userinfo->salt, $userinfo->password))
				{
					throw new Tuxxedo_Exception('Invalid email or password');
				}

				self::set('userid', $userinfo->id);

				$this->tuxxedo->db->query('
								REPLACE INTO 
									`' . TUXXEDO_PREFIX . 'sessions` 
								VALUES
								(
									\'%s\', 
									%d,
									\'%s\', 
									UNIX_TIMESTAMP()
								)', session_id(), $userinfo->id, $this->tuxxedo->db->escape(TUXXEDO_SELF));
			}

			if($userid = self::get('userid'))
			{
				if(!isset($userinfo))
				{
					$query = $this->tuxxedo->db->query('
										SELECT 
											' . TUXXEDO_PREFIX . 'sessions.*,
											' . TUXXEDO_PREFIX . 'users.*
										FROM 
											`' . TUXXEDO_PREFIX . 'sessions`
										LEFT JOIN 
											`' . TUXXEDO_PREFIX . 'users` 
											ON 
												' . TUXXEDO_PREFIX . 'sessions.userid = ' . TUXXEDO_PREFIX . 'users.id
										WHERE 
											' . TUXXEDO_PREFIX . 'sessions.sessionid = \'%s\'
											AND 
											' . TUXXEDO_PREFIX . 'users.id = %d
										LIMIT 1', session_id(), $userid);

					if($query)
					{
						$userinfo = $query->fetchObject();
						$query->free();
					}
				}

				if(isset($userinfo))
				{
					if(!isset($this->tuxxedo->cache->usergroups[$userinfo->usergroupid]))
					{
						throw new Tuxxedo_Basic_Exception('Unable to usergroup permissions, datastore possibly corrupted');
					}

					$this->userinfo		= $userinfo;
					$this->usergroupinfo 	= (object) $this->tuxxedo->cache->usergroups[$this->userinfo->usergroupid];

					$this->tuxxedo->db->setShutdownQuery('
										UPDATE 
											`' . TUXXEDO_PREFIX . 'sessions` 
										SET 
											`location` = \'%s\', 
											`lastactivity` = UNIX_TIMESTAMP() 
										WHERE 
											`sessionid` = \'%s\'', $this->tuxxedo->db->escape(TUXXEDO_SELF), session_id());
				}
			}

			$this->tuxxedo->db->setShutdownQuery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'sessions` 
								WHERE 
									`lastactivity` + %d < UNIX_TIMESTAMP()', self::$options['expires']);
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
			self::$options = Array(
						'expires'	=> $options['cookie_expires'], 
						'prefix'	=> $options['cookie_prefix'], 
						'domain'	=> $options['cookie_domain'], 
						'path'		=> $options['cookie_path']
						);

			session_set_cookie_params($options['cookie_expires'], $options['cookie_domain'], $options['cookie_path'], false, true);
			session_start();
		}

		/**
		 * Gets a session variable
		 *
		 * @param	string			Variable name
		 * @param	boolean			Whether to include the session prefix or not, defaults to true
		 * @return	mixed			Returns the session variable value on success, or null on failure
		 */
		public static function get($name, $prefix = true)
		{
			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			if(!isset($_SESSION[$name]))
			{
				return(NULL);
			}

			return($_SESSION[$name]);
		}

		/**
		 * Sets a session variable
		 *
		 * @param	string			Variable name
		 * @param	mixed			Variable value
		 * @param	boolean			Whether to include the session prefix or not, defaults to true
		 * @return	void			No value is returned
		 */
		public static function set($name, $value, $prefix = true)
		{
			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			$_SESSION[$name] = $value;
		}

		/**
		 * Checks if a user is logged in or not
		 *
		 * @return	boolean			True if a user is logged in, otherwise false
		 */
		public function isLoggedIn()
		{
			return(is_object($this->userinfo));
		}

		/**
		 * Gets the user information for the current logged 
		 * in user
		 *
		 * @return	object			Returns an object with the users information, or boolean false if user isnt logged in
		 */
		public function getUserinfo()
		{
			if(!is_object($this->userinfo))
			{
				return(false);
			}

			return($this->userinfo);
		}

		/**
		 * Gets the usergroup information for the current 
		 * logged in user
		 *
		 * @return	object			Returns an object with the users usergroup information, or boolean false if user isnt logged in
		 */
		public function getUsergroup()
		{
			if(!is_object($this->userinfo))
			{
				return(false);
			}

			return($this->usergroupinfo);
		}

		/**
		 * Checks if a user is a member of a specific usergroup
		 *
		 * @return	boolean			Returns true if the user is a member of that group, otherwise false
		 */
		public function isMemberOf($groupid)
		{
			return(is_object($this->usergroupinfo) && $this->usergroupinfo->id == $groupid);
		}

		/**
		 * Logs a user out
		 *
		 * @return	void			No value is returned
		 */
		public function logout()
		{
			if(!is_object($this->userinfo))
			{
				return;
			}

			session_unset();
			session_destroy();

			$this->tuxxedo->db->setShutdownQuery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'sessions` 
								WHERE 
									`sessionid` = \'%s\'', $this->userinfo->sessionid);

			$this->userinfo = $this->usergroupinfo = NULL;
		}
	}
?>