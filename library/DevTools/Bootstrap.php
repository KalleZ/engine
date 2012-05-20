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
	use Tuxxedo\MVC\Router;
	use Tuxxedo\Registry;
	use Tuxxedo\Template;
	use Tuxxedo\Version;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

	
	/**
	 * DevTools bootstraper, this registers internal hooks related 
	 * specific to the DevTools application.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Bootstrap extends \Tuxxedo\Bootstrap
	{
		/**
		 * Pre initialization callback, the effect of this method only 
		 * have effect the first time it is called.
		 *
		 * This registers two hooks:
		 *
		 *  1) core
		 *  2) style (overriding default)
		 *
		 * @param	integer			The flags thts supposed to be initialized
		 * @return	void			No value is returned
		 */
		public static function preInit($flags)
		{
			static $called;

			if($called)
			{
				return;
			}

			$called = true;

			if(!isset(self::$hooks[self::FLAG_CORE]))
			{
				self::$hooks[self::FLAG_CORE] = Array();
			}

			if(!isset(self::$hooks[self::FLAG_STYLE]))
			{
				self::$hooks[self::FLAG_STYLE] = Array();
			}

			self::$hooks[self::FLAG_CORE][] 	= Array(
									'callback'	=> function(Registry $registry, Array $preloadables = NULL)
									{
										$registry->set('router', new Router('\DevTools\Application\Controllers'));
									}, 
									'preloadables'	=> NULL
									);

			self::$hooks[self::FLAG_STYLE][] 	= Array(
									'callback'	=> function(Registry $registry, Array $preloadables = NULL)
									{
										$registry->register('style', '\DevTools\Style');

										if($preloadables)
										{
											$buffer = Array();

											$registry->style->cache($preloadables, $buffer) or tuxxedo_multi_error('Unable to load template \'%s\'', $buffer);
										}

										return(true);
									}, 
									'preloadables'	=> 'templates'
									);
		}

		/**
		 * Post initialization callback, this is called after all the 
		 * initialization is done. This will only work if the router 
		 * and intl components is loaded and haven't been called with 
		 * those before.
		 *
		 * This registers 4 global template variables:
		 *
		 *   1) widget
		 *   2) phrase
		 *   3) script_name
		 *   4) engine_version
		 *
		 * @param	\Tuxxedo\Registry		The registry object reference
		 * @return	void				No value is returned
		 */
		public static function postInit(Registry $registry)
		{
			static $called;

			if(!$registry->router || !$registry->intl || $called)
			{
				return;
			}

			$called 	= true;
			$controller	= $registry->router->getController();

			Template::globalSet('widget', new Template('widget_' . $controller, true));
			Template::globalSet('phrase', $registry->phrase);
			Template::globalSet('script_name', $controller);
			Template::globalSet('engine_version', Version::FULL);
		}
	}
?>