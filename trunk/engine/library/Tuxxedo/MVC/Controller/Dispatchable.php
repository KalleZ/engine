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
	 * Controller extensions namespace. In here all extensions are stored 
	 * for MV-Controllers.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	namespace Tuxxedo\MVC\Controller;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Interface for dispatchable controller hooks
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	interface Dispatchable
	{
		/**
		 * Dispatch hook constant - Pre dispatching
		 *
		 * @var		integer
		 */
		const DISPATCH_PRE		= 1;

		/**
		 * Dispatch hook constant - Post dispatching
		 *
		 * @var		integer
		 */
		const DISPATCH_POST		= 2;


		/**
		 * Controller dispatch hook, this hook is called for 
		 * both pre and post dispatching and uses its only 
		 * parameter to determine which state we currently are 
		 * in
		 *
		 * @param	integer				The current dispatching state
		 * @return	void				No value is returned
		 */
		public function dispatcher($mode);
	}
?>