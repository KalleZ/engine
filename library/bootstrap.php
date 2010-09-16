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
	use Tuxxedo\Registry;
	use Tuxxedo\User;

	/**
	 * Disable html errors, so error messages dont link to the 
	 * manual
	 */
	ini_set('html_errors', 'Off');

	/**
	 * Disable magic runtime quotes, this helps not cluttering 
	 * up alot of the code to check for them, simply just by 
	 * disabling them
	 */
	ini_set('magic_quotes_runtime', 'Off');

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
	define('TUXXEDO_LIBRARY', TUXXEDO_DIR . '/library');

	/**
	 * Configuration
	 */
	require(TUXXEDO_LIBRARY . '/configuration.php');

	/**
	 * Include functions we cannot autoload
	 */
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Loader.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions.php');

	/**
	 * Set various handlers for errors, exceptions and 
	 * shutdown
	 */
	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_shutdown_handler');
	spl_autoload_register('Tuxxedo\Loader::load');

	/**
	 * Set database table prefix constant
	 *
	 * @var		string
	 */
	define('TUXXEDO_PREFIX', $configuration['database']['prefix']);

	/**
	 * Set the debug mode constant
	 *
	 * @var		boolean
	 */
	define('TUXXEDO_DEBUG', $configuration['application']['debug']);

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

	/**
	 * If debug mode is activated we need the special 
	 * debugging functions
	 */
	if(TUXXEDO_DEBUG)
	{
		/**
		 * Include the debugging functions
		 */
		require('Tuxxedo/functions_debug.php');
	}

	/**
	 * Set error reporting level
	 */
	error_reporting(-1);

	/**
	 * Construct the main registry
	 */
	$registry = Registry::init($configuration);

	/**
	 * Set globals
	 */
	Registry::globals('error_reporting', 	true);
	Registry::globals('errors', 		Array());

	/**
	 * Set the UTC timestamp, we need this for things such as 
	 * session handling
	 */
	date_default_timezone_set('UTC');
	define('TIMENOW_UTC', isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());

	/**
	 * Register the default instances
	 */
	$registry->register('db', '\Tuxxedo\Database');
	$registry->register('cache', '\Tuxxedo\Datastore');
	/**
	 * Precache elements from datastore
	 */
	$cache_buffer		= Array();
	$default_precache 	= Array('options', 'styleinfo', 'usergroups', 'languages', 'phrasegroups');

	$cache->cache((!isset($precache) ? $default_precache : array_merge($default_precache, (array) $precache)), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);

	/**
	 * Now the datastore is loaded we must instanciate the 
	 * user session, note that the invoke method sets the 
	 * cookie parameters and starts session itself here
	 */
	$registry->register('user', 'Tuxxedo\User');

	/**
	 * Options and configuration references
	 */
	$registry->set('options', (object) $cache->options);
	$registry->set('configuration', $configuration);

	/**
	 * User information references
	 */
	$registry->set('userinfo', $user->getUserInfo(NULL, NULL, \Tuxxedo\User::OPT_CURRENT_ONLY));
	$registry->set('usergroup', $user->getUserGroupInfo());

	/**
	 * Date and Timezone references
	 */
	$tz = strtoupper(empty($userinfo->id) ? $options->date_timezone : $userinfo->timezone);

	if($tz != 'UTC')
	{
		date_default_timezone_set($tz);
	}

	$registry->set('timezone', new DateTimeZone($tz));
	$registry->set('datetime', new DateTime('now', $timezone));

	unset($tz);

	/**
	 * Current time constant
	 */
	define('TIMENOW', $datetime->getTimestamp());

	/**
	 * We can only load the styling & internationalization APIs 
	 * once the datastore elements are loaded and user sessions 
	 * have been instanciated
	 */
	$registry->register('style', '\Tuxxedo\Style');
	$registry->register('intl', '\Tuxxedo\Intl');

	/**
	 * Precache templates
	 */
	$cache_buffer		= Array();
	$default_templates 	= Array(
					/* Common templates */
					'header', 'footer', 

					/* Error handling */
					'error', 

					/* Miscellaneous */
					'redirect'
					);

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	$style->cache((!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)), $cache_buffer) or tuxxedo_multi_error('Unable to load template \'%s\'', $cache_buffer);

	unset($cache_buffer);

	/**
	 * Precache phrase groups
	 */
	$cache_buffer		= Array();
	$default_phrasegroups	= Array(
					/* Common groups */
					'global'
					);

	$intl->cache((!isset($phrasegroups) ? $default_phrasegroups : array_merge($default_phrasegroups, (array) $phrasegroups)), $cache_buffer) or tuxxedo_multi_error('Unable to load phrase groups \'%s\'', $cache_buffer);

	unset($cache_buffer);

	/**
	 * Get phrases
	 */
	$registry->set('phrase', $intl->getPhrases());

	/**
	 * Header and footer templates for the main site
	 */
	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>