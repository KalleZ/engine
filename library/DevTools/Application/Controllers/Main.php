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
	 * @subpackage		Documentor
	 *
	 * =============================================================================
	 */


	/**
	 * DevTools application controllers namespace, this contains all the 
	 * controllers for the application.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	namespace DevTools\Application\Controllers;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\MVC\Controller;
	use Tuxxedo\MVC\View;
	use Tuxxedo\MVC\View\Layout;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Main application entry point controller
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	class Main extends Controller
	{
		/**
		 * Main application entry point action
		 */
		public function ActionMain()
		{
			echo new Layout('index');
		}
	}
?>