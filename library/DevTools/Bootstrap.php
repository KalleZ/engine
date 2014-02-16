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
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools;


	/**
	 * Aliasing rules
	 */
	use DevTools\Style;
	use Tuxxedo\Input;
	use Tuxxedo\Registry;
	use Tuxxedo\Template;
	use Tuxxedo\Version;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Development Tools bootstraper, this integrates itself with the 
	 * global bootstraper by using its hooks and other custom overrides 
	 * thats needed specifically for the DevTools.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 * @since		1.2.0
	 */
	class Bootstrap extends \Tuxxedo\Bootstrap
	{
		/**
		 * Pre initializer
		 *
		 * @param	integer				The flags passed to the bootstraper initializer method
		 * @return	void				No value is returned
		 */
		public static function preInit($flags)
		{
			require(TUXXEDO_LIBRARY . '/DevTools/functions.php');
			require(TUXXEDO_LIBRARY . '/DevTools/functions_widget.php');

			parent::setHook(parent::FLAG_CORE, function(Registry $registry, Array $preloadables = NULL, &$configuration = NULL)
			{
				if(!\defined('SCRIPT_NAME'))
				{
					\tuxxedo_doc_error('A script name must be defined prior to use');
				}

				\tuxxedo_handler('exception', '\devtools_exception_handler');

				$registry->set('style', new Style);

				if($preloadables)
				{
					$cache_buffer = Array();

					$registry->style->cache($preloadables, $cache_buffer) or \tuxxedo_multi_error('Unable to load templates', $cache_buffer);

					unset($cache_buffer);
				}
			}, 'templates');

			parent::setHook(parent::FLAG_DATABASE, function(Registry $registry, Array $preloadables = NULL, &$configuration = NULL)
			{
				$db_driver	= strtolower($configuration['database']['driver']);
				$db_subdriver	= strtolower($configuration['database']['subdriver']);

				if(($db_driver == 'sqlite' || ($db_driver == 'pdo' && $db_subdriver == 'sqlite')) && !empty($configuration['devtools']['database']))
				{
					$configuration['database']['database'] = $configuration['devtools']['database'];
				}
			});
		}

		/**
		 * Post initializer
		 *
		 * @param	\Tuxxedo\Registry		The Tuxxedo registry object
		 * @return	void				No value is returned
		 */
		public static function postInit(Registry $registry)
		{
			$registry->register('cookie', '\Tuxxedo\Cookie');
			$registry->register('session', '\DevTools\Session');
			$registry->set('input', new Input);

			$widget_hook	= false;

			if(($widget = $registry->style->getSidebarWidget($widget_hook)) !== false)
			{
				if(!$widget_hook)
				{
					eval('$widget = "' . $widget . '";');
				}
			}

			Template::globalSet('widget', $widget);
			Template::globalSet('engine_version', Version::FULL);

			$configuration = $registry->getConfiguration();

			if($configuration['devtools']['protective'])
			{
				\devtools_auth_handler();
			}
		}
	}
?>