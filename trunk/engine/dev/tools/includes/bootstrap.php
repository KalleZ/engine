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
	use Tuxxedo\Input;
	use Tuxxedo\Registry;
	use Tuxxedo\Version;


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


	require(TUXXEDO_LIBRARY . '/configuration.php');
	require(TUXXEDO_LIBRARY . '/DevTools/functions.php');
	require(TUXXEDO_LIBRARY . '/DevTools/functions_widget.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Loader.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions.php');

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

	$registry = Registry::init($configuration);

	Registry::globals('error_reporting', 	true);
	Registry::globals('errors', 		Array());

	$registry->set('timezone', new DateTimeZone('UTC'));
	$registry->set('datetime', new DateTime('now', $timezone));

	$configuration['application']['debug'] = false;

	/**
	 * Set the UTC time constant
	 *
	 * @var		integer
	 */
	define('TIMENOW_UTC', $datetime->getTimestamp());

	if(!defined('SCRIPT_NAME'))
	{
		tuxxedo_doc_error('A script name must be defined prior to use');
	}

	$registry->register('db', '\Tuxxedo\Database');
	$registry->register('datastore', '\Tuxxedo\Datastore');
	$registry->register('cookie', '\Tuxxedo\Cookie');
	$registry->register('session', '\DevTools\Session');
	$registry->register('devuser', '\DevTools\User');

	$registry->set('devuserinfo', $devuser->getUserinfo());
	$registry->set('input', new Input);
	$registry->set('style', new Style);

	if(SCRIPT_NAME != 'datastore')
	{
		$cache_buffer		= Array();
		$default_precache 	= Array('languages', 'options', 'phrasegroups', 'usergroups');

		$datastore->cache((!isset($precache) ? $default_precache : array_unique(array_merge($default_precache, (array) $precache))), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore elements', $cache_buffer);

		$registry->register('options', '\Tuxxedo\Options');
		$registry->register('intl', '\Tuxxedo\Intl');

		$cache_buffer = Array();
		$intl->cache(Array('global'), $cache_buffer) or tuxxedo_multi_error('Unable to load phrase groups', $cache_buffer);
	}
	else
	{
		$registry->register('options', '\Tuxxedo\Options');
	}

	$cache_buffer		= Array();
	$default_templates 	= Array('header', 'footer', 'error', 'redirect', 'multierror', 'multierror_itembit');

	if($configuration['devtools']['protective'])
	{
		$default_templates[] = 'password';
	}

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	$style->cache((!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)), $cache_buffer) or tuxxedo_multi_error('Unable to load templates', $cache_buffer);

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

	if($session['__devtools_authenticated'] && isset($_GET['logout']) && $_GET['logout'])
	{
		$session['__devtools_authenticated'] 	= false;
		$session['__devtools_authmode']		= $session['__devtools_userid'] = 0;

		tuxxedo_redirect('Logged out with success', './');
	}

	if($session['__devtools_authmode'] && $session['__devtools_authmode'] != $configuration['devtools']['protective'])
	{
		$session['__devtools_authenticated'] 	= false;
		$session['__devtools_authmode']		= $session['__devtools_userid'] = 0;

		unset($_POST['username']);
		unset($_POST['password']);

		eval('$header = "' . $style->fetch('header') . '";');
		eval('$footer = "' . $style->fetch('footer') . '";');
	}

	if($configuration['devtools']['protective'] && !$session['__devtools_authenticated'])
	{
		if(SCRIPT_NAME == 'datastore')
		{
			$cache_buffer = Array();

			$datastore->cache(Array('usergroups'), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore elements', $cache_buffer);

			unset($cache_buffer);
		}

		if($configuration['devtools']['protective'] == 1 && isset($_POST['password']) && $input->post('password') == $configuration['devtools']['password'] || $configuration['devtools']['protective'] == 2 && isset($_POST['username']) && isset($_POST['password']) && $devuser->login($input->post('username'), $input->post('password')) && $devuser->isGranted($configuration['devtools']['permissions']))
		{
			$session['__devtools_authenticated'] 	= true;
			$session['__devtools_authmode']		= $configuration['devtools']['protective'];
			$session['__devtools_userid']		= (($uid = $devuser->getUserinfo()->id) !== false ? $uid : 0);

			tuxxedo_header_redirect($_SERVER['SCRIPT_NAME'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
		}
		elseif(!$session['__devtools_authenticated'])
		{
			$query = $_SERVER['QUERY_STRING'];

			if(!empty($query))
			{
				$url = '';

				parse_str($query, $url);

				if($url)
				{
					unset($url['logout']);

					$query = '';

					foreach($url as $parameter => $value)
					{
						$query .= $parameter . '=' . $value . '&';
					}

					$query = rtrim($query, '&');
				}

				$query = '?' . $query;
			}

			$self = htmlspecialchars($_SERVER['SCRIPT_NAME'] . $query_string, ENT_QUOTES);

			eval(page('password'));
			exit;
		}
	}

	$configuration['application']['debug'] = true;
?>