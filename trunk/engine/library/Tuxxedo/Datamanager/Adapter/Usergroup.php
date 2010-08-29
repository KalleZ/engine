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
	 *
	 * =============================================================================
	 */
	 
	namespace Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Registry;
	use Tuxxedo\Exception;
	use Tuxxedo\Datamanager;
	
	/**
	 * Datamanager for usergroups
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	class Usergroup extends \Tuxxedo\Datamanager\Adapter implements APICache
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
							'type'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_CALLBACK, 
											'callback'	=> Array(__CLASS__, 'isValidType')
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
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	integer			The usergroup id
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL)
		{
			$this->registry 		= $registry;

			$this->dmname		= 'usergroup';
			$this->tablename	= TUXXEDO_PREFIX . 'usergroups';
			$this->idname		= 'id';
			$this->information	= &$this->userdata;

			if($identifier !== NULL)
			{
				$usergroups = $registry->db->query('
									SELECT 
										* 
									FROM 
										`' . TUXXEDO_PREFIX . 'usergroups` 
									WHERE 
										`id` = %d', $identifier);

				if(!$usergroups)
				{
					throw new Exception\Basic('Invalid usergroup id passed to datamanager');
				}

				$this->data 		= $usergroups->fetchAssoc();
				$this->identifier 	= $identifier;
			}
		}

		/**
		 * Save the usergroup in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			A virtually populated array from the datamanager abstraction
		 * @return	boolean			Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Tuxxedo $tuxxedo, Array $virtual)
		{
			if(($datastore = $tuxxedo->cache->usergroups) === false)
			{
				$datastore = Array();
			}
			
			$datastore[(integer) $this->identifier] = $virtual;

			return($registry->cache->rebuild('usergroups', $datastore));
		}

		/**
		 * Checks whether a usergroup type is valid, this is 
		 * used as a callback for the validation filter, hence 
		 * its staticlly defined
		 *
		 * @param	Tuxxedo			Instance to the Tuxxedo registry
		 * @param	integer			The type to check
		 * @return	boolean			Returns true if the type is valid, otherwise false
		 */
		public static function isValidType(Tuxxedo $tuxxedo, $type)
		{
			return($type > 0 && $type < 4);
		}
	}
?>