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
	 * I WANT A UNIT TESTING FRAMEWORK, I WANT A UNIT TESTING FRAMEWORK, ....
	 */
	$result = $db->query('
				SELECT 
					`id`, 
					`title`
				FROM 
					`' . TUXXEDO_PREFIX . 'templates`
				ORDER BY 
					`id` 
				ASC');

	if(!$result || !$result->getNumRows())
	{
		echo 'Error: no results';
		exit;
	}

	/* ->setFetchType() ? */

	foreach($result as $row)
	{
		printf('[%d] %s<br />', $row['id'], $row['title']);
	}

	exit;

	/**
	 * Just print the engine version to show that
	 * the bootstraper was a success
	 */
	echo new Template('index', true, Array(
						'version' => Version::FULL . (Version::PREVIEW ? ' (development preview)' : '') . (TUXXEDO_DEBUG ? ' (DEBUG)' : '')
						));
?>