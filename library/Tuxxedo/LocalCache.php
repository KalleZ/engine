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
	use Tuxxedo\Database;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Local cache registry
	 *
	 * This class implements a localized cache registry for a 
	 * script request or instance. This works much like the 
	 * datastore cache except that it works with live data.
	 *
	 * The design and need for this class comes from as a need 
	 * from the datamanager component orignally to prevent having 
	 * to send queries to the database and sacrifice that network 
	 * communication for script memory.
	 *
	 * <code>
	 * use Tuxxedo\LocalCache;
	 * use Tuxxedo\Registry;
	 *
	 * // Registry
	 * $registry = Registry::init();
	 *
	 * // Load LocalCache
	 * $registry->set('lcache', new LocalCache);
	 *
	 * // We need to get all the books, but our library 
	 * // is big, and have over 1000 entries, so to prevent 
	 * // filling up the entire memory with entries we may 
	 * // not need we are a little specific
	 * 
	 * // The third and optional parameter can be used to 
	 * // specify an alias for that table if the cache is 
	 * // being used over multiple tables with the same 
	 * // name. By default it uses the same name as the table 
	 * // for calls to LocalCache::find(), ...
	 *
	 * // Internally the following is executed
	 * //
	 * //   1) SHOW COLUMNS FROM `books`
	 * //   2) SELECT * FROM `books` WHERE `title` 'PHP%'
	 * $lcache->load('books', Array('title' => 'PHP*'));
	 *
	 * // Find the number of records loaded into cache
	 * printf('There is currently %d book(s) matching \'PHP*\' loaded', $lcache->getNum('books'));
	 *
	 * // We already know that the book we want to find have 
	 * // the id of '42'
	 * //
	 * // If this book is loaded into cache, then the records 
	 * // that matches the id of 42 is returned
	 * $book = $lcache->findSpecific('books', Array('id' => 42));
	 *
	 * // We can also get all the books loaded, but we only want the 
	 * // title of each
	 * $books = $lcache->find('books', Array('title'));
	 *
	 * // List them
	 * echo '<ul>';
	 *
	 * foreach($books as $book)
	 * {
	 * 	printf('<li>%s</li>', $book['title']);
	 * }
	 *
	 * echo '</ul>';
	 *
	 * // We can also unload cache to free the memory or to 
	 * // force a recache
	 * $lcache->unload('books');
	 *
	 * printf('There is currently %d book entries loaded', $lcache->getNum('books'));
	 * </code>
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class LocalCache
	{
		/**
		 * Database instance
		 *
		 * @var		\Tuxxedo\Database
		 */
		protected $db;

		/**
		 * Holds the cached elements from the datastore
		 *
		 * @var		array
		 */
		protected $cache	= Array();


		/**
		 * Constructor
		 *
		 * @param	\Tuxxedo\Database		The database instance to use, if none is supplied the registry registered one is used
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the database instance is not loaded
		 */
		public function __construct(Database $db = NULL)
		{
			if(!$db)
			{
				$registry = Registry::init();

				if(!$registry->db)
				{
					throw new Exception\Basic('The local cache requires a database instance to function');
				}

				$db = $registry->db;
			}

			$this->db = $db;
		}

		/**
		 * Loads a specific table into the cache
		 *
		 * @param	string				The table name
		 * @param	array				An array of conditions to load specific entries or limit them
		 * @param	string				The alias if any (defaults to the table name if NULL)
		 * @param	boolean				Whether or not to add the configuration database table prefix (defaults to true)
		 * @return	boolean				Returns true if the entries was loaded, otherwise false. True can also be returned if none entries was loaded
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an SQL exception if a query should fail
		 */
		public function load($table, Array $conditions = Array(), $alias = NULL, $add_table_prefix = true)
		{
			if($alias === NULL)
			{
				$alias = $table;
			}

			$alias = \strtolower($alias);

			if(isset($this->cache[$alias]))
			{
				unset($this->cache[$alias]);
			}

			$columns = $this->db->query('SHOW COLUMNS FROM `%s`', ($add_table_prefix ? \TUXXEDO_PREFIX : '') . $table);

			if(!$columns || !$columns->getNumRows())
			{
				return(false);
			}

			$fields = Array();

			foreach($columns as $column)
			{
				$fields[] = \strtolower($column['Field']);
			}

			$columns->free();

			$sql = 'SELECT * FROM `' . ($add_table_prefix ? \TUXXEDO_PREFIX : '') . $table . '`';

			if($conditions)
			{
				$added	= false;
				$sql 	.= 'WHERE ';

				foreach($conditions as $column => $value)
				{
					$column = \strtolower($column);

					if(!\in_array($column, $fields))
					{
						continue;
					}

					$added = true;

					$sql .= '`' . $column . '` = \'' . \str_replace('*', '%', $this->db->escape($value)) . '\' ';
				}

				if(!$added)
				{
					$sql = \substr($sql, -6);
				}

				$sql = \rtrim($sql);
			}

			$entries = $this->db->query($sql);

			if(!$entries || !$entries->getNumRows())
			{
				return(true);
			}

			$this->cache[$alias] = Array();

			foreach($entries as $entry)
			{
				$entry = \array_change_key_case($entry, \CASE_LOWER);

				$this->cache[$alias][] = $entry;
			}

			return(true);
		}

		/**
		 * Gets the number of entries for a specific entry
		 *
		 * @param	string				The cache name
		 * @return	integer				Returns the number of entries, or 0 on an invalid entry
		 */
		public function getNum($entry)
		{
			$entry = \strtolower($entry);

			if(!isset($this->cache[$entry]))
			{
				return(0);
			}

			return(\sizeof($this->cache[$entry]));
		}

		/**
		 * Internal lookup method
		 *
		 * @param	string				The cache name
		 * @param	array				The fields to return or NULL for everything (default)
		 * @return	array				Returns the entries, or false on error. An empty array can be returned
		 */
		protected function lookup($entry, Array $fields = NULL)
		{
			$entry = \strtolower($entry);

			if(!isset($this->cache[$entry]))
			{
				return(false);
			}

			\reset($this->cache[$entry]);

			$x		= 0;
			$retval 	= Array();
			$cfields	= \array_keys(\current($this->cache[$entry]));

			foreach($this->cache[$entry] as $row)
			{
				if($fields === NULL || !$fields)
				{
					$retval[$x++] = $row;

					continue;
				}

				$added		= false;
				$retval[$x] 	= Array();

				foreach($fields as $field)
				{
					$field = \strtolower($field);

					if(!\in_array($field, $cfields))
					{
						continue;
					}

					$added 			= true;
					$retval[$x][$field] 	= $row[$field];
				}

				if(!$added)
				{
					unset($retval[$x]);

					continue;
				}

				++$x;
			}

			return($retval);
		}

		/**
		 * Finds an entry
		 *
		 * @param	string				The cache name
		 * @param	array				The fields to return or NULL for everything (default)
		 * @return	array				Returns the entries, or false on error. An empty array can be returned. If only one result is returned, then a non multi dimentional array is returned
		 */
		public function find($entry, Array $fields = NULL)
		{
			$entries = $this->lookup($entry, $fields);

			if(\sizeof($entries) == 1)
			{
				\reset($entries);

				$entries = \current($entries);
			}

			return($entries);
		}

		/**
		 * Finds a specific entry
		 *
		 * @param	string				The cache name
		 * @param	array				An array of conditions (such as values that must pass in key => value pairs, if a value is an array then one or more of the values must exists to pass)
		 * @param	array				The fields to return or NULL for everything (default)
		 * @return	array				Returns the entries, or false on error. An empty array can be returned. If only one result is returned, then a non multi dimentional array is returned
		 */
		public function findSpecific($entry, Array $conditions, Array $fields = NULL)
		{
			$entries = $this->lookup($entry, $fields);

			if(!$entries || !$conditions)
			{
				return($entries);
			}

			foreach($entries as $key => $values)
			{
				if(!$values)
				{
					unset($entries[$key]);

					continue;
				}

				foreach($conditions as $condition => $expr)
				{
					foreach($values as $index => $value)
					{
						if($condition != $index)
						{
							continue;
						}

						if($value != $expr || \is_array($expr) && !\in_array($value, $expr))
						{
							unset($entries[$key]);

							continue;
						}
					}
				}
			}

			if(\sizeof($entries) == 1)
			{
				\reset($entries);

				$entries = \current($entries);
			}

			return($entries);
		}

		/**
		 * Unloads one or more cached entries
		 *
		 * @param	string|array			The cache name, or an array of names
		 * @return	void				No value is returned
		 */
		public function unload($entries)
		{
			if(\is_array($entries))
			{
				if(!$entries)
				{
					return;
				}

				foreach($entries as $entry)
				{
					unset($this->cache[$entry]);
				}

				return;
			}

			unset($this->cache[$entries]);
		}
	}
?>