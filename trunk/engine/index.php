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
	use Tuxxedo\Template;
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
	 * Provoke an error page
	 */
	if(isset($_GET['error']) && $_GET['error'])
	{
		$db->query('Example of an error page');
	}

	/**
	 * Just print the engine version to show that
	 * the bootstraper was a success
	 */
	echo new Template('index', true, Array(
						'app'		=> $configuration['application'], 
						'debug'		=> $configuration['debug'], 
						'version'	=> Version::FULL
						));
?>