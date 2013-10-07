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
	 *
	 * @changelog		1.2.0			This class now implements the InfoAccess pattern
	 */
	class Phrasegroup extends Design\InfoAccess
	{
		/**
		 * Phrase group name
		 *
		 * @var		string
		 * @since	1.2.0
		 */
		protected $phrasegroup;


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

			$this->phrasegroup = $phrasegroup;
			$this->information = $phrases;
		}

		/**
		 * Gets the phrasegroup name for this object
		 *
		 * @return	string				Returns the phrasegroup name
		 *
		 * @since	1.2.0
		 */
		public function getName()
		{
			return($this->phrasegroup);
		}
	}
?>