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
	 * Datamanagers adapter namespace, this contains all the different 
	 * datamanager handler implementations to comply with the standard 
	 * adapter interface, and with the plugins for hooks.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Datamanager\Adapter;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Datamanager\Hooks;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for permissions
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Permission extends Adapter implements Hooks\Cache
	{
		/**
		 * Fields for validation of permissions
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'name'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'bits'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_NUMERIC
											)
							);


		/**
		 * Constructor, fetches a new permission based on its name if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The usergroup name
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'permission';
			$this->tablename	= \TUXXEDO_PREFIX . 'permissions';
			$this->idname		= 'name';

			if($identifier !== NULL)
			{
				$permission = $registry->db->equery('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'permissions` 
									WHERE 
										`name` = \'%s\'', $identifier);

				if(!$permission || !$permission->getNumRows())
				{
					throw new Exception('Invalid permission name passed to datamanager');
				}

				$this->data 		= $permission->fetchAssoc();
				$this->identifier 	= $identifier;

				$permission->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Save the permission in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Array $virtual)
		{
			if($this->context == self::CONTEXT_DELETE && !isset($this->registry->cache->permissions[$this->data['name']]))
			{
				return(true);
			}

			$name		= (isset($virtual['name']) ? $virtual['name'] : $this->data['name']);
			$permissions	= $this->registry->cache->permissions;

			unset($permissions[$name]);

			if($this->context == self::CONTEXT_SAVE)
			{
				$permissions[$name] = $this['bits'];
			}

			return($this->registry->cache->rebuild('permissions', $permissions, false));
		}
	}
?>