<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */
	
	namespace Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Exception;
	
		/**
	 * Datamanager for sessions
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	class Session extends \Tuxxedo\Datamanager\Adapter
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
		 * @param	integer			Session identifier
		 */
		public function __construct(Registry $registry, $identifier = NULL)
		{
			$this->registry 		= $registry;

			$this->dmname		= 'session';
			$this->tablename	= TUXXEDO_PREFIX . 'sessions';
			$this->idname		= 'sessionid';
			$this->information	= &$this->userdata;
			$this->identifier	= $this->fields['sessionid']['default'] = $identifier;
		}
	}
