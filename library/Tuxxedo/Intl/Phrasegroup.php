<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	namespace Tuxxedo\Intl;
	use Tuxxedo\Exception;

	/**
	 * Internationalization phrasegroup class
	 *
	 * Contains basic routines for working with single phrasegroups.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Phrasegroup
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
		public function __construct(\Tuxxedo\Intl $intl, $phrasegroup)
		{
			$phrases = $intl->getPhrasegroup($phrasegroup, false);

			if($phrases === false)
			{
				throw Exception\Basic('Unable to instanciate phrasegroup. Phrasegroup \'%s\' is not loaded into cache', $phrasegroup);
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
