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
	 * Sets the php version id for php versions that doesn't 
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
	 * Include database driver interfaces ect.
	 */
	require(TUXXEDO_DIR . '/includes/class_database.php');

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
	register_shutdown_function('tuxxedo_shutdown');

	/**
	 * Set database table prefix constant
	 *
	 * @var		string
	 */
	define('TUXXEDO_PREFIX', (string) $configuration['database']['prefix']);

	/**
	 * Set the debug mode constant
	 */
	define('TUXXEDO_DEBUG', defined('Tuxxedo::DEBUG') && Tuxxedo::DEBUG);

	/**
	 * Set error reporting level
	 */
	error_reporting(-1);

	/**
	 * Set default timezone
	 */
	date_default_timezone_set('UTC');

	/**
	 * Current time constant
	 */
	define('TIMENOW', time());

	/**
	 * Data filter constant, numeric value
	 *
	 * @var		integer
	 */
	define('TYPE_NUMERIC', 		0x0001);

	/**
	 * Data filter constant, string value
	 *
	 * @var		integer
	 */
	define('TYPE_STRING', 		0x0002);

	/**
	 * Data filter constant, email value
	 *
	 * @var		integer
	 */
	define('TYPE_EMAIL', 		0x0003);

	/**
	 * Data filter constant, boolean value
	 *
	 * @var		integer
	 */
	define('TYPE_BOOLEAN', 		0x0004);

	/**
	 * Data filter constant, callback value
	 *
	 * @var		integer
	 */
	define('TYPE_CALLBACK', 	0x0005);

	/**
	 * Data filter option, gets the raw value 
	 * of the input without any type of santizing
	 *
	 * @var		integer
	 */
	define('INPUT_OPT_RAW',		0x01FF);

	/**
	 * Data filter option, tells the cleaner that this 
	 * is an array input and any of its elements must be of 
	 * the given type. Note that recursive operations are not 
	 * done by the data filter
	 *
	 * @var		integer
	 */
	define('INPUT_OPT_ARRAY', 	0x02FF);

	/**
	 * Construct the main registry
	 */
	$tuxxedo = Tuxxedo::init($configuration);

	/**
	 * Set globals
	 */
	Tuxxedo::globals('error_reporting', 	true);
	Tuxxedo::globals('errors', 		new ArrayObject);

	/**
	 * Register the default instances
	 */
	$tuxxedo->register('db', 'Tuxxedo_Database');
	$tuxxedo->register('cache', 'Tuxxedo_Datastore');
	$tuxxedo->register('filter', 'Tuxxedo_DataFilter');

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
	$tuxxedo->register('user', 'Tuxxedo_UserSession');

	/**
	 * Options and configuration references
	 */
	$tuxxedo->set('options', (object) Tuxxedo::getOptions());
	$tuxxedo->set('configuration', $configuration);

	/**
	 * User information references
	 */
	$tuxxedo->set('userinfo', $user->getUserinfo());
	$tuxxedo->set('usergroup', $user->getUsergroup());

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
					'error', 'error_validation', 'error_validationbit', 

					/* Miscellaneous */
					'redirect'
					);

	if(isset($action_templates) && isset($_REQUEST['do']) && array_key_exists((string) $_REQUEST['do'], $action_templates))
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
	 * Header and footer templates for the main site
	 */
	eval('$header = "' . $style->fetch('header') . '";');
	eval('$footer = "' . $style->fetch('footer') . '";');
?>