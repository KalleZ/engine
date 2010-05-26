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
	 * Datamanager for usergroups
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	final class Tuxxedo_Datamanager_API_Usergroup extends Tuxxedo_Datamanager
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
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_NUMERIC
											)
							);

		/**
		 * Field names for if a required field 
		 * fails validation
		 *
		 * @var		array
		 */
		protected $field_names		= Array(
							'title'		=> 'Usergroup title', 
							'type'		=> 'Usergroup type', 
							'permissions'	=> 'Permission mask'
							);


		/**
		 * Constructor, fetches a new usergroup based on its id if set
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	integer			The usergroup id
		 * @param	boolean			Load from datastore?
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if a database call fails
		 */
		public function __construct(Tuxxedo $tuxxedo, $identifier = NULL, $cached = false)
		{
			$this->tuxxedo 		= $tuxxedo;

			$this->tablename	= TUXXEDO_PREFIX . 'usergroups';
			$this->idname		= 'id';

			if(!is_null($identifier))
			{
				if($cached)
				{
					$usergroups = $tuxxedo->cache->fetch('usergroups');

					if(!$usergroups || !isset($usergroups[$identifier]))
					{
						throw new Tuxxedo_Exception('Invalid usergroup id passed to datamanager');
					}

					$this->data = $usergroups[$identifier];
				}
				else
				{
					$usergroups = $tuxxedo->db->query('
										SELECT 
											* 
										FROM 
											`' . TUXXEDO_PREFIX . 'usergroups` 
										WHERE 
											`id` = %d', $identifier);

					if(!$usergroups)
					{
						throw new Tuxxedo_Exception('Invalid usergroup id passed to datamanager');
					}

					$this->data = $usergroups->fetchAssoc();
				}

				$this->identifier = $identifier;
			}
		}

		/**
		 * Save the usergroup in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	array			A virtually populated array from the datamanager abstraction
		 * @return	boolean			Returns true if the datastore was updated with success, otherwise false
		 */
		protected function rebuild(Array $virtual)
		{
			$datastore 				= $this->tuxxedo->cache->fetch('usergroups');
			$datastore[(integer) $this->identifier] = $virtual;

			return($this->tuxxedo->cache->rebuild('usergroups', $datastore));
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