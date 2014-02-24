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


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');


	IO::signature();


	$output	= ($output = IO::input('outputdir') ? $output : './apidump/output');
	$json 	= json_decode(file_get_contents($output . '/api_hashes.json'));

	if(!$json)
	{
		IO::text('Error: Unable to read API hashes from the JSON index file');
		exit;
	}

	IO::ul();

	foreach($json as $type)
	{
		if(!$type)
		{
			continue;
		}

		foreach($type as $index => $container)
		{
			if(!$container)
			{
				IO::li('Deleting empty index: ' . $index);

				unset($type->{$index});

				continue;
			}

			foreach($container as $name => $file)
			{
				if(!is_file($output . '/' . $file . '.html'))
				{
					IO::li('Deleting index without file: ' . $name . ' (Hash: ' . $file . ')');

					unset($container->{$name});
				}
			}

			if(!$container)
			{
				IO::li('Deleting empty index: ' . $index);

				unset($type->{$index});

				continue;
			}
		}

		if(!$type)
		{
			continue;
		}

		$json->{$index} = $type;
	}

	IO::ul(IO::TAG_END);

	file_put_contents($output . '/api_hashes.json', json_encode($json));

	IO::eol();
	IO::text('Done!');
?>