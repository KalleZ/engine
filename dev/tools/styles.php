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
	use Tuxxedo\Filter;
	use Tuxxedo\Datamanager;


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'styles_index'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'statistics'	=> Array(
									'tools_statistics', 
									'tools_statistics_itembit'
									)
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'styleinfo'
					);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'styles');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	if(($styleid = $filter->get('style', Filter::TYPE_NUMERIC)))
	{
		if(!isset($cache->styleinfo[$styleid]))
		{
			tuxxedo_error('Invalid style id');
		}

		$styledm = Datamanager\Adapter::factory('style', $styleid, false);
	}

	switch(strtolower($filter->get('do')))
	{
		case('password'):
		{
			if(isset($_POST['submit']) && ($password = $filter->post('keyword')) !== false && !empty($password) && ($chars = $filter->post('characters')) % 8 === 0)
			{
				$salt 		= htmlspecialchars(\Tuxxedo\User::getPasswordSalt($chars));
				$hash 		= \Tuxxedo\User::getPasswordHash($password, $salt);
				$password	= htmlspecialchars($password);

				eval('$results = "' . $style->fetch('tools_password_result') . '";');
			}

			eval(page('tools_password'));
		}
		break;
		default:
		{
			eval(page('styles_index'));
		}
		break;
	}
?>