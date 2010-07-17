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
	 * Datamanager for sessions
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	class Tuxxedo_Datamanager_Session extends Tuxxedo_Datamanager
	{
		/**
		 * Fields for validation of styles
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'sessionid'	=> Array(
											'type'		=> self::FIELD_PROTECTED
											), 
							'userid'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_NUMERIC
											), 
							'location'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_STRING, 
											'default'	=> TUXXEDO_SELF
											), 
							'useragent' 	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'lastactivity'	=> Array(
											'type'		=> self::FIELD_PROTECTED, 
											'validation'	=> self::VALIDATE_NUMERIC, 
											'default'	=> TIMENOW_UTC
											)
							);


		/**
		 * Constructor for the sessions datamanager
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	integer			Void identifier
		 */
		public function __construct(Tuxxedo $tuxxedo, $identifier = NULL)
		{
			$this->tuxxedo 		= $tuxxedo;

			$this->dmname		= 'session';
			$this->tablename	= TUXXEDO_PREFIX . 'sessions';
			$this->idname		= 'sessionid';
			$this->information	= &$this->userdata;
			$this->identifier	= $this->fields['sessionid']['default'] = $identifier;
		}
	}
?>