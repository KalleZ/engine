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
	 * Datamanager for languages
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Language extends Adapter implements Hooks\Cache, Hooks\VirtualDispatcher
	{
		/**
		 * Fields for validation of languages
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'id'		=> Array(
											'type'		=> self::FIELD_PROTECTED
											), 
							'title'		=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'developer'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'isotitle' 	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_CALLBACK, 
											'callback'	=> Array(__CLASS__, 'isValidIsotitle')
											), 
							'isdefault'	=> Array(
											'type'		=> self::FIELD_OPTIONAL, 
											'validation'	=> self::VALIDATE_BOOLEAN, 
											'default'	=> false
											), 
							'charset'	=> Array(
											'type'		=> self::FIELD_REQUIRED, 
											'validation'	=> self::VALIDATE_STRING
											), 
							'inherit'	=> Array(
											'type'		=> self::FIELD_VIRTUAL
											)
							);


		/**
		 * Constructor, fetches a new language based on its id if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The language id
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the language id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
		{
			$this->dmname		= 'language';
			$this->tablename	= \TUXXEDO_PREFIX . 'languages';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$language = $registry->db->query('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'languages` 
									WHERE 
										`id` = %d
									LIMIT 1', $identifier);

				if(!$language || !$language->getNumRows())
				{
					throw new Exception('Invalid language id passed to datamanager');
				}

				$this->data 		= $language->fetchAssoc();
				$this->identifier 	= $identifier;

				$language->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Checks whether an ISO code is valid or not (syntax wise)
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The ISO code
		 * @return	boolean				True if the ISO code is valid, otherwise false
		 */
		public static function isValidISOTitle(Adapter $dm, Registry $registry, $iso = NULL)
		{
			$len = strlen($iso);

			if($len != 2 && $len != 5)
			{
				return(false);
			}

			foreach(str_split($iso) as $index => $char)
			{
				if(is_numeric($char) || ($len == 5 && $index == 2 && $char != '-'))
				{
					return(false);
				}
			}

			return(true);
		}

		/**
		 * Save the language in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild()
		{
			if(!$this->identifier && !$this['id'])
			{
				return(false);
			}

			if(($datastore = $this->registry->datastore->languages) === false)
			{
				$datastore = Array();
			}

			if($this->context == self::CONTEXT_DELETE)
			{
				unset($datastore[(integer) ($this['id'] ? $this['id'] : $this->identifier)]);

				foreach(Array('phrasegroup' => 'phrasegroups', 'phrase' => 'phrases') as $singular => $plural)
				{
					$query = $this->registry->db->query('
										SELECT 
											`id` 
										FROM 
											`' . \TUXXEDO_PREFIX . $plural . '`
										WHERE 
											`languageid` = %d', $this->data['id']);

					if(!$query || !$query->getNumRows())
					{
						return(false);
					}

					foreach($query as $row)
					{
						Adapter::factory($singular, $row['id'])->delete();
					}
				}
			}

			if(!$this->registry->datastore->rebuild('languages', $datastore))
			{
				return(false);
			}

			if(isset($this['isdefault']) && $this->registry->options->language_id != $this['id'])
			{
				$dm 			= Adapter::factory('language', $this->registry->options->language_id, 0, $this);
				$dm['isdefault']	= false;

				if(!$dm->save())
				{
					return(false);
				}

				$this->registry->options->language_id = $this['id'];

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
		 */
		public function virtualInherit($value)
		{
			if(!isset($this->registry->datastore->languages[$value]))
			{
				return(false);
			}

			$phrasegroups = $this->registry->db->query('
									SELECT 
										`id`, 
										`title`
									FROM 
										`' . \TUXXEDO_PREFIX . 'phrasegroups` 
									WHERE 
										`languageid` = %d', $value);

			if(!$phrasegroups || !$phrasegroups->getNumRows())
			{
				return(false);
			}

			foreach($phrasegroups as $pgroup)
			{
				$dm 			= Adapter::factory('phrasegroup', $pgroup['id'], self::OPT_LOAD_ONLY, $this);
				$dm['languageid'] 	= $this->data['id'];

				if(!$dm->save())
				{
					return(false);
				}

				$phrases = $this->registry->db->query('
									SELECT 
										`id` 
									FROM 
										`' . \TUXXEDO_PREFIX . 'phrases` 
									WHERE 
											`languageid` = %d 
										AND 
											`phrasegroup` = \'%s\'', $value, $this->registry->db->escape($pgroup['title']));

				if(!$phrases || !$phrases->getNumRows())
				{
					continue;
				}

				foreach($phrases as $phrase)
				{
					$dm 			= Adapter::factory('phrase', $phrase['id'], self::OPT_LOAD_ONLY, $this);
					$dm['languageid']	= $this->data['id'];

					if(!$dm->save())
					{
						return(false);
					}
				}
			}

			return(true);
		}
	}
?>