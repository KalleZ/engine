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
	 * Abstract datamanager class
	 *
	 * Every datamanager class must extend this class in order to be loadable and to 
	 * comply with the datamanager API. This also contains the factory method used 
	 * to instanciate a new datamanager instance.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	abstract class Tuxxedo_Datamanager extends Tuxxedo_InfoAccess
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
		 * Validation constant, numeric value
		 *
		 * @var		integer
		 */
		const VALIDATE_NUMERIC			= Tuxxedo_Filter::TYPE_NUMERIC;

		/**
		 * Validation constant, string value
		 *
		 * @var		integer
		 */
		const VALIDATE_STRING			= Tuxxedo_Filter::TYPE_STRING;

		/**
		 * Validation constant, email value
		 *
		 * @var		integer
		 */
		const VALIDATE_EMAIL			= Tuxxedo_Filter::TYPE_EMAIL;

		/**
		 * Validation constant, boolean value
		 *
		 * @var		integer
		 */
		const VALIDATE_BOOLEAN			= Tuxxedo_Filter::TYPE_BOOLEAN;

		/**
		 * Validation constant, callback
		 *
		 * @var		integer
		 */
		const VALIDATE_CALLBACK			= Tuxxedo_Filter::TYPE_CALLBACK;

		/**
		 * Validation option constant, escape HTML
		 *
		 * @var		integer
		 */
		const VALIDATE_OPT_ESCAPEHTML		= 0x001F;


		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

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
		 * The original data if instanciated by an identifier
		 *
		 * @var		array
		 */
		protected $data				= Array();

		/**
		 * Current data thats been set via the set method
		 *
		 * @var		array
		 */
		protected $userdata			= Array();

		/**
		 * List of loaded datamanagers used for caching in the 
		 * special required cases where more than one driver 
		 * have to be loaded
		 *
		 * @var		array
		 */
		protected static $loaded_datamanagers 	= Array();

		/**
		 * List of fields that had one or more errors and therefore 
		 * could not be saved
		 *
		 * @var		array
		 */
		protected $invalid_fields		= Array();


		/**
		 * Constructor for the current datamanager, this 
		 * can be used to either create a datamanager based 
		 * on a certain record determined by the passed identifier 
		 * or as a clean datamanager to insert a new record.
		 *
		 * @param	mixed			The unique identifier to send to the datamanager
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the unique identifier sent to the datamanager was invalid
		 */
		abstract public function __construct($identifier = NULL);

		/**
		 * Constructs a new datamanger instance
		 *
		 * @param	string				Datamanger name
		 * @param	mixed				An identifier to send to the datamanager to load default data upon instanciating it
		 * @param	boolean				Whether to use internationalization for formdata exceptions
		 * @return	Tuxxedo_Datamanager		Returns a new database instance
		 *
		 * @throws	Tuxxedo_Basic_Exception		Throws a basic exception if loading of a datamanger should fail for some reason
		 *
		 * @note	If loading from the datastore, then only the data in the cache will be loaded, meaning 
		 * 		that not all the data that exists in the database may be available.
		 */
		final public static function factory($datamanager, $identifier = NULL, $intl = true)
		{
			global $tuxxedo;

			if($intl)
			{
				if(!$tuxxedo->intl)
				{
					throw new Tuxxedo_Basic_Exception('Initialization is not instanciated for form data phrases');
				}

				if(!$tuxxedo->intl->cache(Array('datamanagers')))
				{
					throw new Tuxxedo_Basic_Exception('Unable to cache datamanager phrases');
				}
			}

			if(in_array($datamanager, self::$loaded_datamanagers))
			{
				return(new $class($tuxxedo, $identifier));
			}

			$class	= 'Tuxxedo_Datamanager_' . $datamanager;
			$dm 	= new $class($tuxxedo, $identifier);

			if(!is_subclass_of($class, __CLASS__))
			{
				throw new Tuxxedo_Basic_Exception('Corrupt datamanager driver, driver class does not follow the driver specification');
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
			elseif(isset($this->userdata[$field]))
			{
				return($this->userdata[$field]);
			}
			elseif(isset($this->data[$field]))
			{
				return($this->data[$field]);
			}
		}

		/**
		 * Validation method, validates the supplied user data 
		 *
		 * @return	boolean				Returns true if the data is valid, otherwise false
		 */
		public function validate()
		{
			$this->invalid_fields = Array();

			if(!sizeof($this->userdata))
			{
				return(true);
			}

			if(!$this->tuxxedo->filter)
			{
				$this->tuxxedo->register('filter', 'Tuxxedo_Filter');
			}

			foreach($this->fields as $field => $properties)
			{
				switch($properties['type'])
				{
					case(self::FIELD_REQUIRED):
					{
						if(!isset($this->userdata[$field]))
						{
							if(!isset($this->data[$field]))
							{
								$this->invalid_fields[] = $field;
							}

							continue 2;
						}
					}
					break;
					case(self::FIELD_PROTECTED):
					{
						if(isset($this->userdata[$field]))
						{
							$this->invalid_fields[] = $field;

							continue 2;
						}

						if(isset($properties['default']))
						{
							$this->data[$field] = $properties['default'];
						}
						elseif(isset($properties['callback']) && is_callable($properties['callback']))
						{
							if(isset($properties['parameters']))
							{
								$this->data[$field] = call_user_func_array($properties['callback'], array_merge(Array($this->tuxxedo), $properties['parameters']));
							}
							else
							{
								$this->data[$field] = call_user_func($properties['callback'], $this->tuxxedo);
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

				if($properties['validation'] == self::VALIDATE_CALLBACK && isset($properties['callback']))
				{
					if(!$this->tuxxedo->filter->validate($this->userdata[$field], $properties['callback']))
					{
						$this->invalid_fields[] = $field;

						continue;
					}
				}
				else
				{
					$filtered = $this->tuxxedo->filter->user($this->userdata[$field], $properties['validation']);

					if($filtered === NULL)
					{
						$this->invalid_fields[] = $field;
						unset($this->userdata[$field]);

						continue;
					}

					$this->userdata[$field] = $filtered;
				}
			}

			return(!sizeof($this->invalid_fields));
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

			$this->fields[$field]['type'] = (integer) $type;

			return(true);
		}

		/**
		 * Save method, attempts to validate and save the data 
		 * into the database
		 *
		 * @return	boolean				Returns true if the data is saved with success, otherwise boolean false
		 *
		 * @throws	Tuxxedo_Basic_Exception		Throws a basic exception if the query should fail
		 * @throws	Tuxxedo_FormData_Exception	Throws a formdata exception if validation fails
		 */
		public function save()
		{
			if(!$this->validate())
			{
				global $phrase;

				$intl		= isset($this->tuxxedo->intl);
				$formdata 	= Array();

				foreach($this->invalid_fields as $field)
				{
					$formdata[$field] = ($intl && isset($phrase['dm_' . $this->dmname . '_' . $field]) ? $phrase['dm_' . $this->dmname . '_' . $field] : $field);
				}

				throw new Tuxxedo_Formdata_Exception($formdata);
			}

			$values		= '';
			$sql 		= 'REPLACE INTO `' . $this->tablename . '` (';
			$virtual	= array_merge($this->data, $this->userdata);
			$virtual	= ($this->identifier !== NULL ? array_merge(Array($this->idname => $this->identifier), $virtual) : $virtual);
			$n 		= sizeof($virtual);

			foreach($virtual as $field => $data)
			{
				if(isset($this->fields[$field]['validation']) && $this->fields[$field]['validation'] & self::VALIDATE_OPT_ESCAPEHTML)
				{
					$data = htmlspecialchars($data, ENT_QUOTES);
				}

				$sql 	.= '`' . $field . '`' . (--$n ? ', ' : '');
				$values .= '\'' . $this->tuxxedo->db->escape($data) . '\'' . ($n ? ', ' : '');
			}

			if(!$this->tuxxedo->db->query($sql . ') VALUES (' . $values . ')'))
			{
				return(false);
			}

			if($this instanceof Tuxxedo_Datamanager_API_Cache)
			{
				return($this->rebuild($this->tuxxedo, $virtual));
			}

			return(true);
		}

		/**
	 	 * Deletes the data, within the database if an identifier was specified, else 
		 * the current set data is removed
		 *
		 * @return	boolean				Returns true if the deletion was a success otherwise boolean false
		 *
		 * @throws	Tuxxedo_Basic_Exception		Throws a basic exception if the query should fail
		 */
		public function delete()
		{
			if($this->identifier === NULL)
			{
				$this->invalid_fields = $this->userdata = Array();

				return(true);
			}

			return($this->tuxxedo->db->query('
								DELETE FROM 
									`' . $this->tablename . '`
								WHERE 
									`' . $this->idname .'` = \'%s\'', $this->tuxxedo->db->escape($this->identifier)));
		}
	}


	/**
	 * Datastore requirement for using the datamanager
	 *
	 * This interface is for datamanagers that interacts with the datastore 
	 * cache to rebuild it to prevent manual update of it.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface Tuxxedo_Datamanager_API_Cache
	{
		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			A virtually populated array from the datamanager abstraction
		 * @return	boolean			Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Tuxxedo $tuxxedo, Array $virtual);
	}
?>