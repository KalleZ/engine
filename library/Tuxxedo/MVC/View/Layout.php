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
	 * View namespace, this contains routines to ease development 
	 * of MVC related applications.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	namespace Tuxxedo\MVC\View;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\MVC\View;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * View layout, this works as a wrapper to automatically enable 
	 * layout mode. Layout mode cannot be disabled, which is the only 
	 * difference from a regular view.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 * @since		1.2.0
	 */
	class Layout extends View
	{
		/**
		 * Constructor, constructs a new view layout
		 *
		 * @param	string				The name of the view to load as layout
		 * @param	array				Default variables to set
		 */
		public function __construct($name, Array $variables = NULL)
		{
			$this->registry		= Registry::init();
			$this->name 		= (string) $name;
			$this->information	= &$this->variables;
			$this->layout		= true;

			if($variables)
			{
				$this->variables = $variables;
			}
		}

		/**
		 * Wrapper method for changing layout mode, this 
		 * cannot be disabled, thrus no error is emitted
		 *
		 * @return	void				No value is returned
		 */
		public function setLayout($mode)
		{
		}
	}
?>