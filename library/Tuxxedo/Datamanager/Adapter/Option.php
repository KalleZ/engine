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
	use Tuxxedo\Exception;
	use Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Datamanager\Hooks;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for options
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Option extends Adapter implements Hooks\Cache
	{
		/**
		 * Fields for validation of options
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
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the usergroup id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'option';
			$this->tablename	= \TUXXEDO_PREFIX . 'options';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$option = $registry->db->equery('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'options` 
									WHERE 
										`option` = \'%s\'', $identifier);

				if(!$option || !$option->getNumRows())
				{
					throw new Exception\Basic('Invalid option name passed to datamanager');
				}

				$this->data 		= $option->fetchAssoc();
				$this->identifier 	= $identifier;

				$option->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Checks whether the default value is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The value to check
		 * @return	boolean				Returns true if the default value is valid
		 */
		public static function isValidDefaultValue(Adapter $dm, Registry $registry, $defaultvalue)
		{
			throw new Exception\Core('Missing method implementation: %s', __METHOD__);
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
			throw new Exception\Core('Missing method implementation: %s', __METHOD__);
		}

		/**
		 * Save the option in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Array $virtual)
		{
			throw new Exception\Core('Missing method implementation: %s', __METHOD__);
		}
	}
?>