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
	 * Datamanager namespace, this contains all base adapter class that 
	 * datamanagers must extend in order to become loadable. The root 
	 * namespace also hosts interfaces that datamanagers can implement 
	 * to extend the magic within.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Datamanager;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Datamanager\Hooks;
	use Tuxxedo\Exception;
	use Tuxxedo\Input;
	use Tuxxedo\InfoAccess;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * Abstract datamanager class
	 *
	 * Every datamanager class must extend this class in order to be loadable and to 
	 * comply with the datamanager API. This also contains the factory method used 
	 * to instanciate a new datamanager instance.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class Adapter extends InfoAccess
	{
		/**
		 * Indicates that a field is required
		 *
		 * @var		integer
		 */
		const FIELD_REQUIRED			= 1;

		/**
		 * Indicates that a field is optional
		 *
		 * @var		integer
		 */
		const FIELD_OPTIONAL			= 2;

		/**
		 * Indicates that a field is protected
		 *
		 * @var		integer
		 */
		const FIELD_PROTECTED			= 3;

		/**
		 * Indicates that a field is virtual
		 *
		 * @var		integer
		 */
		const FIELD_VIRTUAL			= 4;

		/**
		 * Context constant, default context
		 *
		 * @var		integer
		 */
		const CONTEXT_NONE			= 1;

		/**
		 * Context constant, save() context
		 *
		 * @var		integer
		 */
		const CONTEXT_SAVE			= 2;

		/**
		 * Context constant, delete() context
		 *
		 * @var		integer
		 */
		const CONTEXT_DELETE			= 4;

		/**
		 * Validation constant, numeric value
		 *
		 * @var		integer
		 */
		const VALIDATE_NUMERIC			= Input::TYPE_NUMERIC;

		/**
		 * Validation constant, string value
		 *
		 * @var		integer
		 */
		const VALIDATE_STRING			= Input::TYPE_STRING;

		/**
		 * Validation constant, email value
		 *
		 * @var		integer
		 */
		const VALIDATE_EMAIL			= Input::TYPE_EMAIL;

		/**
		 * Validation constant, boolean value
		 *
		 * @var		integer
		 */
		const VALIDATE_BOOLEAN			= Input::TYPE_BOOLEAN;

		/**
		 * Validation constant, callback
		 *
		 * @var		integer
		 */
		const VALIDATE_CALLBACK			= Input::TYPE_CALLBACK;

		/**
	 	 * Validation option constant, allow empty fields
		 *
		 * @var		integer
		 */
		const VALIDATE_OPT_ALLOWEMPTY		= 0x001F;

		/**
		 * Factory option constant - internationalization (default enabled)
		 *
		 * @var		integer
		 */
		const OPT_INTL				= 1;

		/**
		 * Factory option constant - insert as new record
		 *
		 * @var		integer
		 */
		const OPT_LOAD_ONLY			= 2;

		/**
		 * Factory option constant - default options
		 *
		 * @var		integer
		 */
		const OPT_DEFAULT			= self::OPT_INTL;


		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Datamanager name, set by the datamanager
		 *
		 * @var		string
		 */
		protected $dmname;

		/**
		 * Table name, set by the datamanager
		 *
		 * @var		array
		 */
		protected $tablename;

		/**
		 * Identifier name, set by the datamanager
		 *
		 * @var		array
		 */
		protected $idname;

		/**
		 * Identifier, if any
		 *
		 * @var		array
		 */
		protected $identifier;

		/**
		 * Current data thats been set via the set method
		 *
		 * @var		\stdClass
		 */
		protected $userdata;

		/**
		 * Whether this datamanager are called from another datamanager
		 *
		 * @var		\Tuxxedo\Datamanager\Adapter
		 */
		protected $parent			= false;

		/**
		 * Whether the datamanager needs to re-validate
		 *
		 * @var		boolean
		 */
		protected $revalidate			= true;

		/**
		 * Context for hooks, and adapters
		 *
		 * @var		integer
		 */
		protected $context			= self::CONTEXT_NONE;

		/**
		 * The original data if instanciated by an identifier
		 *
		 * @var		array
		 */
		protected $data				= Array();


		/**
		 * List of shutdown handlers to execute
		 *
		 * @var		array
		 */
		protected $shutdown_handlers		= Array();

		/**
		 * List of fields that had one or more errors and therefore 
		 * could not be saved
		 *
		 * @var		array
		 */
		protected $invalid_fields		= Array();

		/**
		 * Hooks executor callback
		 *
		 * @var		closure
		 */
		protected static $hooks_executor;

		/**
		 * List of loaded datamanagers used for caching in the 
		 * special required cases where more than one driver 
		 * have to be loaded
		 *
		 * @var		array
		 */
		protected static $loaded_datamanagers 	= Array();


		/**
		 * Constructor for the current datamanager, this 
		 * can be used to either create a datamanager based 
		 * on a certain record determined by the passed identifier 
		 * or as a clean datamanager to insert a new record
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	mixed				The unique identifier to send to the datamanager
		 * @param	integer				The datamanager options
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception		Throws an exception if the unique identifier sent to the datamanager was invalid
		 */
		abstract public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL);

		/**
		 * Destructor for the current datamanager, this is 
		 * reserved for shutdown handlers in parent datamanagers.
		 */
		final public function __destruct()
		{
			if($this->shutdown_handlers)
			{
				foreach($this->shutdown_handlers as $c)
				{
					call_user_func_array($c['handler'], $c['arguments']);
				}
			}
		}

		/**
		 * Overloads the info access 'get' method so that default data is allocated 
		 * when using the ArrayAccess accessor
		 *
		 * @param	scalar			The information row name to get
		 * @return	void			No value is returned
		 */
		public function offsetGet($offset)
		{
			if(\is_object($this->information))
			{
				if(!isset($this->information->{$offset}))
				{
					$this->information->{$offset} = (isset($this->fields[$offset]) && isset($this->fields[$offset]['default']) ? $this->fields[$offset]['default'] : '');
				}

				return($this->information->{$offset});
			}
			else
			{
				if(!isset($this->information[$offset]))
				{
					$this->information[$offset] = (isset($this->fields[$offset]) && isset($this->fields[$offset]['default']) ? $this->fields[$offset]['default'] : '');
				}

				return($this->information[$offset]);
			}
		}

		/**
		 * Datamanager initializer, this method initializes the default logic 
		 * used across all datamanager adapters
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 * @return	void				No value is returned
		 */
		final protected function init(Registry $registry, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->registry		= $registry;
			$this->options		= $options;
			$this->userdata		= $this->information = new \stdClass;
			$this->parent		= $parent;
			$this->information 	= &$this->data;

			if($options & self::OPT_LOAD_ONLY)
			{
				$this->identifier = $this->fields[$this->idname]['value'] = NULL;
			}
		}

		/**
		 * Constructs a new datamanger instance
		 *
		 * @param	string				Datamanger name
		 * @param	mixed				An identifier to send to the datamanager to load default data upon instanciating it
		 * @param	integer				Additional options to apply on the datamanager
		 * @return	\Tuxxedo\Datamanager\Adapter	Returns a new database instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if loading of a datamanger should fail for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails when loading the datamanager
		 */
		final public static function factory($datamanager, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$registry = Registry::init();

			if($options & self::OPT_INTL)
			{
				if(!$registry->intl)
				{
					throw new Exception\Basic('Internationalization is not instanciated for form data phrases');
				}

				if(!$registry->intl->cache(array('datamanagers')))
				{
					throw new Exception\Basic('Unable to cache datamanager phrases');
				}
			}

			$class	= (strpos($datamanager, '\\') === false ? '\Tuxxedo\Datamanager\Adapter\\' : '') . ucfirst($datamanager);
			$dm 	= new $class($registry, $identifier, $options, $parent);

			if(\in_array($datamanager, self::$loaded_datamanagers))
			{
				return($dm);
			}

			if(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt datamanager driver, driver class does not follow the driver specification');
			}

			self::$loaded_datamanagers[] = $datamanager;

			if(!self::$hooks_executor)
			{
				self::$hooks_executor = function(Adapter $self, Array $virtual, Array $virtual_fields)
				{
					if($self instanceof Hooks\Cache && !$self->rebuild($virtual))
					{
						return(false);
					}

					$dispatch = ($self instanceof Hooks\VirtualDispatcher);

					if($virtual_fields && ($dispatch || $self instanceof Hooks\Virtual))
					{
						foreach($virtual_fields as $field => $value)
						{
							if($dispatch)
							{
								$method = 'Virtual' . $field;

								if(method_exists($self, $method) && !$self->{$method}($value))
								{
									return(false);
								}
							}
							elseif(!$this->virtual($field, $value))
							{
								return(false);
							}
						}
					}

					return(true);
				};
			}

			return($dm);
		}

		/**
		 * Gets a list over invalid fields, this is only populated 
		 * if an attempt to saving a datamanager have failed
		 *
		 * @return	array				Returns a list of those fields that failed validation
		 */
		public function getInvalidFields()
		{
			return($this->invalid_fields);
		}

		/**
		 * Gets a list of virtual fields from the datamanager adapter 
		 *
		 * @return	array				Returns an array with field => value pairs, and empty array on none
		 */
		public function getVirtualFields()
		{
			if(!$this->fields)
			{
				return(Array());
			}

			$fields = Array();

			foreach($this->fields as $name => $props)
			{
				if(isset($props['type']) && $props['type'] == self::FIELD_VIRTUAL && isset($this->userdata->{$name}))
				{
					$fields[$name] = $this->userdata->{$name};
				}
			}

			return(($fields ? $fields : Array()));
		}

		/**
		 * Gets a field
		 *
		 * @param	string				The field to get, if this value is NULL then all the backend data will be returned
		 * @return	mixed				Returns the field value, and NULL if the field is non existant (set)
		 */
		public function get($field = NULL)
		{
			if($field === NULL)
			{
				return($this->data);
			}
			elseif(isset($this->userdata->{$field}))
			{
				return($this->userdata->{$field});
			}
			elseif(isset($this->data[$field]))
			{
				return($this->data[$field]);
			}
		}

		/**
		 * Sets a shutdown handler
		 *
		 * @param	callback			A callback to execute
		 * @param	array				Any additonal arguments the callback needs to execute properly
		 * @return	void				No value is returned
		 */
		public function setShutdownHandler($handler, Array $arguments)
		{
			if(!is_callable($handler))
			{
				return;
			}

			$this->shutdown_handlers[] = Array(
								'handler'	=> $handler, 
								'arguments'	=> $arguments
								);
		}

		/**
		 * Validation method, validates the supplied user data 
		 *
		 * @return	boolean				Returns true if the data is valid, otherwise false
		 */
		public function validate()
		{
			$this->invalid_fields = Array();

			if(!\get_object_vars($this->userdata))
			{
				$this->revalidate = false;

				return(true);
			}

			$input = $this->registry->register('input', '\Tuxxedo\Input');

			foreach($this->fields as $field => $properties)
			{
				switch($properties['type'])
				{
					case(self::FIELD_VIRTUAL):
					{
						if(!$this->identifier && !isset($this->userdata->{$field}))
						{
							$this->invalid_fields[] = $field;
						}

						continue 2;
					}
					case(self::FIELD_REQUIRED):
					{
						if(!isset($this->userdata->{$field}))
						{
							continue 2;
						}
					}
					break;
					case(self::FIELD_PROTECTED):
					{
						if(isset($this->userdata->{$field}))
						{
							$this->invalid_fields[] = $field;

							continue 2;
						}

						if(isset($properties['default']))
						{
							$this->data[$field] = $properties['default'];
						}
						elseif(isset($properties['callback']) && \is_callable($properties['callback']))
						{
							if(isset($properties['parameters']))
							{
								$this->data[$field] = \call_user_func_array($properties['callback'], \array_merge(Array($this, $this->registry), $properties['parameters']));
							}
							else
							{
								$this->data[$field] = \call_user_func($properties['callback'], $this->registry);
							}
						}

						continue 2;
					}
					break;
				}

				if(isset($properties['default']))
				{
					$this->data[$field] = $properties['default'];
				}

				if(isset($properties['validation']) && $properties['validation'] == self::VALIDATE_CALLBACK && isset($properties['callback']))
				{
					if(!$input->validate($this->userdata->{$field}, $properties['callback']))
					{
						$this->invalid_fields[] = $field;

						continue;
					}
				}
				elseif($properties['type'] == self::FIELD_REQUIRED && ($properties['validation'] & self::VALIDATE_OPT_ALLOWEMPTY) && empty($this->userdata->{$field}) && $this->userdata->{$field} !== 0)
				{
					$this->invalid_fields[] = $field;

					continue;
				}
				elseif(($properties['validation'] & self::VALIDATE_OPT_ALLOWEMPTY) && empty($this->userdata->{$field}))
				{
					continue;
				}
				else
				{
					if(!isset($properties['validation']) || !isset($this->userdata->{$field}) || ($filtered = $input->user($this->userdata->{$field}, $properties['validation'])) === NULL)
					{
						$this->invalid_fields[] = $field;

						unset($this->userdata->{$field});

						continue;
					}

					$this->userdata->{$field} = $filtered;
				}
			}

			if(!$this->invalid_fields)
			{
				$this->revalidate = false;

				return(true);
			}

			return(false);
		}

		/**
		 * Updates a field type (required or optional), note that its 
		 * not possible to set a field to protected
		 *
		 * @param	string				Name of the field
		 * @param	integer				The new type of the field
		 * @return	boolean				Returns true if the new type was set, otherwise false
		 */
		public function setFieldType($field, $type)
		{
			if(!isset($this->fields[$field]) || ($type != self::FIELD_OPTIONAL && $type != self::FIELD_REQUIRED))
			{
				return(false);
			}

			$this->fields[$field]['type'] 	= (integer) $type;
			$this->revalidate		= true;

			return(true);
		}

		/**
		 * Save method, attempts to validate and save the data 
		 * into the database
		 *
		 * @param	boolean				Whether to execute hooks or not. This parameter is mainly designed for datamanager internals
		 * @return	boolean				Returns true if the data is saved with success, otherwise boolean false
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the query should fail
		 * @throws	\Tuxxedo\Exception\FormData	Throws a formdata exception if validation fails
		 */
		public function save($execute_hooks = true)
		{
			$this->context = self::CONTEXT_SAVE;

			if($this->revalidate && !$this->validate())
			{
				$intl		= isset($this->registry->intl) && ($this->options & self::OPT_INTL);
				$formdata 	= Array();

				foreach($this->invalid_fields as $field)
				{
					$formdata[$field] = ($intl && isset($this->registry->phrase['dm_' . $this->dmname . '_' . $field]) ? $this->registry->phrase['dm_' . $this->dmname . '_' . $field] : $field);
				}

				$this->context = self::CONTEXT_NONE;

				throw new Exception\Formdata($formdata);
			}

			$values			= '';
			$sql 			= ($this->options & self::OPT_LOAD_ONLY ? 'INSERT INTO' : 'REPLACE INTO') . ' `' . $this->tablename . '` (';
			$virtual		= \array_merge($this->data, \get_object_vars($this->userdata));
			$virtual		= ($this->identifier !== NULL ? \array_merge(Array($this->idname => $this->identifier), $virtual) : $virtual);
			$virtual_fields		= $this->getVirtualFields();
			$n 			= \sizeof($virtual);
			$this->revalidate 	= true;

			if($virtual_fields)
			{
				$n -= \sizeof($virtual_fields);
			}

			foreach($virtual as $field => $data)
			{
				if(($field == $this->idname && $this->options & self::OPT_LOAD_ONLY) || isset($this->fields[$field]['type']) && $this->fields[$field]['type'] == self::FIELD_VIRTUAL)
				{
					if($field == $this->idname && ($this->options & self::OPT_LOAD_ONLY))
					{
						--$n;
					}

					continue;
				}

				$sql 	.= '`' . $field . '`' . (--$n ? ', ' : '');
				$values .= (is_null($data) ? ($this->fields[$field]['validation'] == self::VALIDATE_NUMERIC || $this->fields[$field]['validation'] == self::VALIDATE_BOOLEAN ? '0' : 'NULL') : '\'' . $this->registry->db->escape($data) . '\'') . ($n ? ', ' : '');
			}

			if(!$this->registry->db->query($sql . ') VALUES (' . $values . ')'))
			{
				$this->context = self::CONTEXT_NONE;

				return(false);
			}

			if(($new_id = $this->registry->db->getInsertId()) !== false)
			{
				$this->data[$this->idname] = $new_id;
			}

			if($execute_hooks)
			{
				$hooks = self::$hooks_executor;

				if(!$this->parent)
				{
					$this->context = self::CONTEXT_NONE;

					return($hooks($this, $virtual, $virtual_fields));
				}

				$this->parent->setShutdownHandler($hooks, Array($this, $virtual, $virtual_fields));
			}

			$this->context = self::CONTEXT_NONE;

			return(true);
		}

		/**
	 	 * Deletes the data, within the database if an identifier was specified, else 
		 * the current set data is removed
		 *
		 * @return	boolean				Returns true if the deletion was a success otherwise boolean false
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the query should fail
		 */
		public function delete()
		{
			$this->invalid_fields 	= Array();
			$this->userdata		= new \stdClass;

			if($this->identifier === NULL && !($this->options & self::OPT_LOAD_ONLY))
			{
				return(true);
			}

			$this->context = self::CONTEXT_DELETE;

			if($this instanceof Hooks\Cache && !$this->rebuild(Array()))
			{
				$this->context = self::CONTEXT_NONE;

				return(false);
			}

			$this->context = self::CONTEXT_NONE;

			return($this->registry->db->equery('
								DELETE FROM 
									`' . $this->tablename . '`
								WHERE 
									`' . $this->idname .'` = \'%s\'', ($this->options & self::OPT_LOAD_ONLY ? $this->data[$this->idname] : $this->identifier)));
		}

		/**
		 * Gets the parent datamanager pointer
		 *
		 * @return	\Tuxxedo\Datamanager\Adapter	Returns a datamanager pointer to the parent object if any, false on root or error
		 */
		public function getParent()
		{
			return($this->parent);
		}
	}
?>