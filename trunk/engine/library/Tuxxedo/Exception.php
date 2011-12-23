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
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
		 * Standard exception, indicates that this is a GUI themed error (a non critical fatal)
		 *
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct()
		{
			if(!\func_num_args())
			{
				parent::__construct('Unknown error');
			}
			else
			{
				parent::__construct(\call_user_func_array('\sprintf', \func_get_args()));
			}
		}
	}
?>