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
	 * Datamanager for styles
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Style extends Adapter implements Hooks\Cache, Hooks\VirtualDispatcher
	{
		/**
		 * Fields for validation of styles
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'id'		=> Array(
											'type'		=> self::FIELD_PROTECTED
											), 
							'name'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'developer'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'styledir' 	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'default'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_BOOLEAN, 
											'default'	=> 0
											), 
							'inherit'	=> Array(
											'type'		=> self::FIELD_VIRTUAL
											)
							);


		/**
		 * Constructor, fetches a new style based on its id if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The style id
		 * @param	integer				Additional options to apply on the datamanager
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the style id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT)
		{
			$this->dmname		= 'style';
			$this->tablename	= \TUXXEDO_PREFIX . 'styles';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$style = $registry->db->query('
								SELECT 
									* 
								FROM 
									`' . \TUXXEDO_PREFIX . 'styles` 
								WHERE 
									`id` = %d
								LIMIT 1', $identifier);

				if(!$style || !$style->getNumRows())
				{
					throw new Exception\Exception('Invalid style id passed to datamanager');
				}

				$this->data 		= $style->fetchAssoc();
				$this->identifier 	= $identifier;

				$style->free();
			}

			parent::init($registry, $options);
		}

		/**
		 * Save the style in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Array $virtual)
		{
			if(($datastore = $this->registry->cache->styleinfo) === false)
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

			return($this->registry->cache->rebuild('styleinfo', $datastore));
		}

		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	mixed				The value to handle
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function virtualInherit($value)
		{
			if(!isset($this->registry->cache->styleinfo[$value]))
			{
				return(false);
			}

			foreach(explode(',', $this->data['templateids']) as $id)
			{
				$template 		= Adapter::factory('template', $template['id'], self::OPT_DEFAULT | self::OPT_LOAD_ONLY);
				$template['id']		= NULL;
				$template['styleid'] 	= $this->data['id'];
				$template['changed']	= 0;
				$template['revision']	= 1;

				if(!$template->save())
				{
					return(false);
				}
			}

			return(false);
		}
	}
?>