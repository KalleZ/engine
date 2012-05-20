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
	 * @subpckage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Bootstrap;
	use Tuxxedo\Registry;


	/**
	 * Sets the path to where the root script is, if the 
	 * constant CWD is defined before including this file, 
	 * then it will be used as root dir
	 *
	 * @var		string
	 */
	define('TUXXEDO_DIR', 	getcwd());

	/**
	 * Sets the library path
	 *
	 * @var		string
	 */
	define('TUXXEDO_LIBRARY', TUXXEDO_DIR . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library');


	/**
	 * Load the bootstraper
	 */
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Bootstrap.php');
	require(TUXXEDO_LIBRARY . '/DevTools/Bootstrap.php');


	/**
	 * Preloadables
	 */
	Bootstrap::init(Bootstrap::MODE_MINIMAL);

	$default_precache_datastore 	= Array('options', 'optioncategories', 'styleinfo', 'usergroups', 'languages', 'phrasegroups', 'permissions');
	$default_precache_templates 	= Array('header', 'footer', 'error', 'multierror', 'multierror_itembit', 'redirect');
	$default_precache_phrasegroups	= Array('global', 'devtools');

	$preloadables			= $router->getPreloadables();

	$precache			= $preloadables['datastore'];
	$views				= $preloadables['views'];
	$actionviews			= $preloadables['actionviews'];
	$phrasegroups			= $preloadables['phrasegroups'];

	if(isset($_GET['do']) && isset($actionviews[(string) $_GET['do']]))
	{
		$default_precache_templates = array_merge($default_precache_templates, (array) $actionviews[(string) $_GET['do']]);
	}

	Bootstrap::setPreloadables('datastore', (!$precache ? $default_precache_datastore : array_merge($default_precache_datastore, (array) $precache)));
	Bootstrap::setPreloadables('templates', (!$views ? $default_precache_templates : array_merge($default_precache_templates, (array) $views)));
	Bootstrap::setPreloadables('phrasegroups', (!$phrasegroups ? $default_precache_phrasegroups : array_merge($default_precache_phrasegroups, (array) $phrasegroups)));

	/**
	 * Bootstrap
	 */
	Bootstrap::init();

	/**
	 * Dispatch
	 */
	$router()->dispatch();
?>