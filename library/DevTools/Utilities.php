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
	 * Developmental Tools namespace. This namespace is for all development 
	 * tool related routines, as used by /dev/tools.
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Utilities class wrapper. This class wraps around the previously 
	 * declared prodecural functions, which now exists as static methods 
	 * for primarily autoloading reasoning.
	 *
	 * Unlike the \Tuxxedo\Utilities class, this one is specific for 
	 * the DevTools application and contains mainly UI related methods.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @since		1.2.0
	 */
	class Utilities
	{
		/**
		 * Redirect using a template
		 *
		 * If the timeout is set to 0, then a header redirect will be 
		 * issued instead, thrus no template rendered.
		 *
		 * This function terminates the script.
		 *
		 * @param	string			The message to show to the user while redirecting
		 * @param	string			The redirect location
		 * @param	integer			The timeout in seconds (before redirecting)
		 * @return	void			No value is returned
		 */
		public static function redirect($message, $location, $timeout = 3)
		{
			if(!$timeout)
			{
				header('Location: ' . $location);
				exit;
			}

			eval(\page('redirect'));
			exit;
		}

		/**
		 * Redirect directly using a header call
		 *
		 * This function terminates the script.
		 *
		 * @param	string			The redirect location
		 * @return	void			No value is returned
		 */
		public static function headerRedirect($location)
		{
			header('Location: ' . $location);
			exit;
		}
	}
?>