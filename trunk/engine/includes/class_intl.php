<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Internationalization Interface
	 *
	 * This class deals with basic routines for internationalization 
	 * support and its relative components.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Internationalization extends Tuxxedo_InfoAccess
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * Holds the current loaded phrases
		 *
		 * @var		array
		 */
		protected $phrases	= Array();


		/**
		 * Constructs a new internationalization object
		 *
		 * @param	array			The language data to use
		 */
		public function __construct(Array $languageinfo)
		{
			$this->tuxxedo		= Tuxxedo::init();
			$this->information 	= $languageinfo;
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if an invalid (or not cached) language id was used
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
			$languagedata 	= $tuxxedo->cache->languages;
			$languageid	= ($options ? (!empty($tuxxedo->userinfo->id) && $tuxxedo->userinfo->language_id != $options['language_id'] ? $tuxxedo->userinfo->language_id : $options['language_id']) : 0);

			if($languageid && isset($languagedata[$languageid]))
			{
				return(new self($languagedata[$languageid]));
			}

			throw new Tuxxedo_Basic_Exception('Invalid language id, try rebuild the datastore or use the repair tools');
		}

		/**
		 * Caches a phrase group, trying to cache an already loaded 
		 * phrase group will recache it
		 *
		 * @param	array			A list of phrase groups to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $phrasegroups, Array &$error_buffer = NULL)
		{
			if(!sizeof($phrasegroups))
			{
				return(false);
			}

			$result = $this->tuxxedo->db->query('
								SELECT 
									`title`, 
									`translation`, 
									`phrasegroup`
								FROM 
									`' . TUXXEDO_PREFIX . 'phrases` 
								WHERE 
										`languageid` = %d 
									AND 
										`phrasegroup` IN (
											\'%s\'
										);', 
								$this['id'], join('\', \'', array_map(Array($this->tuxxedo->db, 'escape'), $phrasegroups)));

			if($result && !$result->getNumRows())
			{
				return(true);
			}
			elseif($result === false)
			{
				if(!is_null($error_buffer))
				{
					$error_buffer = $phrasegroups;
				}

				return(false);
			}

			while($row = $result->fetchAssoc())
			{
				if(!isset($this->phrases[$row['phrasegroup']]))
				{
					$this->phrases[$row['phrasegroup']] = Array();
				}

				$this->phrases[$row['phrasegroup']][$row['title']] = $row['translation'];
			}

			return(true);
		}

		/**
		 * Gets all phrases from a specific phrasegroup
		 *
		 * @param	string			The phrasegroup to get
		 * @param	boolean			Whether to return a new phrasegroup object or just an array
		 * @return	mixed			Depending on the value of second parameter, an object or array is returned. False is returned on faliure
		 */
		public function getPhrasegroup($phrasegroup, $object = true)
		{
			if(!isset($this->phrases[$phrasegroup]))
			{
				return(false);
			}

			if($object)
			{
				return(new Tuxxedo_Internationalization_Phrasegroup($this, $phrasegroup));
			}

			return($this->phrases[$phrasegroup]);
		}

		/**
		 * Gets all phrasegroups
		 *
		 * @return	array			Returns an array with all loaded phrasegroups, false is returned if no phrasegroups is loaded.
		 */
		public function getPhrasegroups()
		{
			if(!sizeof($this->phrases))
			{
				return(false);
			}

			return(array_keys($this->phrases));
		}

		/**
		 * Finds a phrase
		 *
		 * @param	string			The phrase to find
		 * @param	string			Optionally search in a specific phrasegroup, defaults to search in all
		 * @return	string			Returns a phrases translation, false is returned on failure
		 */
		public function find($phrase, $phrasegroup = NULL)
		{
			if($phrasegroup)
			{
				if(!isset($this->phrases[$phrasegroup]))
				{
					return(false);
				}

				$search = array_search($this->phrases[$phrasegroup], $phrase);

				if($search === false)
				{
					return(false);
				}

				return($this->phrases[$phrasegroup][$search]);
			}

			foreach($this->phrases as $phrasegroup => $phrases)
			{
				if(($search = array_search($phrases, $phrase)) !== false)
				{
					return($phrases[$search]);
				}
			}

			return(false);
		}

		/**
		 * Gets all phrases, note that phrases may be overridden by 
		 * another if there is more with the same name. To overcome this 
		 * limitation you must fetch the phrasegroup in which the phrase 
		 * belongs and fetch it from there
		 *
		 * @return	array			Returns an array containing all loaded phrases
		 */
		public function getPhrases()
		{
			$phrases = Array();

			if(sizeof($this->phrases))
			{
				foreach($this->phrases as $group => $group_phrases)
				{
					$phrases = array_merge($phrases, $group_phrases);
				}
			}

			return($phrases);
		}
	}

	/**
	 * Internationalization phrasegroup class
	 *
	 * Contains basic routines for working with single phrasegroups.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Internationalization_Phrasegroup
	{
		/**
		 * Holds the list of loaded phrases for 
		 * this phrasegroup
		 *
		 * @var		array
		 */
		protected $phrases	= Array();


		/**
		 * Constructs a new phrasegroup object
		 *
		 * @param	Tuxxedo_Internationalization	Reference to the internationalization object to use for this phrasegroup
		 * @param	string				Name of the phrasegroup to instanciate
		 *
		 * @throws	Tuxxedo_Basic_Exception		Throws a basic exception if the phrasegroup isnt cached in the internationalization object
		 */
		public function __construct(Tuxxedo_Internationalization $intl, $phrasegroup)
		{
			$phrases = $intl->getPhrasegroup($phrasegroup, false);

			if($phrases === false)
			{
				throw Tuxxedo_Basic_Exception('Unable to instanciate phrasegroup. Phrasegroup \'%s\' is not loaded into cache', $phrasegroup);
			}

			$this->phrases = $phrases;
		}

		/**
		 * Gets a specific phrase from this phrasegroup
		 *
		 * @param	string			Title of the phrase to get
		 * @return	string			Returns the phrase translation, and false on error
		 */
		public function getPhrase($title)
		{
			if(isset($this->phrases[$title]))
			{
				return($this->phrases[$title]);
			}

			return(false);
		}

		/**
		 * Gets all loaded phrases from this phrasegroup
		 * 
		 * @return	array			Returns all loaded phrases for this phrasegroup
		 */
		public function getPhrases()
		{
			return($this->phrases);
		}
	}
?>