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
	 * @subpckage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Version;


	/**
	 * Bootstraper
	 */
	require('./library/bootstrap.php');


	/**
	 * Just print the engine version to show that
	 * the bootstraper was a success
	 */
	echo($header);
	echo('Tuxxedo Engine version: ' . Version::FULL . (TUXXEDO_DEBUG ? ' (DEBUG)' : ''));
	echo($footer);
?>