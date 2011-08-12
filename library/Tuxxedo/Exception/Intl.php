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
	 * Exception namespace, this contains all the core exceptions defined within 
	 * the library.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Exception;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;

    
	/**
	 * Basic exception type, this is used for errors that 
	 * should act as fatal errors. If an exception of this 
	 * is caught by the default exception handler it will 
	 * terminate the execution.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Intl extends \Tuxxedo\Exception
	{
		/**
		 * Constructs a new internalizationized exception, meaning that the 
		 * contents of this exception may be formatted for internationalized 
		 * usage.
		 *
		 * If the internationalization object is not instanciated, then the 
	 	 * message is used as a raw translation, if the translation parameter 
		 * is specified then its formatted using a sprintf-alike syntax, 
		 * example:
		 *
		 * <code>
		 * try
		 * {
		 * 	throw new Exception\Intl('You are not old enough to view this content, you must be %d years old', 'age_limit_x', 18);
		 * }
		 * catch(Exception\Intl $e)
		 * {
		 * 	echo $e->getMessage();
		 * }
		 * </code>
		 *
		 * If the translation method is specified and the component is not loaded, then 
		 * formatting will be applied internally.
		 *
		 * Now if the internationalization component is loaded, then the above 
		 * would have outputted the same, however internally it would lookup the 
		 * phrase name 'age_limit_x' within the internationalization object, and 
		 * find its translation phrase and format it like regular phrases are 
		 * formatted with the {1}, {2}, {N} syntax.
		 *
		 * @param	string			The untranslated message, in case of the internationalization library was not loaded
		 * @param	string			The translation phrase
		 * @param	mixed			Optionally translation phrase replacement or parameter 1, 2, N
		 */
		public function __construct($message, $translation = NULL)
		{
			$registry = Registry::init();

			if(!$registry->intl)
			{
				if(\func_num_args() > 2)
				{
					$args = \func_get_args();

					unset($args[0]);

					parent::__construct(\call_user_func_array('\sprintf', $args));
				}
				else
				{
					parent::__construct($message);
				}
			}
			else
			{
				if(!$registry->intl->find($message))
				{
					if($translation !== NULL)
					{
						throw new Basic('Unable to find phrase (%s) for internationalized exception, no translation defined', $message);
					}

					throw new Exception($translation);
				}

				$args = \func_get_args();

				unset($args[1]);

				parent::__construct(\call_user_func_array(Array($registry->intl, 'format'), $args));
			}
		}
	}
?>