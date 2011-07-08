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
	 * @subpackage		Dev
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Utilities\IO;
	use Tuxxedo\Database;


	/**
	 * Bootstraper
	 */
	require('./includes/bootstrap.php');


	/**
	 * Database connection and some hacking of the configuration
	 */
	$registry->set('db', new Database\Driver\Sqlite(Array(
								'driver'	=> 'sqlite', 
								'database'	=> '../sql/bin/tuxxedo_documentation.sqlite3', 
								'password'	=> ''
								)));

	$datamap = @unserialize(@file_get_contents('../api/dumps/serialized.dump'));

	if(!$datamap)
	{
		IO::text('Error, unable to load serialized dump file');
	}

	IO::headline('API indexer for Tuxxedo Engine');

	foreach($datamap as $file => $index)
	{
		$db->query('
				REPLACE INTO 
					`' . TUXXEDO_PREFIX . 'file`
					(
						`path`, 
						`namespaces`, 
						`constants`, 
						`aliases`, 
						`classes`, 
						`interfaces`, 
						`functions`, 
						`package`, 
						`docblock` 
					)
					VALUES
					(
						\'%s\', 
						\'\', 
						\'\', 
						\'\', 
						\'\', 
						\'\', 
						\'\', 
						\'\', 
						\'\'
					)', $file);

		$file_id = $db->getInsertId();
		echo $file_id, '<br>';
	}
?>