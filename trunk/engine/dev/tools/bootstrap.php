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

	define('TUXXEDO_DEBUG', 	defined('Tuxxedo::DEBUG') && Tuxxedo::DEBUG);
	define('TUXXEDO_DIR', 		CWD);
	define('TUXXEDO_PREFIX', 	$configuration['database']['prefix']);
	define('TUXXEDO_PHP_VERSION', 	PHP_VERSION_ID);

	EG('error_reporting', 	true);
	EG('errors', 		new ArrayObject);

	$tuxxedo = Tuxxedo::init($configuration);

	$tuxxedo->register('db', 	'Tuxxedo_Database');
	$tuxxedo->register('cache', 	'Tuxxedo_Datastore');

	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_devmenu');

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