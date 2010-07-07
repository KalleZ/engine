<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */


	/**
	 * This odd check must be here in order to prevent direct execution 
	 * of the bootstrap script
	 */
	(sizeof(get_included_files()) != 1) or exit;

	/**
	 * General constant needed to access include files
	 *
	 * @var		boolean
	 */
	define('TUXXEDO', true);

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
	 * Sets the PHP version id for PHP versions that doesn't 
	 * have the PHP_VERSION_ID constant
	 */
	if(defined('PHP_VERSION_ID'))
	{
		/**
		 * Set the version id constant, PHP_VERSION_ID is defined
		 *
		 * @var 	integer
		 */
		define('TUXXEDO_PHP_VERSION', PHP_VERSION_ID);
	}
	else
	{
		$version = PHP_VERSION;

		/**
		 * Set the version id constant, PHP_VERSION_ID is not defined 
		 * lets emulate it
		 *
		 * @var 	integer
		 */
		define('TUXXEDO_PHP_VERSION', ($version{0} * 10000 + $version{2} * 100 + $version{4}));

		unset($version);
	}

	/**
	 * Sets the path to where the root script is, if the 
	 * constant CWD is defined before including this file, 
	 * then it will be used as root dir
	 *
	 * @var		string
	 */
	define('TUXXEDO_DIR', 	(defined('CWD') ? CWD : getcwd()));

	/**
	 * Configuration
	 */
	require(TUXXEDO_DIR . '/includes/configuration.php');

	/**
	 * Include core classes
	 */
	require(TUXXEDO_DIR . '/includes/class_core.php');

	/**
	 * Include general functions
	 */
	require(TUXXEDO_DIR . '/includes/functions.php');

	/**
	 * Set various handlers for errors, exceptions and 
	 * shutdown
	 */
	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_shutdown_handler');
	spl_autoload_register(Array('Tuxxedo_Autoloader', 'load'));

	/**
	 * Set database table prefix constant
	 *
	 * @var		string
	 */
	define('TUXXEDO_PREFIX', $configuration['database']['prefix']);

	/**
	 * Set the debug mode constant
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
	 * If debug mode is activated we need the special 
	 * debugging functions
	 */
	if(TUXXEDO_DEBUG)
	{
		/**
		 * Include the debugging functions
		 */
		require(TUXXEDO_DIR . '/includes/functions_debug.php');
	}

	/**
	 * Set error reporting level
	 */
	error_reporting(-1);

	/**
	 * Construct the main registry
	 */
	$tuxxedo = Tuxxedo::init($configuration);

	/**
	 * Set globals
	 */
	Tuxxedo::globals('hooks', 		true);
	Tuxxedo::globals('error_reporting', 	true);
	Tuxxedo::globals('errors', 		Array());

	/**
	 * Set the UTC timestamp, we need this for things such as 
	 * session handling
	 */
	define('TIMENOW_UTC', isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());

	/**
	 * Register the default instances
	 */
	$tuxxedo->register('db', 'Tuxxedo_Database');
	$tuxxedo->register('cache', 'Tuxxedo_Datastore');

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
	$tuxxedo->register('user', 'Tuxxedo_User');

	/**
	 * Options and configuration references
	 */
	$tuxxedo->set('options', (object) Tuxxedo::getOptions());
	$tuxxedo->set('configuration', $configuration);

	/**
	 * User information references
	 */
	$tuxxedo->set('userinfo', $user->getUserInfo(NULL, NULL, Tuxxedo_User::OPT_CURRENT_ONLY));
	$tuxxedo->set('usergroup', $user->getUserGroupInfo());

	/**
	 * Date and Timezone references
	 */
	$tz = strtoupper(empty($userinfo->id) ? $options->date_timezone : $userinfo->timezone);

	if($tz != 'UTC')
	{
		date_default_timezone_set($tz);
	}

	$tuxxedo->set('timezone', new DateTimeZone($tz));
	$tuxxedo->set('datetime', new DateTime('now', $timezone));

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
	$tuxxedo->register('style', 'Tuxxedo_Style');
	$tuxxedo->register('intl', 'Tuxxedo_Internationalization');

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

	if(isset($action_templates) && isset($_REQUEST['do']) && isset($action_templates[(string) $_REQUEST['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_REQUEST['do']]);
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
	$tuxxedo->set('phrase', $intl->getPhrases());

	/**
	 * Header and footer templates for the main site
	 */
	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>