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
	 * Datamanager for option categories
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Optioncategory extends Adapter implements Hooks\Cache
	{
		/**
		 * Fields for validation of permissions
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'name'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_IDENTIFIER
											)
							);


		/**
		 * Constructor, fetches a new option category based on its name if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The option category name
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the option category name is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'optioncategory';
			$this->tablename	= \TUXXEDO_PREFIX . 'optioncategories';
			$this->idname		= 'name';

			if($identifier !== NULL)
			{
				$category = $registry->db->equery('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'optioncategories` 
									WHERE 
										`name` = \'%s\'', $identifier);

				if(!$category || !$category->getNumRows())
				{
					throw new Exception('Invalid option category name passed to datamanager');

					return;
				}

				$this->data 		= $category->fetchAssoc();
				$this->identifier 	= $identifier;

				$category->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Save the option category in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild()
		{
			if(($datastore = $this->registry->datastore->optioncategories) === false)
			{
				$datastore = Array();
			}

			$id 		= (isset($this->data['name']) ? $this['name'] : $this->identifier);
			$old_category	= '';
			$new_category	= '';

			if($this->context == self::CONTEXT_SAVE)
			{
				if(isset($this['name']))
				{
					foreach($datastore as $key => $value)
					{
						if($value == $id)
						{
							unset($datastore[$key]);

							break;
						}
					}

					$old_category = $id;
				}

				$datastore[] = $new_category = $this['name'];
			}
			elseif($this->context == self::CONTEXT_DELETE && \in_array($id, $datastore))
			{
				$new_category = '';
				$old_category = $id;

				foreach($datastore as $key => $value)
				{
					if($value == $id)
					{
						unset($datastore[$key]);

						break;
					}
				}
			}
			else
			{
				return(false);
			}

			if(!empty($old_category))
			{
				$options = $this->registry->db->equery('
									SELECT 
										`option` 
									FROM 
										`' . \TUXXEDO_PREFIX . 'options` 
									WHERE 
										`category` = \'%s\'', $old_category);

				if($options && $options->getNumRows())
				{
					foreach($options as $row)
					{
						$dm 		= Adapter::factory('option', $row['option']);
						$dm['category']	= $new_category;

						$dm->save();
					}
				}
			}

			return($this->registry->datastore->rebuild('optioncategories', $datastore));
		}
	}
?>