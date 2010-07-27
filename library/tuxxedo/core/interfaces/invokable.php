<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 * @subpackage		Core
	 *
	 * =============================================================================
	 */


	/**
	 * Core engine namespace, standard exceptions are integrated within this 
	 * part of the namespace, functions that previously were procedural is 
	 * defined as static classes.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	namespace \Tuxxedo\Core\Interfaces;


	/**
	 * Interface for requring the registry to pass certain information 
	 * before the constructor is called.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface Invokable
	{
		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Core\Registry			The registry object reference
		 * @param	array					The configuration array
		 * @param	array					The options array
		 * @return	object					Object instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	Only thrown on poorly a configured database section in the configuration file
		 */
		public static function invoke(\Tuxxedo\Core\Registry $registry, Array $configuration);
	}
?>