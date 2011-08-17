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
	use Tuxxedo\Registry;
	use Tuxxedo\Exception;
	use Tuxxedo\Datamanager\Adapter;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for options
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Style extends Adapter implements APICache
	{
		/**
		 * Fields for validation of styles
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'option'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'value'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'defaultvalue'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_CALLBACK, 
											'callback'	=> Array(__CLASS__, 'isValidDefaultValue')
											), 
							'type'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_CALLBACK, 
											'callback'	=> Array(__CLASS__, 'isValidType'), 
											'default'	=> 's'
											), 
							);


		/**
		 * Constructor, fetches a new option based on its name if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The option name
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the option name is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL)
		{
			$this->registry 	= $registry;

			$this->dmname		= 'option';
			$this->tablename	= \TUXXEDO_PREFIX . 'options';
			$this->idname		= 'id';
			$this->information	= &$this->userdata;

			if($identifier !== NULL)
			{
				$option = $registry->db->query('
								SELECT 
									* 
								FROM 
									`' . \TUXXEDO_PREFIX . 'options` 
								WHERE 
									`option` = %s
								LIMIT 1', $identifier);

				if(!$option || !$option->getNumRows())
				{
					throw new Exception\Basic('Invalid option name passed to datamanager');
				}

				$this->data 		= $option->fetchAssoc();
				$this->identifier 	= $identifier;

				$option->free();
			}
		}

		/**
		 * Save the option in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Registry $registry, Array $virtual)
		{
			if(($datastore = $this->registry->cache->options) === false)
			{
				$datastore = Array();
			}
			
			$datastore[(string) $this->identifier] = $virtual;

			return($this->registry->cache->rebuild('options', $datastore));
		}

		public static function isValidDefaultValue()
		{
			return(false);
		}

		/**
		 * Checks whether the option values fits the type definition
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The type to check
		 * @return	boolean				Returns true if the type is valid, otherwise false
		 */
		public static function isValidType(Adapter $dm, Registry $registry, $type)
		{
			if(isset($type{1}) || !in_array($type, Array('s', 'b', 'i')))
			{
				return(false);
			}

			if($dm['

			switch($type)
			{
				case('i'):
				{
					return((isset($dm['value']
			}
		}
	}
?>