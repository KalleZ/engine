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
	use Tuxxedo\Template\Compiler;

	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Template extends Adapter implements Hooks\Cache, Hooks\Resetable
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
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isValidTemplateTitle')
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
		 * Checks whether the template title is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The title to check
		 * @return	boolean				Returns true if the title is valid
		 */
		public static function isValidTemplateTitle(Adapter $dm, Registry $registry, $title)
		{
			static $cached;

			if($dm->identifier === NULL)
			{
				return(!empty($title));
			}

			if(!$cached)
			{
				$titles = $registry->db->query('
								SELECT 
									`title`, 
									`styleid`
								FROM 
									`' . \TUXXEDO_PREFIX . 'templates`');

				if(!$titles || !$titles->getNumRows())
				{
					return(false);
				}

				foreach($titles as $row)
				{
					if(!isset($cached[$row['styleid']]))
					{
						$cached[$row['styleid']] = Array();
					}

					$cached[$row['styleid']][] = \strtolower($row['title']);
				}
			}

			if(!isset($cached[$dm['styleid']]))
			{
				return(false);
			}

			return(!isset($cached[$dm['styleid']][\strtolower($title)]));
		}

		/**
		 * Syncronizes the templateids in the style manager
		 *
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild()
		{
			$styleinfo 	= $this->registry->datastore->styleinfo;
			$template 	= \TUXXEDO_DIR . '/styles/' . $styleinfo[$this['styleid']]['styledir'] . '/templates/' . $this['title'] . '.tuxx';

			if($this->context == self::CONTEXT_DELETE)
			{
				if(\is_file($template) && !@\unlink($template))
				{
					return(false);
				}

				$ids = \explode(',', $styleinfo[$this['styleid']]['templateids']);

				foreach($ids as $index => $id)
				{
					if($id == $this['id'])
					{
						unset($ids[$index]);

						break;
					}
				}

				$styleinfo[$this['styleid']]['templateids'] = \trim(\implode(',', $ids), ',');

				return($this->registry->datastore->rebuild('styleinfo', $styleinfo));
			}
			elseif($this->context == self::CONTEXT_SAVE)
			{
				if(!@\file_put_contents($template, $this['source']))
				{
					return(false);
				}

				if(empty($styleinfo[$this['styleid']]['templateids']))
				{
					$styleinfo[$this['styleid']]['templateids'] = $this->data['id'];
				}
				else
				{
					$ids = \explode(',', $styleinfo[$this->data['styleid']]['templateids']);

					if(!\in_array($this->data['id'], $ids))
					{
						$ids[] = $this->data['id'];
					}

					$styleinfo[$this->data['styleid']]['templateids'] = \implode(',', \array_unique($ids));
				}

				return($this->registry->datastore->rebuild('styleinfo', $styleinfo));
			}

			return(true);
		}


		/**
		 * Resets the data to its default values while keeping the 
		 * identifier intact
		 *
		 * @return	boolean				Returns true on successful reset, otherwise false
		 */
		public function reset()
		{
			if(($this->options & self::OPT_LOAD_ONLY) || $this->identifier === NULL)
			{
				return(false);
			}

			static $compiler;

			if(!$compiler)
			{
				$compiler = new Compiler;
			}

			try
			{
				$compiler->set($this['defaultsource']);
				$compiler->compile();

				$style 	= Adapter::factory('style', $this['styleid'], self::OPT_LOAD_ONLY);

				if(!@\file_put_contents(\TUXXEDO_DIR . '/styles/' . $style['styledir'] . '/templates/' . $this['title'] . '.tuxx', $compiler->get()))
				{
					throw new Exception\Basic('Failed to open a file pointer to the template file');
				}
			}
			catch(Exception $e)
			{
				return(false);
			}

			$this['source'] 	= $this['defaultsource'];
			$this['compiledsource']	= $compiler->get();
			$this['changed']	= false;

			return($this->save());
		}
	}
?>