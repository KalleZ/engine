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
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
	abstract class Adapter extends Design\InfoAccess implements \Iterator
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
		const CONTEXT_DELETE			= 3;

		/**
		 * Context constant, void context
		 *
		 * @var		integer
		 */
		const CONTEXT_VOID			= 4;

		/**
		 * Validation constant, no validation
		 *
		 * @var		integer
		 */
		const VALIDATE_NONE			= 1;

		/**
		 * Validation constant, numeric value
		 *
		 * @var		integer
		 */
		const VALIDATE_NUMERIC			= 2;

		/**
		 * Validation constant, string value
		 *
		 * @var		integer
		 */
		const VALIDATE_STRING			= 3;

		/**
		 * Validation constant, email value
		 *
		 * @var		integer
		 */
		const VALIDATE_EMAIL			= 4;

		/**
		 * Validation constant, boolean value
		 *
		 * @var		integer
		 */
		const VALIDATE_BOOLEAN			= 5;

		/**
		 * Validation constant, callback
		 *
		 * @var		integer
		 */
		const VALIDATE_CALLBACK			= 6;

		/**
	 	 * Validation option constant, allow empty fields
		 *
		 * @var		integer
		 */
		const VALIDATE_STRING_EMPTY		= 7;

		/**
		 * Validation option constant, identifier
		 *
		 * @var		integer
		 */
		const VALIDATE_IDENTIFIER		= 8;

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
		 * Factory option constant - internationalization, load if available
		 *
		 * @var		integer
		 */
		const OPT_INTL_AUTO			= 4;

		/**
		 * Factory option constant - default options
		 *
		 * @var		integer
		 */
		const OPT_DEFAULT			= self::OPT_INTL_AUTO;


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
		 * Whether to re-identify the data when saving
		 *
		 * @var		boolean
		 */
		protected $reidentify 			= false;
 
		/**
		 * Iterator position
		 *
		 * @var		integer
		 */
		protected $iterator_position		= 0;

		/**
		 * Whether this datamanager are called from another datamanager
		 *
		 * @var		\Tuxxedo\Datamanager\Adapter
		 */
		protected $parent			= false;

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
		 * Cache data if the identifier is gonna be validated
		 *
		 * @var		array
		 */
		protected $identifier_data		= Array();

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
				foreach($this->shutdown_handlers as $callback)
				{
					call_user_func_array($callback['handler'], $callback['arguments']);
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
			if(!isset($this->information[$offset]))
			{
				$this->information[$offset] = (isset($this->fields[$offset]) && isset($this->fields[$offset]['default']) ? $this->fields[$offset]['default'] : '');
			}

			return($this->information[$offset]);
		}

		/**
		 * Overloads the info access 'set' method so that its prohibited to 
		 * set elements that doesn't exists
		 *
		 * @param	scalar			The information row name to set
		 * @param	mixed			The information row value to set
		 * @return	void			No value is returned
		 */
		public function offsetSet($offset, $value)
		{
			if(!isset($this->fields[$offset]))
			{
				throw new Exception('Cannot define value for non existing field \'%s\'', $offset);
			}

			$this->information[$offset] = $value;
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
			$this->parent		= $parent;
			$this->information 	= &$this->data;

			if($options & self::OPT_LOAD_ONLY)
			{
				$this->identifier = $this->fields[$this->idname]['value'] = NULL;
			}

			if(isset($this->fields[$this->idname]['validation']) && $this->fields[$this->idname]['validation'] == self::VALIDATE_IDENTIFIER)
			{
				$query = $registry->db->query('
								SELECT 
									`%s` 
								FROM 
									`%s`', $this->idname, $this->tablename);

				if($query && $query->getNumRows())
				{
					foreach($query as $row)
					{
						$this->identifier_cache[] = $row[$this->idname];
					}
				}
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

			if($options & (self::OPT_INTL | self::OPT_INTL_AUTO))
			{
				if($options & self::OPT_INTL && !$registry->intl)
				{
					throw new Exception\Basic('Internationalization is not instanciated for form data phrases');
				}

				if($registry->intl && !$registry->intl->cache(Array('datamanagers')))
				{
					throw new Exception\Basic('Unable to cache datamanager phrases');
				}
			}

			$class	= (\strpos($datamanager, '\\') === false ? '\Tuxxedo\Datamanager\Adapter\\' : '') . $datamanager;
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
		 * @param	boolean				Whether or not to check for populated data (defaults to true)
		 * @return	array				Returns an array with field => value pairs, and empty array on none (if populated is set to off, all values are boolean true)
		 */
		public function getVirtualFields($populated = true)
		{
			if(!$this->fields)
			{
				return(Array());
			}

			$fields = Array();

			foreach($this->fields as $name => $props)
			{
				if(isset($props['type']) && $props['type'] == self::FIELD_VIRTUAL)
				{
					if($populated && !isset($this->data[$name]))
					{
						continue;
					}

					$fields[$name] = ($populated ? $this->data[$name] : true);
				}
			}

			return($fields);
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

			foreach($this->fields as $field => $props)
			{
				if($props['type'] == self::FIELD_PROTECTED && !isset($props['validation']) || $props['type'] == self::FIELD_OPTIONAL && !isset($props['default']) && !isset($this->data[$field]))
				{
					continue;
				}

				if(isset($props['default']) && !isset($this->data[$field]))
				{
					$this->data[$field] = $props['default'];
				}

				if(!isset($props['validation']) || $props['type'] == self::FIELD_VIRTUAL)
				{
					$props['validation'] = 0;
				}

				if($props['validation'] && !\in_array($props['validation'], Array(self::VALIDATE_STRING, self::VALIDATE_STRING_EMPTY, self::VALIDATE_BOOLEAN, self::VALIDATE_CALLBACK)) && $props['type'] != self::FIELD_PROTECTED && !isset($this->data[$field]))
				{
					$this->invalid_fields[] = $field;

					continue;
				}

				switch($props['validation'])
				{
					case(self::VALIDATE_NUMERIC):
					{
						if((!isset($this->data[$field]) && $props['type'] == self::FIELD_REQUIRED) || !\is_numeric($this->data[$field]))
						{
							$this->invalid_fields[] = $field;

							continue;
						}
					}
					break;
					case(self::VALIDATE_STRING_EMPTY):
					{
						$this->data[$field] = (isset($this->data[$field]) ? (string) $this->data[$field] : '');

						continue;
					}
					case(self::VALIDATE_STRING):
					{
						if((!isset($this->data[$field]) && $props['type'] == self::FIELD_REQUIRED) || empty($this->data[$field]))
						{
							$this->invalid_fields[] = $field;

							continue;
						}
					}
					break;
					case(self::VALIDATE_EMAIL):
					{
						if((!isset($this->data[$field]) && $props['type'] == self::FIELD_REQUIRED) || !\filter_var($this->data[$field], \FILTER_VALIDATE_EMAIL))
						{
							$this->invalid_fields[] = $field;

							continue;
						}
					}
					break;
					case(self::VALIDATE_BOOLEAN):
					{
						$this->data[$field] = (isset($this->data[$field]) ? (boolean) $this->data[$field] : (isset($props['default']) ? (boolean) $props['default'] : false));

						continue;
					}
					case(self::VALIDATE_CALLBACK):
					{
						$value = (isset($this->data[$field]) ? $this->data[$field] : NULL);

						if(!isset($props['callback']) || !\is_callable($props['callback']) || !\call_user_func($props['callback'], $this, $this->registry, $value))
						{
							$this->invalid_fields[] = $field;

							continue;
						}
					}
					break;
					case(self::VALIDATE_IDENTIFIER):
					{
						if(!isset($this->data[$field]) || empty($this->data[$field]))
						{
							$this->invalid_fields[] = $field;

							continue;
						}

						if($this->identifier)
						{
							$exists = \in_array($field, $this->identifier_data);

							if($this->identifier != $this->data[$field])
							{
								if($exists)
								{
									$this->invalid_fields[] = $field;

									continue;
								}
							}
							elseif($exists)
							{
								$this->invalid_fields[] = $field;

								continue;
							}
						}
						else
						{
							$this->reidentify = true;
						}
					}
					break;
					default:
					{
						if($props['type'] != self::FIELD_VIRTUAL)
						{
							$this->invalid_fields[] = $field;
						}
					}
					break;
				}
			}

			if($this->invalid_fields)
			{
				return(false);
			}

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
		 * @throws	\Tuxxedo\Exception\Multi	Throws a multi exception if validation fails
		 */
		public function save($execute_hooks = true)
		{
			if($this->context == self::CONTEXT_VOID)
			{
				return(false);
			}

			$this->context = self::CONTEXT_SAVE;

			if(!$this->validate())
			{
				$intl		= $this->registry->intl && ($this->options & (self::OPT_INTL | self::OPT_INTL_AUTO));
				$multidata 	= Array();

				foreach($this->invalid_fields as $field)
				{
					$multidata[$field] = ($intl && ($phrase = $this->registry->intl->find('dm_' . $this->dmname . '_' . $field, 'datamanagers')) !== false ? $phrase : $field);
				}

				$this->context = self::CONTEXT_NONE;

				throw new Exception\Multi($multidata, ($intl ? $this->registry->intl->find('validation_failed', 'datamanagers') : ''));
			}

			$values		= '';
			$virtual	= ($this->identifier !== NULL ? \array_merge(Array($this->idname => $this->identifier), $this->data) : $this->data);
			$virtual_fields	= $this->getVirtualFields();
			$n 		= \sizeof($virtual);

			if($virtual_fields)
			{
				$n -= \sizeof($virtual_fields);
			}

			$new_identifier = isset($this->data[$this->idname]) && !$this->reidentify;
			$sql		= ($new_identifier ? 'UPDATE `' . $this->tablename . '` SET ' : (($this->options & self::OPT_LOAD_ONLY) ? 'INSERT INTO' : 'REPLACE INTO') . ' `' . $this->tablename . '` (');

			foreach($virtual as $field => $data)
			{
				if(($field == $this->idname && ($this->options & self::OPT_LOAD_ONLY)) || isset($this->fields[$field]['type']) && $this->fields[$field]['type'] == self::FIELD_VIRTUAL)
				{
					if($field == $this->idname && ($this->options & self::OPT_LOAD_ONLY))
					{
						--$n;
					}

					continue;
				}

				if($new_identifier)
				{
					$sql .= '`' . $field . '` = ' . (is_null($data) ? ($this->fields[$field]['validation'] == self::VALIDATE_NUMERIC || $this->fields[$field]['validation'] == self::VALIDATE_BOOLEAN ? '0' : 'NULL') : '\'' . $this->registry->db->escape($data) . '\'') . (--$n ? ', ' : '');
				}
				else
				{
					$sql 	.= '`' . $field . '`' . (--$n ? ', ' : '');
					$values .= (is_null($data) ? ($this->fields[$field]['validation'] == self::VALIDATE_NUMERIC || $this->fields[$field]['validation'] == self::VALIDATE_BOOLEAN ? '0' : 'NULL') : '\'' . $this->registry->db->escape($data) . '\'') . ($n ? ', ' : '');
				}
			}

			if($new_identifier)
			{
				$sql .= ' WHERE `' . $this->idname . '` = \'' . $this->registry->db->escape($this->identifier) . '\'';
			}
			else
			{
				$sql .= ') VALUES (' . $values . ')';
			}

			if(!$this->registry->db->query($sql))
			{
				$this->context = self::CONTEXT_NONE;

				return(false);
			}

			if(($new_id = $this->registry->db->getInsertId()))
			{
				$this->data[$this->idname] = $new_id;
			}

			if($execute_hooks)
			{
				if(!$this->parent)
				{
					$result 	= $this->hooks($this);
					$this->context 	= self::CONTEXT_NONE;

					return($result);
				}

				$this->parent->setShutdownHandler(Array($this, 'hooks'), Array($this));
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
			if($this->context == self::CONTEXT_VOID)
			{
				return(false);
			}

			$this->invalid_fields = Array();

			if($this->identifier === NULL && !($this->options & self::OPT_LOAD_ONLY))
			{
				return(true);
			}

			$this->context = self::CONTEXT_DELETE;

			if(($this instanceof Hooks\Cache && !$this->rebuild()))
			{
				$this->context = self::CONTEXT_NONE;

				return(false);
			}

			$this->context = self::CONTEXT_VOID;

			return($this->registry->db->equery('
								DELETE FROM 
									`' . $this->tablename . '`
								WHERE 
									`' . $this->idname . '` = \'%s\'', ($this->options & self::OPT_LOAD_ONLY ? $this->data[$this->idname] : $this->identifier)));
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

		/**
		 * Gets the fields this datamanager provides
		 *
		 * @return	array				Returns an array with the fields
		 */
		public function getFields()
		{
			return(\array_keys($this->fields));
		}

		/**
		 * Gets default data to allocate the $data property internally
		 *
		 * @return	array				Returns an array with the same structure as the $data property and false on error
		 */
		public function getDataStruct()
		{
			$data = Array();

			foreach($this->fields as $name => $props)
			{
				$data[$name] = (isset($props['default']) ? $props['default'] : '');
			}

			return($data);
		}

		/**
		 * Iterator method - current
		 * 
		 * @return	mixed				Returns the current field
		 */
		public function current()
		{
			return(\key($this->data));
		}

		/**
		 * Iterator method - rewind
		 *
		 * @return	void				No value is returned
		 */
		public function rewind()
		{
			\reset($this->data);

			$this->iterator_position = 0;
		}

		/**
		 * Iterator method - key
		 *
		 * @return	integer				Returns the currrent index
		 */
		public function key()
		{
			return($this->iterator_position);
		}

		/**
		 * Iterator method - next
		 *
		 * @return	void				No value is returned
		 */
		public function next()
		{
			if(\next($this->data) !== false)
			{
				++$this->iterator_position;
			}
		}

		/**
		 * Iterator method - valid
		 *
		 * @return	boolean				Returns true if its possible to continue iterating, otherwise false is returned
		 */
		public function valid()
		{
			return(\sizeof($this->data) - 1 != $this->iterator_position);
		}

		/**
		 * Hooks executor
		 *
		 * This method executes hooks on a datamanager instance, this is cannot be 
		 * called publically.
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The datamanager adapter instance to execute hooks on
		 * @return	boolean				Returns true if all fields 
		 */
		protected function hooks(Adapter $self)
		{
			if(($self instanceof Hooks\Cache && !$self->rebuild()) || ($self instanceof Hooks\Recache && !$self->recache()))
			{
				return(false);
			}

			$dispatch 	= ($self instanceof Hooks\VirtualDispatcher);
			$virtual	= $this->getVirtualFields();

			if($virtual && ($dispatch || $self instanceof Hooks\Virtual))
			{
				foreach($virtual as $field => $value)
				{
					if($dispatch)
					{
						$method = 'virtual' . $field;

						if(\method_exists($self, $method) && !$self->{$method}($value))
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
		}
	}
?>