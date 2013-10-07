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
	\defined('\TUXXEDO_LIBRARY') or exit;


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
		 *
		 * @changelog	1.2.0			Renamed 'default' to 'isdefault'
		 * @changelog	1.1.0			Added the 'inherit' field
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
							'isdefault'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_BOOLEAN, 
											'default'	=> false
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
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the style id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
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
					throw new Exception('Invalid style id passed to datamanager');
				}

				$this->data 		= $style->fetchAssoc();
				$this->identifier 	= $identifier;

				$style->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Save the style in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild()
		{
			if(!$this->identifier && !$this->data['id'])
			{
				return(false);
			}

			if(($datastore = $this->registry->datastore->styleinfo) === false)
			{
				$datastore = Array();
			}

			if($this->context == self::CONTEXT_DELETE)
			{
				unset($datastore[(integer) ($this['id'] ? $this['id'] : $this->identifier)]);

				foreach(\explode(',', $this->registry->datastore->styleinfo[$this['id']]['templateids']) as $id)
				{
					if(empty($id))
					{
						continue;
					}

					Datamanager\Adapter::factory('template', $id, $this->options)->delete();
				}
			}
			elseif($this->context == self::CONTEXT_SAVE)
			{
				$virtual		= $this->data;
				$virtual['templateids']	= '';
				$templates		= $this->registry->db->query('
											SELECT 
												`id` 
											FROM 
												`' . \TUXXEDO_PREFIX . 'templates` 
											WHERE 
												`styleid` = %d
 											ORDER BY 
												`id` 
											ASC', $this->data['id']);

				if($templates && $templates->getNumRows())
				{
					$ids = Array();

					while($row = $templates->fetchRow())
					{
						$ids[] = $row[0];
					}

					$virtual['templateids'] = \implode(',', $ids);
				}

				$datastore[(integer) $this->data['id']] = $virtual;
			}

			if(!$this->registry->datastore->rebuild('styleinfo', $datastore))
			{
				return(false);
			}

			if($this->data['isdefault'] && $this->registry->options->style_id != $this->data['id'])
			{
				$dm 			= Adapter::factory('style', $this->registry->options->style_id, 0, $this);
				$dm['isdefault']	= false;

				if(!$dm->save())
				{
					return(false);
				}

				$this->registry->options->style_id = $this->data['id'];

				$this->registry->options->save();
			}

			return(true);
		}

		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	mixed				The value to handle
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 *
		 * @since	1.1.0
		 */
		public function virtualInherit($value)
		{
			if(!isset($this->registry->datastore->styleinfo[$value]))
			{
				return(false);
			}

			foreach(\explode(',', $this->registry->datastore->styleinfo[$value]['templateids']) as $id)
			{
				$template 		= Adapter::factory('template', $id, self::OPT_LOAD_ONLY, $this);
				$template['styleid'] 	= $this->data['id'];
				$template['changed']	= 0;
				$template['revision']	= 1;

				if(!$template->save())
				{
					return(false);
				}

				$ids[] = $template->get('id');
			}

			$datastore 				= $registry->datastore->styleinfo;
			$datastore[$value]['templateids'] 	= \implode(',', $ids);

			return($this->registry->datastore->rebuild('styleinfo', $datastore));
		}
	}
?>