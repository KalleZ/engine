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
	use Tuxxedo\Helper;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for phrase groups
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Phrasegroup extends Adapter implements Hooks\Cache, Hooks\VirtualDispatcher
	{
		/**
		 * Datamanager name
		 *
		 * @var		string
		 */
		const DM_NAME			= 'phrasegroup';

		/**
		 * Identifier name for the datamanager
		 *
		 * @var		string
		 */
		const ID_NAME			= 'id';

		/**
		 * Table name for the datamanager
		 *
		 * @var		string
		 */
		const TABLE_NAME		= 'phrasegroups';


		/**
		 * Fields for validation of phrase groups
		 *
		 * @var		array
		 */
		protected $fields		= [
							'id'		=> [
										'type'		=> parent::FIELD_PROTECTED
										], 
							'title'		=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_CALLBACK, 
										'callback'	=> [__CLASS__, 'isValidPhrasegroupTitle']
										], 
							'languageid'	=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_CALLBACK, 
										'callback'	=> [__CLASS__, 'isValidLanguageId']
										], 
							'phrases'	=> [
										'type'		=> parent::FIELD_VIRTUAL
										]
							];


		/**
		 * Constructor, fetches a new phrase group based on its title if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The phrase group title
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the phrase group title is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = parent::OPT_DEFAULT, Adapter $parent = NULL)
		{
			if($identifier !== NULL)
			{
				$phrasegroup = $registry->db->equery('
									SELECT 
										* 
									FROM 
										`' . \TUXXEDO_PREFIX . 'phrasegroups` 
									WHERE 
										`id` = %d', $identifier);

				if(!$phrasegroup || !$phrasegroup->getNumRows())
				{
					throw new Exception('Invalid phrase group id passed to datamanager');

					return;
				}

				$this->data 			= $phrasegroup->fetchAssoc();
				$this->data['languageid']	= (integer) $this->data['languageid'];
				$this->identifier 		= $identifier;

				$phrasegroup->free();
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Checks whether a language id is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The language id
		 * @return	boolean				True if the language exists, otherwise false
		 */
		public static function isValidLanguageId(Adapter $dm, Registry $registry, $languageid = NULL)
		{
			return($languageid === NULL || $registry->datastore->languages && isset($registry->datastore->languages[$languageid]));
		}

		/**
		 * Checks whether a phrasegroup title is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The phrasegroup title
		 * @return	boolean				True if the phrasegroup title is valid, otherwise false
		 */
		public static function isValidPhrasegroupTitle(Adapter $dm, Registry $registry, $title = NULL)
		{
			$query = $registry->db->query('
							SELECT 
								`id`
							FROM 
								`' . \TUXXEDO_PREFIX . 'phrasegroups` 
							WHERE 
									`languageid` = %d 
								AND 
									`title` = \'%s\' 
							LIMIT 1', $dm->data['languageid'], $registry->db->escape($title));

			return(!$query || !$query->getNumRows());
		}

		/**
		 * Save the phrase group in the datastore, this method is called from 
		 * the parent class in cases when the save method was success
		 *
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 *
		 * @todo	Needs a little more work for fetching the old name, probably something we need to add in the core datamanager adapter
		 */
		public function rebuild()
		{
			if(($datastore = $this->registry->datastore->phrasegroups) === false)
			{
				$datastore = [];
			}

			$id		= (isset($this->data['title']) ? $this->data['title'] : $this->identifier);
			$languageid	= $this->data['languageid'];
			$ptr 		= (isset($datastore[$languageid]) ? $datastore[$languageid] : NULL);

			if($this->context == parent::CONTEXT_DELETE && $ptr && isset($ptr[$id]))
			{
				unset($ptr[$id]);

				if(!$ptr)
				{
					unset($ptr);
				}
			}
			elseif($this->context == parent::CONTEXT_SAVE)
			{
				if($ptr && isset($ptr[$id]))
				{
					unset($ptr);
				}

				if(!isset($datastore[$languageid]))
				{
					$datastore[$languageid] = [];
					$ptr			= &$datastore[$languageid];
				}

				$query		= $this->registry->db->equery('
										SELECT 
											COUNT(`id`) as \'phrases\' 
										FROM 
											`' . \TUXXEDO_PREFIX . 'phrases` 
										WHERE 
											`phrasegroup` = \'%s\'', $id);

				$ptr[$id] 	= [
							'id'		=> $id, 
							'phrases'	=> ($query && $query->getNumRows() ? (integer) $query->fetchObject()->phrases : 0)
							];
			}
			else
			{
				return(true);
			}

			$groups = $this->registry->db->equery('
								SELECT 
									`id` 
								FROM 
									`' . \TUXXEDO_PREFIX . 'phrases` 
								WHERE 
										`phrasegroup` = \'%s\'
									AND 
										`languageid` = %d', (isset($this->original_data['title']) ? $this->original_data['title'] : $id), $languageid);

			if($groups && $groups->getNumRows())
			{
				foreach($groups as $row)
				{
					$dm = Adapter::factory('phrase', $row['id']);

					if($this->context == parent::CONTEXT_DELETE)
					{
						$dm->delete();
					}
					else
					{
						$dm['phrasegroup'] = $id;

						$dm->save();
					}
				}
			}

			return($this->registry->datastore->rebuild('phrasegroups', $datastore));
		}

		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	mixed				The value to handle
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function virtualPhrases($value)
		{
			if(!isset($this->registry->datastore->phrasegroups[$value]))
			{
				return(false);
			}

			$phrasegroups 				= $this->registry->datastore->phrasegroups;
			$phrasegroups[$value]['phrases'] 	= Helper::factory('database')->count('phrases', [
															'phrasegroup' 	=> $value, 
															'languageid'	=> $this->data['languageid']
															]);

			return($this->registry->datastore->rebuild('phrasegroups', $phrasegroups));
		}
	}
?>