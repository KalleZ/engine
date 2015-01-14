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
	use Tuxxedo\Template\Layout;
	use Tuxxedo\Version;


	/**
	 * Precache templates
	 */
	$templates = [
			/* Index page */
			'index'
			];


	/**
	 * Bootstraper
	 */
	require('./library/bootstrap.php');
use Tuxxedo\Helper;
$d = Helper::factory('database');
$x = 0;
foreach($d->getTables() as $table)
{
++$x;
echo '<pre>';
var_dump($table);
echo '<hr>';
}
echo '<h2>' . $x . '</h2>';
die;
	/**
	 * Just print the engine version to show that
	 * the bootstraper was a success
	 */
	echo new Layout('index', ['version' => Version::FULL]);
?>