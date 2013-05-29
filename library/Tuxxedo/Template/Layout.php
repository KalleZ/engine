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
	 * Template namespace. This contains special routines for template handling 
	 * and such. It is also the home of the template compiler.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Template;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;
	use Tuxxedo\Template;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Template layout, this is identical to the basic template class, except that 
	 * it will wrap around the 'header' and 'footer' templates to its content.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Layout extends Template
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
		 * Whether to set this as a layout or not
		 *
		 * @param	boolean				Set to true to activate layout mode, and false to not, disabled for 'Layout' templates
		 * @return	void				No value is returned
		 */
		public function setLayout($mode)
		{
		}
	}
?>