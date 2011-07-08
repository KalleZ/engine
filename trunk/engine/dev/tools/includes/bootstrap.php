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
	 * Aliasing rules
	 */
	use DevTools\Style;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\Version;


	/**
	 * Set the debug mode constant
	 *
	 * @var		boolean
	 */
	define('TUXXEDO_DEBUG', 	true);

	/**
	 * Sets the path to where the root script is, if the 
	 * constant CWD is defined before including this file, 
	 * then it will be used as root dir
	 *
	 * @var		string
	 */
	define('TUXXEDO_DIR', '../..');

	/**
	 * Sets the library path
	 *
	 * @var		string
	 */
	define('TUXXEDO_LIBRARY', '../../library');

	/**
	 * URL of the current page being executed, including its 
	 * query string, note that this constant is using the 
	 * raw data. It is up to the user of this constant to 
	 * proper filter it
	 *
	 * @var		string
	 */
	define('TUXXEDO_SELF', $_SERVER['SCRIPT_NAME'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));

	/**
	 * User agent string if any for the browsing user, note that 
	 * like the TUXXEDO_SELF constant, this have to be escaped if 
	 * used in database context
	 *
	 * @var		string
	 */
	define('TUXXEDO_USERAGENT', (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));


	require(TUXXEDO_LIBRARY . '/configuration.php');
	require(TUXXEDO_LIBRARY . '/DevTools/functions.php');
	require(TUXXEDO_LIBRARY . '/DevTools/functions_widget.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Loader.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions_debug.php');

	if(!$configuration['application']['debug'])
	{
		$configuration['application']['debug'] = $debug_notice = true;
	}

	/**
	 * Set database table prefix constant
	 *
	 * @var		string
	 */
	define('TUXXEDO_PREFIX', $configuration['database']['prefix']);

	/**
	 * SQLite uses relative paths
	 */
	if($configuration['database']['driver'] == 'sqlite' || ($configuration['database']['driver'] == 'pdo' && $configuration['database']['subdriver'] == 'sqlite'))
	{
		$configuration['database']['database'] = '../sql/bin/tuxxedo.sqlite3';
	}

	date_default_timezone_set('UTC');

	set_error_handler('tuxxedo_error_handler');
	register_shutdown_function('tuxxedo_shutdown_handler');
	spl_autoload_register('\Tuxxedo\Loader::load');

	set_exception_handler('devtools_exception_handler');

	Registry::globals('error_reporting', 	true);
	Registry::globals('errors', 		Array());

	$registry = Registry::init($configuration);

	$registry->set('timezone', new DateTimeZone('UTC'));
	$registry->set('datetime', new DateTime('now', $timezone));

	/**
	 * Current time constant
	 *
	 * @var		integer
	 */
	define('TIMENOW', $datetime->getTimestamp());

	/**
	 * Set the UTC time constant
	 *
	 * @var		integer
	 */
	define('TIMENOW_UTC', TIMENOW);

	if(!defined('SCRIPT_NAME'))
	{
		throw new Exception\Basic('A script name must be defined prior to use');
	}

	$registry->register('db', '\Tuxxedo\Database');
	$registry->register('cache', '\Tuxxedo\Datastore');
	$registry->register('input', '\Tuxxedo\Input');

	$registry->set('style', new Style);

	if(isset($precache) && $precache)
	{
		$cache_buffer = Array();

		$cache->cache($precache, $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);
	}

	$cache_buffer		= Array();
	$default_templates 	= Array('header', 'footer', 'error', 'redirect', 'multierror', 'multierror_itembit');

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	$style->cache((!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)), $cache_buffer) or tuxxedo_multi_error('Unable to load template \'%s\'', $cache_buffer);

	unset($cache_buffer);

	$registry->set('options', (object) $cache->options);

	$engine_version = Version::FULL;
	$widget_hook	= false;

	if(($widget_panel = $style->getSidebarWidget($widget_hook)) !== false)
	{
		if($widget_hook)
		{
			$widget = $widget_panel;
		}
		else
		{
			eval('$widget = "' . $widget_panel . '";');
		}
	}

	unset($widget_hook);

	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>