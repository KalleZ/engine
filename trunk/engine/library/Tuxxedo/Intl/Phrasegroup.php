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
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
	use Tuxxedo\Intl;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Internationalization phrasegroup class
	 *
	 * Contains basic routines for working with single phrasegroups.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Phrasegroup extends Design\InfoAccess
	{
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

			$this->information = $phrases;
		}

		/**
		 * Gets all loaded phrases from this phrasegroup
		 * 
		 * @return	array			Returns all loaded phrases for this phrasegroup
		 */
		public function getPhrases()
		{
			return($this->information);
		}
	}
?>