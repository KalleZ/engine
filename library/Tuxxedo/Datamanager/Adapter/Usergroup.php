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
	use Tuxxedo\Helper;
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
	 *
	 * @changelog		1.2.0			This class now implements the 'VirtualDispatcher' hook
	 */
	class Usergroup extends Adapter implements Hooks\Cache, Hooks\VirtualDispatcher
	{
		/**
		 * Datamanager name
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const DM_NAME			= 'usergroup';

		/**
		 * Identifier name for the datamanager
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const ID_NAME			= 'id';

		/**
		 * Table name for the datamanager
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const TABLE_NAME		= 'usergroups';


		/**
		 * Fields for validation of usergroups
		 *
		 * @var		array
		 * 
		 * @changelog	1.2.0			Added the 'users' virtual field
		 * @changelog	1.2.0			Removed the 'type' field
		 */
		protected $fields		= [
							'id'		=> [
										'type'		=> parent::FIELD_PROTECTED
										], 
							'title'		=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_STRING
										], 
							'permissions'	=> [
										'type'		=> parent::FIELD_OPTIONAL, 
										'validation'	=> parent::VALIDATE_NUMERIC, 
										'default'	=> 0
										], 
							'users'		=> [
										'type'		=> parent::FIELD_VIRTUAL
										]
							];


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
		public function __construct(Registry $registry, $identifier = NULL, $options = parent::OPT_DEFAULT, Adapter $parent = NULL)
		{
			if($identifier !== NULL)
			{
				$usergroup = $registry->db->query('
									SELECT 
										* 
									FROM 
										"' . \TUXXEDO_PREFIX . 'usergroups" 
									WHERE 
										"id" = %d', $identifier);

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
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild()
		{
			if($this->context == parent::CONTEXT_DELETE && !isset($this->registry->datastore->usergroups[$this->data['id']]))
			{
				return(true);
			}

			$usergroups = $this->registry->datastore->usergroups;

			unset($usergroups[$this->information['id']]);

			if($this->context == parent::CONTEXT_SAVE)
			{
				$virtual	= $this->data;
				$query 		= $this->registry->db->query('
										SELECT 
											COUNT("id") AS "count" 
										FROM 
											"' . \TUXXEDO_PREFIX . 'users" 
										WHERE 
											"usergroupid" = %d', $this->information['id']);

				$virtual['users']		= ($query && $query->getNumRows() ? (integer) $query->fetchObject()->count : 0);
				$usergroups[$this->information['id']] 	= $virtual;
			}

			\ksort($usergroups);

			return($this->registry->datastore->rebuild('usergroups', $usergroups));
		}

		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	mixed				The value to handle
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 *
		 * @since	1.2.0
		 */
		public function virtualUsers($value)
		{
			if(!isset($this->registry->datastore->usergroups[$value]))
			{
				return(false);
			}

			$usergroups 			= $this->registry->datastore->usergroups;
			$usergroups[$value]['users'] 	= Helper::factory('database')->count('users', ['usergroupid' => $value]);

			return($this->registry->datastore->rebuild('usergroups', $usergroups));
		}
	}
?>