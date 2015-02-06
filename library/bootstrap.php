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
	 * @subpckage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Bootstrap;


	/**
	 * Sets the path to where the root script is, if the 
	 * constant CWD is defined before including this file, 
	 * then it will be used as root dir
	 *
	 * @var		string
	 */
	define('TUXXEDO_DIR', 	(defined('CWD') ? CWD : getcwd()));

	/**
	 * Sets the library path
	 *
	 * @var		string
	 */
	define('TUXXEDO_LIBRARY', TUXXEDO_DIR . DIRECTORY_SEPARATOR . 'library');


	/**
	 * Load the bootstraper
	 */
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Bootstrap.php');


	/**
	 * Preloadables
	 */
	$default_precache_datastore 	= ['options', 'optioncategories', 'styleinfo', 'usergroups', 'languages', 'phrasegroups', 'permissions'];
	$default_precache_templates 	= ['header', 'footer', 'error', 'error_listbit'];
	$default_precache_phrasegroups	= ['global'];

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_precache_templates = array_merge($default_precache_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	Bootstrap::setPreloadables('datastore', (!isset($precache) ? $default_precache_datastore : array_unique(array_merge($default_precache_datastore, (array) $precache))));
	Bootstrap::setPreloadables('templates', (!isset($templates) ? $default_precache_templates : array_unique(array_merge($default_precache_templates, (array) $templates))));
	Bootstrap::setPreloadables('phrasegroups', (!isset($phrasegroups) ? $default_precache_phrasegroups : array_unique(array_merge($default_precache_phrasegroups, (array) $phrasegroups))));


	/**
	 * Bootstrap
	 */
	Bootstrap::init();
?>