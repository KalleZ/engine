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
	 * Internationalization namespace, this contains components for 
	 * internationalization manipulation, like phrases and phrasegroups.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Intl;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Intl;


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
		 * @param	\Tuxxedo\Intl			Reference to the internationalization object to use for this phrasegroup
		 * @param	string				Name of the phrasegroup to instanciate
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the phrasegroup isnt cached in the internationalization object
		 */
		public function __construct(Intl $intl, $phrasegroup)
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
?>