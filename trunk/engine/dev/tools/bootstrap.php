<?php
	/**
	 * Emulation layer of the bootstrap.php in the includes 
	 * root, this is to prevent corrupt datastores from 
	 * stopping the execution.
	 *
	 * We don't assume the user of this uses anything below 
	 * PHP 5.2.7, so no compatibility layer here.
	 *
	 * We must not use any sessions here, as it would cause 
	 * the development tools to interfere with the main site.
	 */

	define('CWD', 		'../..');
	define('TUXXEDO', 	1337);

	require(CWD . '/includes/configuration.php');
	require(CWD . '/includes/class_core.php');
	require(CWD . '/includes/class_database.php');
	require(CWD . '/includes/functions.php');
	require(CWD . '/includes/functions_debug.php');

	if(!defined('Tuxxedo::DEBUG') || !Tuxxedo::DEBUG)
	{
		throw new Tuxxedo_Basic_Exception('Debug mode must be enabled to load the development tools');
	}

	define('TUXXEDO_DEBUG', 	true);
	define('TUXXEDO_DIR', 		CWD);
	define('TUXXEDO_PREFIX', 	$configuration['database']['prefix']);
	define('TUXXEDO_PHP_VERSION', 	PHP_VERSION_ID);

	Tuxxedo::globals('error_reporting', 	true);
	Tuxxedo::globals('errors', 		new ArrayObject);

	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_devmenu');
	spl_autoload_register('tuxxedo_autoload_handler');

	$tuxxedo = Tuxxedo::init($configuration);

	$tuxxedo->register('db', 	'Tuxxedo_Database');
	$tuxxedo->register('cache', 	'Tuxxedo_Datastore');

	$tuxxedo->set('timezone', new DateTimeZone('UTC'));
	$tuxxedo->set('datetime', new DateTime('now', $timezone));

	function tuxxedo_devmenu()
	{
		if(stristr(ob_get_contents(), '<?xml') !== false)
		{
			return;
		}

		echo('<hr>');
		echo('<a href="./datastore.php">datastore rebuilder</a>');
		echo(' | ');
		echo('<a href="./templates.php">template manager</a>');
		echo(' | ');
		echo('<a href="./options.php">options manager</a>');
		echo(' | ');
		echo('<a href="./sessions.php">user sessions</a>');
		echo(' | ');
		echo('<a href="./password.php">password generator</a>');
	}

	function redirect($location)
	{
		if(!headers_sent())
		{
			header('Location: ' . $location);
			exit;
		}

		echo('<a href="' . $location . '">Click here to get redirected...</a>');
	}

	function htmlize($input)
	{
		return(htmlspecialchars($input, ENT_QUOTES));
	}

	function convert_to_option_type($type, $value)
	{
		switch($type)
		{
			case('b'):
			{
				return((boolean) $value);
			}
			break;
			case('i'):
			{
				return((integer) $value);
			}
			break;
		}

		return((string) $value);
	}
?>