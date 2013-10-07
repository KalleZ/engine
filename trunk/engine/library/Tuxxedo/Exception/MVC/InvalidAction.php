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
	 * MVC Exception namespace. This contains all the specialized exceptions for 
	 * MVC components.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Exception\MVC;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

    
	/**
	 * Invalid action method
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class InvalidAction extends Exception
	{
		/**
		 * Constructor, sets the standardized exception message
		 *
		 * @param	\Exception				The previous exception if any
		 *
		 * @changelog	1.1.0					This method now supports previous exceptions ($previous parameter)
		 */
		public function __construct(\Exception $previous = NULL)
		{
			$this->previous = $previous;

			parent::__construct('Invalid action');
		}
	}    
?>