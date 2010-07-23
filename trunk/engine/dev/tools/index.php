<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'index'
					);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'index');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	eval(page('index'));
?>