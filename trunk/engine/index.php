<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */


	/**
	 * Include the boostraper
	 */
	require('./library/bootstrap.php');

	/**
	 * Just print the engine version to show that 
	 * the bootstraper was a success
	 */
	echo($header);
	echo('Tuxxedo Engine version: ' . Tuxxedo::VERSION_STRING . (TUXXEDO_DEBUG ? ' (DEBUG)' : ''));
	echo($footer);
?>