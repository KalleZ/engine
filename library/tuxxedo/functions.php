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
	 * Autoloader, this attempts to resolve namespaced classes in the 
	 * manner of:
	 * \Tuxxedo\<component>\<subcomponent>\Class
	 *
	 * to:
	 * /library/tuxxedo/component/subcomponent/class.php
	 *
	 * All namespaces/classes/interfaces will be converted into lowercase 
	 * while attempting to resolve it.
	 *
	 * @param	string			The class/interface to load
	 * @return	void			No value is returned
	 */
	function tuxxedo_handler_autoload($name)
	{
		if(class_exists($name, false))
		{
			return;
		}

		if(strpos('\\', $name) !== false)
		{
			$path = str_replace('\\', '/', strtolower($name)) . '.php';
		}
		else
		{
			$path = '/' . strtolower($name) . '.php';
		}

		require(TUXXEDO_LIBRARY . '/' . $path);
	}
?>