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
	use Tuxxedo\Datamanager;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datastore cache, this enables datastore caching for 
	 * databases. This assumes the datastore table and 
	 * everything else required for a database based 
	 * datastore is setup.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Datastore
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Holds the cached elements from the datastore
		 *
		 * @var		array
		 */
		protected $cache	= Array();


		/**
		 * Constructor
		 */
		public function __construct()
		{
			$this->registry = Registry::init();
		}

		/**
		 * Gets a reference to the loaded element, so that it can be 
	 	 * referenced in memory rather than copied
		 *
		 * @param	string			The datastore element to get a reference of
		 * @param	array			Returns a reference to an array for the element, or boolean false on error
		 */
		public function getRef($name, &$data)
		{
			if(isset($this->cache[$name]))
			{
				$data = $this->cache[$name];
			}
		}

		/**
		 * Quick reference for overloading of a loaded 
		 * element in the datastore.
		 *
		 * @param	string			The datastore element to get
		 * @return	array			Returns an array is returned, otherwise boolean false on error
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
		 * Allows the usage of isset() on datastore elements
		 *
		 * @param	string			The datastore element to check
		 * @return	boolean			Returns true if the element is loaded otherwise false
		 */
		public function __isset($name)
		{
			return(isset($this->cache[$name]) && !empty($this->cache[$name]));
		}

		/**
		 * Allows the usge of unset() on datastore elements
		 *
		 * @param	string			The datastore element to unload
		 * @return	void			No value is returned
		 */
		public function __unset($name)
		{
			if(isset($this->cache[$name]))
			{
				unset($this->cache[$name]);
			}
		}

		/**
		 * Unloads a datastore element from the loaded cache
		 *
		 * @param	string			The datastore element to free from cache
		 * @return	void			No value is returned
		 */
		public function unload($name)
		{
			$this->__unset($name);
		}

		/**
		 * Rebuilds a datastore element if it already exists, or adds 
		 * a new entry in the datastore if no elements with that name 
		 * already exists. To delete a datastore element completely,  
		 * the data parameter must be set to NULL
		 *
		 * @param	string			The datastore element
		 * @param	mixed			This can be either an array or object, if this is NULL then the datastore is deleted completely
		 * @return	boolean			True on success, otherwise false on error
		 *
		 * @throws	\Tuxxedo\Exception\SQL	Throws an exception if the query should fail
		 */
		public function rebuild($name, Array $data = NULL)
		{
			try
			{
				$dm = Datamanager\Adapter::factory('datastore', $name);

				if($data === NULL)
				{
					unset($this->cache[$name]);

					return($dm->delete());
				}
			}
			catch(Exception $e)
			{
				if($data === NULL)
				{
					unset($this->cache[$name]);

					return(true);
				}

				$dm 		= Datamanager\Adapter::factory('datastore');
				$dm['name']	= $name;
			}

			$dm['data'] = $data;

			if(!$dm->save())
			{
				return(false);
			}

			$this->cache[$name] = $data;

			return(true);
		}

		/**
		 * Caches a set of elements from the datastore into 
		 * the current cache.
		 *
		 * @param	array			An array, where the values are the datastore element names
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			True on success, otherwise false
		 *
		 * @throws	\Tuxxedo\Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $elements, Array &$error_buffer = NULL)
		{
			$elements = \array_filter($elements, Array($this, 'filter'));

			if(!$elements)
			{
				return(false);
			}

			$result = $this->registry->db->query('
								SELECT 
									`name`, 
									`data` 
								FROM 
									`' . \TUXXEDO_PREFIX . 'datastore` 
								WHERE 
									`name` 
									IN
									(
										\'%s\'
									);', \join('\', \'', \array_map(Array($this->registry->db, 'escape'), $elements)));

			if($result === false)
			{
				if($error_buffer !== NULL)
				{
					$error_buffer = $elements;
				}

				return(false);
			}

			$loaded = Array();

			while($row = $result->fetchAssoc())
			{
				$row['data'] = @\unserialize($row['data']);

				if($row['data'] !== false)
				{
					$loaded[] 			= $row['name'];
					$this->cache[$row['name']] 	= $row['data'];
				}
			}

			if($error_buffer !== NULL)
			{
				if(($diff = \array_diff($elements, $loaded)))
				{
					$error_buffer = $diff;
				}

				return(false);
			}

			return(true);
		}

		/**
		 * Filters out already loaded elements in the datastore
		 *
		 * @param	string				The datastore element to check
		 * @return	boolean				Returns true if the element not is loaded, otherwise false
		 */
		protected function filter($element)
		{
			return(!isset($this->cache[$element]));
		}
	}
?>