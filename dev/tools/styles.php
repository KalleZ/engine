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
	use Tuxxedo\Datamanager;
	use Tuxxedo\Exception;
	use Tuxxedo\Filter;


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
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'options', 
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
		case('style'):
		{
			switch(strtolower($filter->get('action')))
			{
				case('add'):
				case('edit'):
				case('delete'):
				{
					throw new Exception\Core('Style handlers not implemented');
				}
				break;
				default:
				{
					tuxxedo_error('Invalid style action');
				}
			}
		}
		break;
		case('templates'):
		{
			switch(strtolower($filter->get('action')))
			{
				case('add'):
				case('edit'):
				case('delete'):
				{
					throw new Exception\Core('Template handlers not implemented');
				}
				break;
				default:
				{
					tuxxedo_error('Invalid template action');
				}
			}
		}
		break;
		default:
		{
			eval(page('styles_index'));
		}
		break;
	}
?>