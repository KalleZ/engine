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
	defined('TUXXEDO_LIBRARY') or exit;


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
							'type'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_CALLBACK, 
											'callback'	=> Array(__CLASS__, 'isValidType'), 
											'default'	=> 2
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
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL)
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
					throw new Exception\Exception('Invalid usergroup id passed to datamanager');
				}

				$this->data 		= $usergroup->fetchAssoc();
				$this->identifier 	= $identifier;

				$usergroup->free();
			}

			parent::init($registry);
		}

		/**
		 * Checks whether a usergroup type is valid, this is 
		 * used as a callback for the validation filter, hence 
		 * its staticlly defined
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The type to check
		 * @return	boolean				Returns true if the type is valid, otherwise false
		 */
		public static function isValidType(Adapter $dm, Registry $registry, $type)
		{
			return($type > 0 && $type < 3);
		}

		/**
		 * Save the usergroup in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Registry $registry, Array $virtual)
		{
			if(($datastore = $registry->cache->usergroups) === false)
			{
				$datastore = Array();
			}

			if(!$virtual)
			{
				unset($datastore[(integer) ($this->data[$this->idname] ? $this->data[$this->idname] : $this->identifier)]);
			}
			else
			{
				$datastore[(integer) $this->data[$this->idname]] = $virtual;
			}

			return($registry->cache->rebuild('usergroups', $datastore));
		}
	}
?>