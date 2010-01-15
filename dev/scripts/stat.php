<?php
	const ENGINE_LOCATION = '../..';

	$recursive_glob = function($expression)
	{
		global $recursive_glob;
		static $expression_prefix;

		if(!$expression_prefix)
		{
			$expression_prefix = strlen($expression) + 1;
		}

		$glob = glob($expression . '/*');

		if(!sizeof($glob))
		{
			return(false);
		}

		$return_value = Array();

		foreach($glob as $entry)
		{
			if(is_dir($entry))
			{
				if(($entries = $recursive_glob($entry)) !== false)
				{
					foreach($entries as $sub_entry)
					{
						$return_value[] = $sub_entry;
					}
				}

				continue;
			}

			$return_value[] = str_replace('\\', '/', substr_replace($entry, '', 0, $expression_prefix));
		}

		return($return_value);
	};

	$files = $recursive_glob(ENGINE_LOCATION);

	if(!$files || !sizeof($files))
	{
		die('Engine source path not found');
	}

	$statistics = Array(
				'lines'		=> Array(), 
				'size'		=> Array(), 
				'total'		=> Array(
								'lines'		=> 0, 
								'size'		=> 0
								), 
				'php'		=> Array(
								'blanks'	=> 0, 
								'tokens'	=> 0
								)
				);

	foreach($files as $path)
	{
		$path		= ENGINE_LOCATION . '/' . $path;
		$extension 	= pathinfo($path, PATHINFO_EXTENSION);

		if(!isset($statistics['lines'][$extension]))
		{
			$statistics['lines'][$extension] = 0;
		}

		if(!isset($statistics['size'][$extension]))
		{
			$statistics['size'][$extension] = 0;
		}

		$l = file($path);

		if(stripos($extension, 'php') !== false)
		{
			foreach($l as $line)
			{
				$line = trim($line);

				if(empty($line))
				{
					++$statistics['php']['blanks'];
				}
			}

			$statistics['php']['tokens'] += sizeof(token_get_all(file_get_contents($path)));
		}

		$statistics['lines'][$extension] 	+= sizeof($l);
		$statistics['size'][$extension]		+= ($s = filesize($path));

		$statistics['total']['lines']		+= sizeof($l);
		$statistics['total']['size']		+= $s;
	}

	echo('<h1>Statistics</h1>');
	echo('<ul>');
	echo('<li>' . sizeof($statistics['lines']) . ' different extension(s)</li>');
	echo('<li>' . $statistics['total']['lines'] . ' total lines of code</li>');
	echo('<li>' . $statistics['total']['size'] . ' total size in bytes</li>');
	echo('<li>');
	echo('<ul>');

	foreach($statistics['lines'] as $ext => $lines)
	{
		echo('<li>');
		echo('<strong>' . strtoupper($ext) . '</strong>: ');
		echo('Lines: ' . $lines . ' ');

		if(isset($statistics[$ext]))
		{
			echo('(');

			$end = sizeof($statistics[$ext]);

			foreach($statistics[$ext] as $prop => $val)
			{
				echo(ucwords($prop) . ': ' . $val . (--$end ? ', ' : ''));
			}

			echo(') ');
		}

		echo('Total size: ' . $statistics['size'][$ext]);
		echo('</li>');
	}

	echo('</ul>');
	echo('</li>');
	echo('</ul>');
?>