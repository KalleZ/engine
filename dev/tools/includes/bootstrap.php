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
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Emulation layer of the bootstrap.php in the includes 
	 * root, this is to prevent corrupt datastores from 
	 * stopping the execution.
	 *
	 * We must not use any sessions here, as it would cause 
	 * the development tools to interfere with the main site.
	 */

	define('CWD', 		'../..');
	define('TUXXEDO', 	1337);

	require(CWD . '/library/configuration.php');
	require(CWD . '/library/Tuxxedo/Loader.php');
	require(CWD . '/library/Tuxxedo/functions.php');
	require(CWD . '/library/Tuxxedo/functions_debug.php');

	require('./includes/functions.php');

	if(!$configuration['application']['debug'])
	{
		$configuration['application']['debug'] = $debug_notice = true;
	}

	if(!defined('SCRIPT_NAME'))
	{
		throw new Tuxxedo_Basic_Exception('A script name must be defined prior to use');
	}

	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_shutdown_handler');
	spl_autoload_register('Tuxxedo\Loader::load');

	Tuxxedo\Registry::globals('error_reporting', 	true);
	Tuxxedo\Registry::globals('errors', 		Array());

	define('TUXXEDO_DEBUG', 	true);
	define('TUXXEDO_DIR', 		CWD);
	define('TUXXEDO_LIBRARY', 	CWD . '/library');
	define('TUXXEDO_PREFIX', 	$configuration['database']['prefix']);

	require('./includes/template.php');

	$registry = Tuxxedo\Registry::init($configuration);

	$registry->load(Array('db', 'cache', 'filter'));

	$registry->set('timezone', new DateTimeZone('UTC'));
	$registry->set('datetime', new DateTime('now', $timezone));
	$registry->set('style', new Tuxxedo_Dev_Style);

	define('TIMENOW', $datetime->getTimestamp());
	define('TIMENOW_UTC', TIMENOW);

	if(isset($precache) && sizeof($precache))
	{
		$cache_buffer = Array();

		$cache->cache($precache, $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);
	}

	$cache_buffer		= Array();
	$default_templates 	= Array('header', 'footer', 'error', 'redirect');

	if(isset($action_templates) && isset($_REQUEST['do']) && isset($action_templates[(string) $_REQUEST['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_REQUEST['do']]);
	}

	$style->cache((!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)), $cache_buffer) or tuxxedo_multi_error('Unable to load template \'%s\'', $cache_buffer);

	unset($cache_buffer);

	$registry->set('options', (object) Tuxxedo\Registry::getOptions());

	$engine_version = Tuxxedo\Registry::VERSION_STRING;

	if(($widget_panel = $style->getSidebarWidget()) !== false)
	{
		eval('$widget = "' . $widget_panel . '";');
	}

	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>