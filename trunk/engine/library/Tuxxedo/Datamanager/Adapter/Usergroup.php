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
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for usergroups
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Usergroup extends Adapter implements Hooks\Cache
	{
		/**
		 * Fields for validation of usergroups
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'id'		=> Array(
											'type'		=> self::FIELD_PROTECTED
											), 
							'title'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'permissions'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_NUMERIC, 
											'default'	=> 0
											)
							);


		/**
		 * Constructor, fetches a new usergroup based on its id if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The usergroup id
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'usergroup';
			$this->tablename	= \TUXXEDO_PREFIX . 'usergroups';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$usergroup = $registry->db->query('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'usergroups` 
									WHERE 
										`id` = %d', $identifier);

				if(!$usergroup || !$usergroup->getNumRows())
				{
					throw new Exception('Invalid usergroup id passed to datamanager');
				}

				$this->data 			= $usergroup->fetchAssoc();
				$this->data['permissions']	= (integer) $this->data['permissions'];
				$this->identifier 		= $identifier;

				$usergroup->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Save the usergroup in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Array $virtual)
		{
			if($this->context == self::CONTEXT_DELETE && !isset($this->registry->datastore->usergroups[$this->data['id']]))
			{
				return(true);
			}

			$id		= (isset($virtual['id']) ? $virtual['id'] : $this->data['id']);
			$usergroups	= $this->registry->datastore->usergroups;

			unset($usergroups[$id]);

			if($this->context == self::CONTEXT_SAVE)
			{
				$query = $this->registry->db->query('
									SELECT 
										COUNT(`id`) as \'count\' 
									FROM 
										`' . \TUXXEDO_PREFIX . 'users` 
									WHERE 
										`usergroupid` = %d', $id);

				$virtual['users']	= ($query && $query->getNumRows() ? (integer) $query->fetchObject()->count : 0);
				$usergroups[$id] 	= $virtual;
			}

			\ksort($usergroups);

			return($this->registry->datastore->rebuild('usergroups', $usergroups));
		}
	}
?>