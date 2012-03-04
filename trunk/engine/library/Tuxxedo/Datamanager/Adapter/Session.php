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
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for sessions
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Session extends Adapter
	{
		/**
		 * Fields for validation of session
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'sessionid'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'userid'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_NUMERIC
											), 
							'location'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_STRING_EMPTY, 
											'default'	=> \TUXXEDO_SELF
											), 
							'useragent' 	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_STRING_EMPTY, 
											'default'	=> \TUXXEDO_USERAGENT
											), 
							'lastactivity'	=> Array(
											'type'		=> self::FIELD_PROTECTED, 
											'validation'	=> self::VALIDATE_NUMERIC, 
											'default'	=> \TIMENOW_UTC
											)
							);


		/**
		 * Constructor for the sessions datamanager
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				Session identifier
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'session';
			$this->tablename	= \TUXXEDO_PREFIX . 'sessions';
			$this->idname		= 'sessionid';

			if($identifier)
			{
				$this->identifier = $this->fields['sessionid']['default'] = $identifier;
				$this->reidentify = true;
			}

			parent::init($registry, $options, $parent);
		}
	}
?>