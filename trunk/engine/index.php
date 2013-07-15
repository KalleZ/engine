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
	use Tuxxedo\Registry;
	use Tuxxedo\Template\Layout;
	use Tuxxedo\Version;


	/**
	 * Precache templates
	 */
	$templates = Array(
				/* Index page */
				'index'
				);


	/**
	 * Bootstraper
	 */
	require('./library/bootstrap.php');


	/**
	 * Just print the engine version to show that
	 * the bootstraper was a success
	 */
	$config = Registry::getConfiguration();

	echo new Layout('index', Array(
					'app'		=> $config['application'], 
					'debug'		=> $config['debug'], 
					'version'	=> Version::FULL
					));
?>