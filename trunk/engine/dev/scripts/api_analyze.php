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


	$engine_path	= realpath(__DIR__ . '/../../library/');
	$files 		= analyze(new DirectoryIterator($engine_path));
	$datamap	= Array();

	echo('<h1>Lexical analyze of engine API</h1>');

	foreach($files as $real_file)
	{
		$file = substr(str_replace($engine_path, '', $real_file), 1);

		printf('<h3>/%s</h3>', str_replace('\\', '/', $file));

		$context 		= new stdClass;
		$context->current 	= false;

		$datamap[$file]		= Array(
						'namespaces'	=> Array(), 
						'classes'	=> Array(), 
						'interfaces'	=> Array(), 
						'constants'	=> Array(), 
						'functions'	=> Array()
						);

		$tokens			= $tokens_copy = token_get_all(file_get_contents($real_file));

		foreach($tokens as $index => $token)
		{
			if(!is_array($token))
			{
				$token = Array(0, $token);
			}

			switch($token[0])
			{
				case(T_NAMESPACE):
				{
					if(($name = lexical_scan_concat($tokens_copy, $index, ';')) == false)
					{
						continue;
					}

					$datamap[$file]['namespaces'][] = $name;

					printf('NAMESPACE (%s)<br />', $name);
				}
				break;
				case(T_INTERFACE):
				case(T_CLASS):
				{
					if(($name = lexical_scan($tokens_copy, $index, T_STRING)) == false)
					{
						continue;
					}

					$type					= ($token[0] == T_CLASS ? 'class' : 'interface');
					$type_multiple				= ($token[0] == T_CLASS ? 'classes' : 'interfaces');

					$context->current 			= $token[0];
					$context->type				= $type;
					$context->type_multiple			= $type_multiple;
					$context->{$type}			= $name;

					$datamap[$file][$type_multiple][$name]	= Array(
											'constants'	=> Array(), 
											'properties'	=> Array(), 
											'methods'	=> Array(), 
											'namespace'	=> end($datamap[$file]['namespaces']), 
											'implements'	=> Array(),  
											'metadata'	=> Array(
															'final'		=> lexical_scan_backwards($tokens_copy, $index, T_FINAL, T_OPEN_TAG), 
															'abstract'	=> lexical_scan_backwards($tokens_copy, $index, T_ABSTRACT, T_OPEN_TAG)
															)
											);

					/* var_dump($datamap[$file][$type_multiple][$name]['implements']); */

					printf('%s (%s) %s<br />', strtoupper($type), $name, dump_metadata($datamap[$file][$type_multiple][$name]['metadata']));
				}
				break;
				case(T_FUNCTION):
				{
					if(($function = lexical_scan($tokens_copy, $index, T_STRING)) == false)
					{
						continue;
					}

					if($context->current == T_CLASS || $context->current == T_INTERFACE)
					{
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['methods'][] = Array(
																		'method'	=> $function, 
																		'namespace'	=> end($datamap[$file]['namespaces']), 
																		'metadata'	=> Array(
																						'final'		=> lexical_scan_backwards($tokens_copy, $index, T_FINAL, '{}'), 
																						'abstract'	=> lexical_scan_backwards($tokens_copy, $index, T_ABSTRACT, '{}'), 
																						'public'	=> lexical_scan_backwards($tokens_copy, $index, T_PUBLIC, '{}'), 
																						'protected'	=> lexical_scan_backwards($tokens_copy, $index, T_PROTECTED, '{}'), 
																						'private'	=> lexical_scan_backwards($tokens_copy, $index, T_PRIVATE, '{}'), 
																						'static'	=> lexical_scan_backwards($tokens_copy, $index, T_STATIC, '{}')
																						)
																		);

						$metadata = end($datamap[$file][$context->type_multiple][$context->{$context->type}]['methods']);

						printf('- METHOD (%s) %s<br />', $function, dump_metadata($metadata['metadata']));

						unset($metadata);
					}
					else
					{
						$datamap[$file]['functions'][] = Array(
											'function'	=> $function, 
											'namespace'	=> end($datamap[$file]['namespaces'])
											);

						printf('FUNCTION (%s)<br />', $function);
					}
				}
				break;
				case(T_STRING):
				{
					if($context->current !== false)
					{
						continue;
					}

					if(strtolower($token[1]) == 'define')
					{
						if(($const = lexical_scan($tokens_copy, $index, T_CONSTANT_ENCAPSED_STRING)) == false)
						{
							continue;
						}

						$datamap[$file]['constants'][] = $const = substr($const, 1, strlen($const) - 2);

						printf('GLOBAL CONSTANT (%s)<br />', $const);
					}
				}
				break;
				case(T_CONST):
				{
					if(($const = lexical_scan($tokens_copy, $index, T_STRING)) == false)
					{
						continue;
					}

					if($context->current !== false)
					{
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['constants'][] = Array(
																		'constant'	=> $const, 
																		'namespace'	=> end($datamap[$file]['namespaces'])
																		);

						printf('- CONSTANT (%s)<br />', $const);
					}
					else
					{
						$datamap[$file]['constants'][] = $const;

						printf('GLOBAL CONSTANT (%s)<br />', $const);
					}
				}
				break;
				case(T_VARIABLE):
				{
					if($context->current === false || sizeof($datamap[$file][$context->type_multiple][$context->{$context->type}]['methods']))
					{
						continue;
					}

					$property 										= substr($token[1], 1);
					$datamap[$file][$context->type_multiple][$context->{$context->type}]['properties'][]	= $property;

					printf('- PROPERTY (%s)<br />', $property);
				}
				break;
			}
		}
	}

	file_put_contents(__DIR__ . '/../api/dumps/serialized.dump', serialize($datamap));
	file_put_contents(__DIR__ . '/../api/dumps/json.dump', json_encode($datamap));


	function analyze(DirectoryIterator $iterator)
	{
		$files = $extra = Array();

		$iterator->rewind();

		foreach($iterator as $entry)
		{
			if($entry->isDot())
			{
				continue;
			}

			if($entry->isDir())
			{
				$extra = array_merge($extra, analyze(new DirectoryIterator($entry->getPathName())));
			}
			elseif(strtolower(pathinfo($path = $entry->getPathName(), PATHINFO_EXTENSION)) == 'php')
			{
				$files[] = realpath($path);
			}
		}

		$files = array_merge($files, $extra);

		return($files);
	}

	function dump_metadata(Array $data)
	{
		$dump = '';

		foreach($data as $parameter => $exists)
		{
			if($exists)
			{
				$dump .= $parameter . ', ';
			}
		}

		return((empty($dump) ? '' : 'meta=' . rtrim($dump, ', ')));
	}

	function lexical_next_index(Array $tokens, $start_index, $token)
	{
		$inc = 0;

		while(isset($tokens[$start_index + $inc++]))
		{
			$token_data = $tokens[$start_index + $inc - 1];
			$token_data = (is_array($token_data) ? $token_data[0] : $token_data);

			if($token_data == $token)
			{
				return($start_index + $inc - 1);
			}
		}

		return(false);
	}

	function lexical_scan(Array $tokens, $start_index, $token)
	{
		$inc			= 0;
		$searching_for_token 	= is_numeric($token);

		while(isset($tokens[$start_index + $inc++]))
		{
			$t = $tokens[$start_index + $inc - 1];

			if(is_array($t) && $searching_for_token && $t[0] === $token)
			{
				return($t[1]);
			}
			elseif($t == $token)
			{
				return($start_index + $inc);
			}
		}

		return(false);
	}

	function lexical_scan_concat(Array $tokens, $start_index, $token, $skip_whitespace = true)
	{
		$scanned 		= '';
		$inc			= 0;
		$searching_for_token 	= is_numeric($token);

		++$start_index;

		while(isset($tokens[$start_index + $inc++]))
		{
			$token_data 	= $tokens[$start_index + $inc - 1];
			$token_array 	= isset($token_data[1]);

			if($skip_whitespace && $token_array && $token_data[0] == T_WHITESPACE)
			{
				continue;
			}
			elseif($token_array && $searching_for_token && $token_data[0] === $token || $token_data == $token)
			{
				break;
			}

			$scanned .= (isset($token_data[1]) ? $token_data[1] : $token_data);
		}

		return($scanned);
	}

	function lexical_scan_implements(Array $tokens, $start_index)
	{
		/* This code sucks and should be terminated */

		$inc 			= $matched_index = 0;
		$matched_tokens		= Array();
		$start_index		= lexical_next_index($tokens, $start_index, T_IMPLEMENTS);

		while(isset($tokens[$start_index + $inc++]))
		{
			$token_data 		= $tokens[$start_index + $inc - 1];
			$token_data 		= (is_array($token_data) ? $token_data[0] : $token_data);
			$namespace_interface	= $token_data == T_NS_SEPARATOR;

			if(!$namespace_interface && $token_data == '{')
			{
				break;
			}
			elseif($token_data == T_STRING || $namespace_interface)
			{
				if($namespace_interface)
				{
					$matched_tokens[key($matched_tokens)] .= '\\';
				}
				else
				{
					$matched_tokens[] = (is_array($tokens[$start_index + $inc - 1]) ? $tokens[$start_index + $inc - 1][1] : $token_data);
				}
			}
		}

		return(array_map('stripslashes', $matched_tokens));
	}

	function lexical_scan_backwards(Array $tokens, $start_index, $token, $stop_token)
	{
		/* This code is pretty broken aswell and needs fine tuning to work correctly in all cases */

		return(false);

		$inc = 0;

		$tokens = array_reverse($tokens);

		if(strlen($stop_token) > 1)
		{
			$stop_token = str_split($stop_token);
		}

		while(isset($tokens[$start_index + $inc++]))
		{
			$token_data = $tokens[$start_index + $inc - 1];
			$token_data = (is_array($token_data) ? $token_data[0] : $token_data);

			if(is_array($stop_token) && in_array($token_data, $stop_token) || $token_data == $stop_token)
			{
				break;
			}
			elseif($token_data == $token)
			{
				$tokens = array_reverse($tokens);

				return(true);
			}
		}

		$tokens = array_reverse($tokens);

		return(false);
	}
?>