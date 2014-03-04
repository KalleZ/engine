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
	 * @since		1.2.0
	 */
	class Language extends Adapter implements Hooks\Cache, Hooks\VirtualDispatcher
	{
		/**
		 * Fields for validation of languages
		 *
		 * @var		array
		 */
		protected $fields		= [
							'id'		=> [
										'type'		=> parent::FIELD_PROTECTED
										], 
							'title'		=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_STRING
										], 
							'developer'	=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_STRING
										], 
							'isotitle' 	=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_CALLBACK, 
										'callback'	=> [__CLASS__, 'isValidIsotitle']
										], 
							'isdefault'	=> [
										'type'		=> parent::FIELD_OPTIONAL, 
										'validation'	=> parent::VALIDATE_BOOLEAN, 
										'default'	=> false
										], 
							'charset'	=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_STRING
										], 
							'inherit'	=> [
										'type'		=> parent::FIELD_VIRTUAL
										]
							];


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
		public function __construct(Registry $registry, $identifier = NULL, $options = parent::OPT_DEFAULT, Adapter $parent = NULL)
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
			if(!$this->identifier && !$this->data['id'])
			{
				return(false);
			}

			if(($datastore = $this->registry->datastore->languages) === false)
			{
				$datastore = [];
			}

			if($this->context == parent::CONTEXT_SAVE)
			{
				$tmp = $this->data;

				unset($tmp['inherit']);

				$datastore[$this->data['id']] = $tmp;
			}
			elseif($this->context == parent::CONTEXT_DELETE)
			{
				unset($datastore[(integer) ($this->data['id'] ? $this->data['id'] : $this->identifier)]);

				foreach(['phrasegroup' => 'phrasegroups', 'phrase' => 'phrases'] as $singular => $plural)
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
						continue;
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

			if($this->data['isdefault'] && $this->registry->options->language_id != $this->data['id'])
			{
				$dm 			= Adapter::factory('language', $this->registry->options->language_id, 0, $this);
				$dm['isdefault']	= false;

				if(!$dm->save())
				{
					return(false);
				}

				$this->registry->options->language_id = $this->data['id'];

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
				$dm 			= Adapter::factory('phrasegroup', $pgroup['id'], parent::OPT_LOAD_ONLY, $this);
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
					$dm 			= Adapter::factory('phrase', $phrase['id'], parent::OPT_LOAD_ONLY, $this);
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