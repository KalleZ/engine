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


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'database_index'
					);

	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'database';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	switch(strtolower($input->get('do')))
	{
		case('test'):
		{
		}
		break;
		default:
		{
			eval(page('database_index'));
		}
		break;
	}
?>