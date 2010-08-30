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

    namespace Tuxxedo;

	/**
	 * Default exception, mainly used for general errors. All 
	 * Tuxxedo specific exceptions extend this exception.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Exception extends \Exception
	{
		/**
		 * Indicates whenever this is a fatal error or not
		 *
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct()
		{
			$args = \func_get_args();

			if(!\sizeof($args))
			{
				$args[0] = 'Unknown error';
			}

			parent::__construct(\call_user_func_array('\sprintf', $args));
		}
	}
?>