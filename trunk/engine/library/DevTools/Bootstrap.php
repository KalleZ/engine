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
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Developmental Tools namespace. This namespace is for all development 
	 * tool related routines, as used by /dev/tools.
	 *
	 * @author              Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Input;
	use Tuxxedo\Registry;
	use Tuxxedo\Template;
	use Tuxxedo\Version;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

	
	/**
	 * Bootstrap hook, this class hooks into the Engine bootstraper to 
	 * define various globals used.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 *
	 * @wip
	 */
	class Bootstrap extends \Tuxxedo\Bootstrap
	{
		/**
		 * Pre-initializer hook
		 *
		 * @param	integer				Initializer flags
		 */
		public static function preInit($flags)
		{
		}

		/**
		 * Post-initializer hook
		 *
		 * @param	\Tuxxedo\Registry		Reference to the Tuxxedo Object Registry
		 * @return	void				No value is returned
		 */
		public static function postInit(Registry $registry)
		{
			$registry->register('cookie', '\Tuxxedo\Cookie');
			$registry->register('session', '\DevTools\Session');
			$registry->register('devuser', '\DevTools\User');

			$registry->set('devuserinfo', $registry->devuser->getUserinfo());
			$registry->set('input', new Input);
			$registry->set('style', new Style);

			if($registry->style)
			{
				Template::globalSet('engine_version', Version::FULL);
			}
		}
	}
?>