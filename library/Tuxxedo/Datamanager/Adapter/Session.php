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
		 *
		 * @changelog	1.2.0			Added the 'rehash' field
		 */
		protected $fields		= Array(
							'sessionid'	=> Array(
											'type'		=> parent::FIELD_REQUIRED, 
											'validation'	=> parent::VALIDATE_STRING
											), 
							'userid'	=> Array(
											'type'		=> parent::FIELD_OPTIONAL, 
											'validation'	=> parent::VALIDATE_NUMERIC
											), 
							'location'	=> Array(
											'type'		=> parent::FIELD_OPTIONAL, 
											'validation'	=> parent::VALIDATE_STRING_EMPTY
											), 
							'useragent' 	=> Array(
											'type'		=> parent::FIELD_OPTIONAL, 
											'validation'	=> parent::VALIDATE_STRING_EMPTY
											), 
							'lastactivity'	=> Array(
											'type'		=> parent::FIELD_PROTECTED, 
											'validation'	=> parent::VALIDATE_NUMERIC, 
											'default'	=> \TIMENOW_UTC
											), 
							'rehash'	=> Array(
											'type'		=> parent::FIELD_OPTIONAL, 
											'validation'	=> parent::VALIDATE_BOOLEAN, 
											'default'	=> 0
											)
							);


		/**
		 * Constructor for the sessions datamanager
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				Session identifier
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @changelog	1.2.0				User-Agent and Location is no longer manually set as the constants no longer exists but figured out on its own
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = parent::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname				= 'session';
			$this->tablename			= \TUXXEDO_PREFIX . 'sessions';
			$this->idname				= 'sessionid';

			$this->fields['useragent']['default']	= (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
			$this->fields['location']['default']	= $_SERVER['SCRIPT_NAME'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

			if($identifier)
			{
				$this->identifier 	= $this->fields['sessionid']['default'] = $identifier;
				$this->reidentify 	= true;
				$this->data 		= $this->getDataStruct();

				$session = $registry->db->equery('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'sessions` 
									WHERE 
										`sessionid` = \'%s\'', $identifier);

				if($session && $session->getNumRows())
				{
					$this->data = $session->fetchAssoc();

					$session->free();
				}
			}

			parent::init($registry, $options, $parent);
		}
	}
?>