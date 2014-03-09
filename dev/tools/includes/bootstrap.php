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
	use DevTools\Bootstrap;
	use Tuxxedo\Registry;
	use Tuxxedo\Template;


	/**
	 * Sets the path to where the root script is
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
	require(TUXXEDO_LIBRARY . '/Tuxxedo/Bootstrap.php');
	require(TUXXEDO_LIBRARY . '/DevTools/Bootstrap.php');

	$default_precache 	= ['languages', 'options', 'phrasegroups', 'usergroups'];
	$default_phrasegroups	= ['global'];
	$default_templates 	= ['header', 'footer', 'error', 'redirect', 'multierror', 'multierror_itembit'];

	Bootstrap::setPreloadables('datastore', (!isset($precache) ? $default_precache : array_merge($default_precache, (array) $precache)));
	Bootstrap::setPreloadables('phrasegroups', (!isset($phrasegroups) ? $default_phrasegroups : array_merge($default_phrasegroups, (array) $phrasegroups)));


	if($configuration['devtools']['protective'])
	{
		$default_templates[] = 'password';
	}

	if(isset($action_templates) && isset($_GET['do']) && isset($action_templates[(string) $_GET['do']]))
	{
		$default_templates = array_merge($default_templates, (array) $action_templates[(string) $_GET['do']]);
	}

	Bootstrap::setPreloadables('templates', (!isset($templates) ? $default_templates : array_merge($default_templates, (array) $templates)));

	unset($templates);


	/**
	 * Bootstrap
	 */
	if(SCRIPT_NAME != 'datastore')
	{
		Bootstrap::init(Bootstrap::MODE_CUSTOM, Bootstrap::FLAG_DATE | Bootstrap::FLAG_DATABASE | Bootstrap::FLAG_DATASTORE | Bootstrap::FLAG_OPTIONS | Bootstrap::FLAG_INTL);
	}
	else
	{
		Bootstrap::init(Bootstrap::MODE_CUSTOM, Bootstrap::FLAG_DATE | Bootstrap::FLAG_DATABASE | Bootstrap::FLAG_OPTIONS);
	}

	$registry = Registry::init();

	/**
	 * Header and footer templates for the main site
	 */
	$header = new Template('header', true);
	$footer = new Template('footer', true);
?>