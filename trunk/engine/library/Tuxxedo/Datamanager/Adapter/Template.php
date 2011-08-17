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
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Template extends Adapter implements Hooks\Cache
	{
		/**
		 * Fields for validation of styles
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'id'			=> Array(
												'type'		=> self::FIELD_PROTECTED
												), 
							'title'			=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING
												), 
							'source'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING_EMPTY
												), 
							'compiledsource' 	=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING_EMPTY
												), 
							'defaultsource'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING_EMPTY
												), 
							'styleid'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_NUMERIC
												), 
							'changed'		=> Array(
												'type'		=> self::FIELD_OPTIONAL, 
												'validation'	=> self::VALIDATE_BOOLEAN, 
												'default'	=> false
												), 
							'revision'		=> Array(
												'type'		=> self::FIELD_OPTIONAL, 
												'validation'	=> self::VALIDATE_NUMERIC, 
												'default'	=> 0
												)
							);


		/**
		 * Constructor, fetches a new template based on its id if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The template id
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the template id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'template';
			$this->tablename	= \TUXXEDO_PREFIX . 'templates';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$template = $registry->db->query('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'templates` 
									WHERE 
										`id` = %d
									LIMIT 1', $identifier);

				if(!$template || !$template->getNumRows())
				{
					throw new Exception('Invalid template id passed to datamanager');
				}

				$this->data 		= $template->fetchAssoc();
				$this->identifier 	= $identifier;

				$template->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Syncronizes the templateids in the style manager
		 *
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Array $virtual)
		{
			if($this->context == self::CONTEXT_DELETE)
			{
				$styleinfo	= $this->registry->cache->styleinfo;
				$ids 		= \explode(',', $styleinfo[$this['styleid']]['templateids']);

				foreach($ids as $index => $id)
				{
					if($id == $this['id'])
					{
						unset($ids[$index]);

						break;
					}
				}

				$styleinfo[$this['styleid']]['templateids'] = \trim(\implode(',', $ids), ',');

				return($this->registry->cache->rebuild('styleinfo', $styleinfo, false));
			}
			elseif($this->context == self::CONTEXT_SAVE)
			{
				if(!$virtual || !isset($virtual['styleid']) || $virtual['styleid'] == $this['styleid'])
				{
					return(true);
				}

				$styleinfo	= $this->registry->cache->styleinfo;
				$ids 		= \explode(',', $styleinfo[$this['styleid']]['templateids']);

				foreach($ids as $index => $id)
				{
					if($id == $this['id'])
					{
						unset($ids[$index]);

						break;
					}
				}

				$styleinfo[$this['styleid']]['templateids'] = \trim(\implode(',', $ids), ',');

				if(empty($styleinfo[$virtual['styleid']]['templateids']))
				{
					$styleinfo[$virtual['styleid']]['templateids'] = $this->data['id'];
				}
				else
				{
					$styleinfo[$virtual['styleid']]['templateids'] .= ',' . $this->data['id'];
				}

				return($this->registry->cache->rebuild('styleinfo', $styleinfo, false));
			}

			return(true);
		}
	}
?>