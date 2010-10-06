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
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


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
		 * @throws	\Tuxxedo\Exception\SQL	Throws an exception if the query should fail (only if the delay parameter is set to false)
		 */
		public function rebuild($name, Array $data = NULL, $delay = true)
		{
			if($data === NULL)
			{
				$sql = \sprintf('
							DELETE FROM 
								`' . \TUXXEDO_PREFIX . 'datastore` 
							WHERE 
								`name` = \'%s\';', $this->registry->db->escape($name));
			}
			else
			{
				$sql = \sprintf('
							REPLACE INTO 
								`' . \TUXXEDO_PREFIX . 'datastore` 
								(
									`name`, 
									`data`
								) 
							VALUES 
								(
								\'%s\', 
									\'%s\'
								);', $this->registry->db->escape($name), $this->registry->db->escape(\serialize($data)));
			}

			if($delay)
			{
				$this->registry->db->setShutdownQuery($sql);

				return(true);
			}

			$retval = $this->registry->db->query($sql);

			if($retval)
			{
				if($data === NULL)
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
			return(isset($this->cache[$element]));
		}
	}
?>