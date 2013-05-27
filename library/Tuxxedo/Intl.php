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
	 *
	 * =============================================================================
	 */


	/**
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
	use Tuxxedo\Intl;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Internationalization Interface
	 *
	 * This class deals with basic routines for internationalization 
	 * support and its relative components.
	 *
	 * This component reserves the registry name 'phrase' for the 
	 * global phrase array.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Intl extends Design\InfoAccess implements Design\Invokable
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Holds the current loaded phrases
		 *
		 * @var		Array
		 */
		protected $phrases	= Array();


		/**
		 * Constructs a new internationalization object
		 *
		 * @param	array			The language data to use
		 */
		public function __construct(Array $languageinfo)
		{
			$this->registry		= Registry::init();
			$this->information 	= $languageinfo;

			$this->registry->set('phrase', Array());
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				The configuration array
		 * @return	object				Object instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if an invalid (or not cached) language id was used
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL)
		{
			static $iso_to_language;

			if(!$iso_to_language)
			{
				$iso_to_language = function() use($registry)
				{
					static $map;

					$isos = Intl::getISOCodes();

					if(!$isos || !$registry->datastore->languages)
					{
						return($registry->options['language_id']);
					}

					if(!$map)
					{
						$map = Array();

						foreach($registry->datastore->languages as $id => $lang)
						{
							$map[\strtolower($lang['isotitle'])] = $id;
						}
					}

					foreach($isos as $isotitle)
					{
						if(isset($map[$isotitle]))
						{
							return($map[$isotitle]);
						}
					}

					return($registry->options->language_id);
				};
			}

			$options	= $registry->options;
			$languagedata 	= $registry->datastore->languages;
			$languageid	= ($options && $options->language_autodetect ? $iso_to_language() : ($options ? $options->language_id : 0));
			$languageid	= ($options ? (isset($registry->userinfo->id) && $registry->userinfo->language_id !== NULL && $registry->userinfo->language_id != $languageid ? $registry->userinfo->language_id : $languageid) : $languageid);

			if($languageid && isset($languagedata[$languageid]))
			{
				return(new self($languagedata[$languageid]));
			}

			throw new Exception\Basic('Invalid language id');
		}

		/**
		 * Caches a phrase group, trying to cache an already loaded 
		 * phrase group will recache it
		 *
		 * @param	array				A list of phrase groups to load
		 * @param	array				An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean				Returns true on success otherwise false
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an exception if the query should fail
		 */
		public function cache(Array $phrasegroups, Array &$error_buffer = NULL)
		{
			if(!$phrasegroups || !($phrasegroups = \array_filter($phrasegroups, Array($this, 'doPhrasegroupFilter'))))
			{
				return(false);
			}

			$result = $this->registry->db->query('
								SELECT 
									`title`, 
									`translation`, 
									`phrasegroup`
								FROM 
									`' . \TUXXEDO_PREFIX . 'phrases` 
								WHERE 
										`languageid` = %d 
									AND 
										`phrasegroup` IN (
											\'%s\'
										);', 
								$this['id'], join('\', \'', \array_map(Array($this->registry->db, 'escape'), $phrasegroups)));

			if($result && !$result->getNumRows())
			{
				return(true);
			}
			elseif($result === false)
			{
				if($error_buffer !== NULL)
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

			$this->registry->set('phrase', $this->getPhrases());

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
				return(new Intl\Phrasegroup($this, $phrasegroup));
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
			if(!$this->phrases)
			{
				return(false);
			}

			return(\array_keys($this->phrases));
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

				$search = isset($this->phrases[$phrasegroup][$phrase]);

				if($search === false)
				{
					return(false);
				}

				return($this->phrases[$phrasegroup][$phrase]);
			}

			foreach($this->phrases as $phrases)
			{
				if(isset($phrases[$phrase]))
				{
					return($phrases[$phrase]);
				}
			}

			return(false);
		}

		/**
		 * Format a translation string
		 *
		 * @param	   string		  The phrase to perform replacements on
		 * @param	   scalar		  Replacement string #1
		 * @param	   scalar		  Replacement string #n
		 * @return	  string		  Returns the formatted translation string
		 */
		public function format()
		{
			$args 		= \func_get_args();
			$size 		= \func_num_args($args);

			$args[0] 	= (!isset($this->phrases[$args[0]]) ?: $this->phrases[$args[0]]);

			if(!$args[0] || !$size)
			{
				return('');
			}
			elseif($size == 1)
			{
				return($args[0]);
			}

			for($i = 0; $i < $size; ++$i)
			{
				if(\strpos($args[0], '{' . ($i + 1) . '}') !== false)
				{
					$args[0] = \str_replace('{' . ($i + 1) . '}', $args[$i + 1], $args[0]);
				}
			}

			return($args[0]);
		}

		/**
		 * Gets all phrases, note that phrases may be overridden by 
		 * another if there is more with the same name. To overcome this 
		 * limitation you must fetch the phrasegroup in which the phrase 
		 * belongs and fetch it from there
		 *
		 * @return	array			Returns an array containing all loaded phrases and empty array if no phrases are loaded
		 */
		public function getPhrases()
		{
			if(!$this->phrases)
			{
				return(Array());
			}

			$phrases = Array();

			foreach($this->phrases as $group_phrases)
			{
				if(!$group_phrases)
				{
					continue;
				}

				foreach($group_phrases as $name => $phrase)
				{
					$phrases[$name] = $phrase;
				}
			}

			return($phrases);
		}

		/**
		 * Gets the browser language codes in priority
		 *
		 * @return	array			Returns an array with the language codes in priority from the user's browser, each code may be either 2 or 5 bytes long or NULL in case the HTTP_ACCEPT_LANGUAGE variable was not set
		 */
		public static function getISOCodes()
		{
			static $codes;

			if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				return;
			}
 
			if($codes === NULL)
			{
				$codes = Array();

				foreach(\explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part)
				{
					$parts = \explode(',', $part);

					if(\strpos($parts[0], '=') === false)
					{
						$codes[] = \strtolower($parts[0]);
					}

					if(isset($parts[1]))
					{
						$codes[] = \strtolower($parts[1]);
					}
				}

				$codes = \array_unique($codes);
			}

			return($codes);
		}

		/**
		 * Unloads a phrasegroup from current memory
		 *
		 * @param	string|array			The name of the phrasegroup(s) to remove from the cache
		 * @return	boolean				Returns true on success and false on error
		 */
		public function unload($list)
		{
			if(!$list)
			{
				return(false);
			}

			if(\is_array($list))
			{
				foreach($list as $group)
				{
					if(isset($this->phrases[$group]))
					{
						unset($this->phrases[$group]);
					}
				}

				$this->registry->set('phrase', $this->getPhrases());

				return(true);
			}
			elseif(!isset($this->phrases[$list]))
			{
				return(false);
			}

			unset($this->phrases[$list]);

			$this->registry->set('phrase', $this->getPhrases());

			return(true);
		}

		/**
		 * Filter callback for checking if a phrasegroup have any 
		 * phrases
		 *
		 * @param	string			The phrasegroup to check
		 * @return	boolean			True if is one or more phrases in that phrasegroup, false if none
		 */
		private function doPhrasegroupFilter($phrasegroup)
		{
			return(isset($this->registry->datastore->phrasegroups[$phrasegroup]) && $this->registry->datastore->phrasegroups[$phrasegroup]['phrases']);
		}
	}
?>