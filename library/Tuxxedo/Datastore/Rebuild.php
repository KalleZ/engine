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
	 * Datastore namespace, this contains routines for rebuilding known 
	 * data structures thats used by the core Engine and can be extended 
	 * in the same manner as other components that implements a factory 
	 * pattern.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Datastore;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datastore rebuilder
	 *
	 * This class will work with adapters to correct rebuild datastore 
	 * components.
	 *
	 * There are several ways to rebuild the data, that can prove useful 
	 * in different application states:
	 *
	 * 1) Rebuild::OPT_TRUNCATE
	 *
	 *    This option truncates the entire datastore component, leaving 
	 *    it still loadable but as an empty array.
	 *
	 * 2) Rebuild::OPT_TRUNCATE_SINGLE
	 *
	 *    This option deletes a single row based on the key.
	 *
	 * 3) Rebuild::OPT_SYNC
	 *
	 *    This updates all the rows, and is the default option when 
	 *    instanciating the rebuild class.
	 *
	 * 4) Rebuild::OPT_SYNC_SINGLE
	 *
	 *    This updates a single row based on the key with new data, if 
	 *    the data however is no longer available, this row will be 
	 *    deleted from the array.
	 *
	 * While the class only needs the datastore component name and a key 
	 * if one of the single options is supplied, the rebuild class will 
	 * return an instance with a save method that must be called. The 
	 * reason for this is that this instance can be saved as a shutdown 
	 * callback.
	 *
	 * <code>
	 * use Tuxxedo\Datastore\Rebuild;
	 *
	 * // We updated usergroup #1 somewhere in our code, and need to resync it, 
	 * // while the usergroup datamanager does that internally, this is more of 
	 * // a proof-of-concept:
	 * Rebuild::factory('usergroups', Rebuild::OPT_SYNC_SINGLE, 1)->save();
	 * </code>
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class Rebuild
	{
		/**
		 * Option constant - Truncate all
		 *
		 * @var		integer
		 */
		const OPT_TRUNCATE			= 1;

		/**
		 * Option constant - Truncate single
		 *
		 * @var		integer
		 */
		const OPT_TRUNCATE_SINGLE		= 2;

		/**
		 * Option constant - Sync all
		 *
		 * @var		integer
		 */
		const OPT_SYNC				= 3;

		/**
		 * Option constant - Sync single
		 *
		 * @var		integer
		 */
		const OPT_SYNC_SINGLE			= 4;


		/**
		 * The name of the datastore component


		/**
		 * Holds the loaded structures, thats been validated 
		 * and can be used.
		 *
		 * @var		array
		 */
		protected static $loaded_structures	= Array();


		/**
		 * Constructs a new instance for rebuilding a datastore component, this 
		 * method should not be executed directly, but rather thru the factory 
		 * method.
		 *
		 * @param	array				The method for rebuilding (one of the OPT_* constants)
		 * @param	scalar				A key if needed, this only have an effect on singular options
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if no key was supplied for a singular option or on invalid option
		 */
		public function __construct($method, $key = NULL)
		{
			if(\get_class($this) == __CLASS__)
			{
				throw new Exception\Basic('Cannot call base constructor directly from a non-initalized instance');
			}

			switch($method)
			{
				case(self::OPT_TRUNCATE_SINGLE):
				case(self::OPT_SYNC_SINGLE):
				{
					if(!$key || !isset($this->struct[$key]))
					{
						throw new Exception\Basic('Invalid key supplied to this singular option');
					}

					$this->key = $key;
				}
				case(self::OPT_TRUNCATE):
				case(self::OPT_SYNC):
				{
					$this->method = $method;
				}
				break;
				default:
				{
					throw new Exception\Basic('Invalid option supplied');
				}
				break;
			}
		}

		/**
		 * Constructs a new rebuild instance
		 *
		 * @param	string				Datastore component name
		 * @param	array				The method for rebuilding (one of the OPT_* constants)
		 * @param	scalar				A key if needed, this only have an effect on singular options
		 * @return	\Tuxxedo\Datastore\Rebuild	Returns a new datastore rebuild instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if loading of a structure should fail for some reason
		 */
		public static function factory($struct, $method, $key = NULL)
		{
			$class = (\strpos($struct, '\\') === false ? '\Tuxxedo\Datastore\Structure\\' : '') . $struct;

			if(\in_array($struct, self::$loaded_structures))
			{
				return(new $class($method, $key));
			}

			$instance = new $class($method, $key);

			if(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt datastore structure, structure class does not follow the specification');
			}

			self::$loaded_structures[] = $struct;

			return($instance);
		}

		/**
		 * Saves the datastore for this paticular component
		 *
		 * @return	boolean				Returns true if the operation was successful, otherwise false
		 *
		 * @throws	\Tuxxedo\Exception		May throw an exception depending on the component if saving fails
		 */
		public function save()
		{
			if(!($datastore = Registry::init()->datastore))
			{
				return(false);
			}

			$dm = Datamanager\Adapter::factory('datastore', $this->dmname);

			switch($this->method)
			{
				case(self::OPT_TRUNCATE):
				{
					$dm['data'] = Array();
				}
				break;
				case(self::OPT_TRUNCATE_SINGLE):
				{
					if(isset($dm[$this->key]))
					{
						unset($dm[$this->key]);
					}
				}
				break;
				case(self::OPT_SYNC):
				{
					$dm['data'] = self::getSyncedData();
				}
				break;
				case(self::OPT_SYNC_SINGLE):
				{
					$dm['data'] = self::getSyncedData($this->key);
				}
				break;
			}

			if($dm['data'] === false)
			{
				return(false);
			}

			return($dm->save());
		}

		/**
		 * Syncronization method that adapters must implement
		 *
		 * @param	scalar				A key if needed, this only have an effect on singular options
		 * @return	mixed				Returns the syncronized data, and boolean false on error
		 *
		 * @throws	\Tuxxedo\Exception		May throw an exception depending on the component if saving fails
		 */
		abstract protected static function getSyncedData($key = NULL);
	}
?>