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
	 * Ini configuration overrides
	 */
	ini_set('html_errors', 'Off');

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
	define('TUXXEDO_DIR', realpath('../../'));

	/**
	 * Sets the library path
	 *
	 * @var		string
	 */
	define('TUXXEDO_LIBRARY', realpath('../../library'));

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
	$db_driver	= strtolower($configuration['database']['driver']);
	$db_subdriver	= strtolower($configuration['database']['subdriver']);

	if(($db_driver == 'sqlite' || ($db_driver == 'pdo' && $db_subdriver == 'sqlite')) && !empty($configuration['devtools']['database']))
	{
		$configuration['database']['database'] = $configuration['devtools']['database'];
	}

	unset($db_driver, $db_subdriver);

	date_default_timezone_set('UTC');

	tuxxedo_handler('exception', 'devtools_exception_handler');
	tuxxedo_handler('error', 'tuxxedo_error_handler');
	tuxxedo_handler('shutdown', 'tuxxedo_shutdown_handler');
	tuxxedo_handler('autoload', '\Tuxxedo\Loader::load');

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
	$registry->register('datastore', '\Tuxxedo\Datastore');
	$registry->register('input', '\Tuxxedo\Input');
	$registry->register('cookie', '\Tuxxedo\Cookie');
	$registry->set('style', new Style);

	if(SCRIPT_NAME != 'datastore')
	{
		$cache_buffer		= Array();
		$default_precache 	= Array('languages', 'options', 'phrasegroups');

		$datastore->cache((!isset($precache) ? $default_precache : array_unique(array_merge($default_precache, (array) $precache))), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);

		$registry->register('intl', '\Tuxxedo\Intl');

		$cache_buffer = Array();
		$intl->cache(Array('global'), $cache_buffer) or tuxxedo_multi_error('Unable to load phrasegroup \'%s\'', $cache_buffer);
	}

	$registry->set('options', (object) $datastore->options);

	$cache_buffer		= Array();
	$default_templates 	= Array('header', 'footer', 'error', 'redirect', 'multierror', 'multierror_itembit');

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	$style->cache((!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)), $cache_buffer) or tuxxedo_multi_error('Unable to load template \'%s\'', $cache_buffer);

	unset($cache_buffer);

	$engine_version = Version::FULL;
	$widget_hook	= false;

	if(($widget = $style->getSidebarWidget($widget_hook)) !== false)
	{
		if(!$widget_hook)
		{
			eval('$widget = "' . $widget . '";');
		}
	}

	unset($widget_hook);

	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>