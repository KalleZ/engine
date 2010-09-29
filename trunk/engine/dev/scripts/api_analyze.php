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


	$engine_path		= realpath(__DIR__ . '/../../');
	$files 			= analyze(new DirectoryIterator($engine_path));
	$datamap		= Array();

	const ACC_PUBLIC	= 1;
	const ACC_PROTECTED	= 2;
	const ACC_PRIVATE	= 4;
	const ACC_ABSTRACT	= 8;
	const ACC_FINAL		= 16;
	const ACC_STATIC	= 32;

	echo('<h1>Lexical analyze of engine API</h1>');

	foreach($files as $real_file)
	{
		$file = substr(str_replace($engine_path, '', $real_file), 1);

		if(strpos($file, '\\') !== false)
		{
			$file = str_replace('\\', '/', $file);
		}

		printf('<h3>/%s</h3>', $file);

		$context 		= new stdClass;
		$context->current 	= false;
		$context->modifiers	= 0;

		$datamap[$file]		= Array(
						'namespaces'	=> Array(), 
						'aliases'	=> Array(), 
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
				continue;
			}

			switch($token[0])
			{
				case(T_NAMESPACE):
				{
					if(($name = lexical_scan_concat($tokens_copy, $index, ';')) == false)
					{
						continue;
					}

					if($name{0} != '\\')
					{
						$name = '\\' . $name;
					}

					$datamap[$file]['namespaces'][] = $name;

					printf('NAMESPACE (%s)<br />', $name);
				}
				break;
				case(T_USE):
				{
					if(($alias = lexical_scan_separator($tokens_copy, $index, T_AS, ';')) == false)
					{
						continue;
					}

					if($alias[0]{0} != '\\')
					{
						$alias[0] = '\\' . $alias[0];
					}

					$datamap[$file]['aliases'] = array_merge($datamap[$file]['aliases'], $alias);

					printf('ALIAS (%s%s)<br />', $alias[0], (isset($alias[1]) ? ' AS ' . $alias[1] : ''));
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
					$name 					= resolve_namespace_alias(end($datamap[$file]['namespaces']), $datamap[$file]['aliases'], $name);

					$context->current 			= $token[0];
					$context->type				= $type;
					$context->type_multiple			= $type_multiple;
					$context->{$type} 			= $name;
					$extends				= lexical_scan_extends_implements($tokens_copy, $index, T_EXTENDS, Array(T_IMPLEMENTS, '{'));
					$extends 				= ($extends ? $extends[0] : '');

					$datamap[$file][$type_multiple][$name]	= Array(
											'constants'	=> Array(), 
											'properties'	=> Array(), 
											'methods'	=> Array(), 
											'namespace'	=> end($datamap[$file]['namespaces']), 
											'extends'	=> $extends, 
											'implements'	=> lexical_scan_extends_implements($tokens_copy, $index, T_IMPLEMENTS),  
											'metadata'	=> Array(
															'final'		=> (boolean) ($context->modifiers & ACC_FINAL), 
															'abstract'	=> (boolean) ($context->modifiers & ACC_ABSTRACT)
															)
											);

					$context->modifiers			= 0;

					printf('%s (%s) %s<br />', strtoupper($type), $name, dump_metadata($datamap[$file][$type_multiple][$name]['metadata']));

					if($extends)
					{
						printf('- EXTENDS (%s)<br />', resolve_namespace_alias($datamap[$file][$type_multiple][$name]['namespace'], $datamap[$file]['aliases'], $extends));
					}

					if($datamap[$file][$type_multiple][$name]['implements'])
					{
						foreach($datamap[$file][$type_multiple][$name]['implements'] as $interface)
						{
							printf('- IMPLEMENTS (%s)<br />', resolve_namespace_alias($datamap[$file][$type_multiple][$name]['namespace'], $datamap[$file]['aliases'], $interface));
						}
					}
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
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['methods'][] 	= Array(
																		'method'	=> $function, 
																		'metadata'	=> Array(
																						'final'		=> (boolean) ($context->modifiers & ACC_FINAL), 
																						'abstract'	=> (boolean) ($context->modifiers & ACC_ABSTRACT), 
																						'public'	=> (boolean) ($context->modifiers & ACC_PUBLIC), 
																						'protected'	=> (boolean) ($context->modifiers & ACC_PROTECTED), 
																						'private'	=> (boolean) ($context->modifiers & ACC_PRIVATE), 
																						'static'	=> (boolean) ($context->modifiers & ACC_STATIC)
																						)
																		);

						$context->modifiers									= 0;
						$metadata 										= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['methods']);

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
					if($context->current === false || $datamap[$file][$context->type_multiple][$context->{$context->type}]['methods'])
					{
						continue;
					}

					$property 										= substr($token[1], 1);
					$datamap[$file][$context->type_multiple][$context->{$context->type}]['properties'][]	= Array(
																	'property'	=> $property, 
																	'metadata'	=> Array(
																					'final'		=> (boolean) ($context->modifiers & ACC_FINAL), 
																					'abstract'	=> (boolean) ($context->modifiers & ACC_ABSTRACT), 
																					'public'	=> (boolean) ($context->modifiers & ACC_PUBLIC), 
																					'protected'	=> (boolean) ($context->modifiers & ACC_PROTECTED), 
																					'private'	=> (boolean) ($context->modifiers & ACC_PRIVATE), 
																					'static'	=> (boolean) ($context->modifiers & ACC_STATIC)
																					)
																	);

					$context->modifiers									= 0;
					$metadata 										= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['properties']);

					printf('- PROPERTY (%s) %s<br />', $property, dump_metadata($metadata['metadata']));
				}
				break;
				case(T_PUBLIC):
				{
					$context->modifiers |= ACC_PUBLIC;
				}
				break;
				case(T_PROTECTED):
				{
					$context->modifiers |= ACC_PROTECTED;
				}
				break;
				case(T_PRIVATE):
				{
					$context->modifiers |= ACC_PRIVATE;
				}
				break;
				case(T_ABSTRACT):
				{
					$context->modifiers |= ACC_ABSTRACT;
				}
				break;
				case(T_FINAL):
				{
					$context->modifiers |= ACC_FINAL;
				}
				break;
				case(T_STATIC):
				{
					$context->modifiers |= ACC_STATIC;
				}
				break;
			}
		}

		foreach(array_keys($datamap[$file]) as $element)
		{
			if(sizeof($datamap[$file][$element]))
			{
				$note = true;

				break;
			}
		}

		if(isset($note))
		{
			print('<em>No functional code found within this file</em>');

			unset($note);
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

	function resolve_namespace_alias($root_ns, Array $aliases, $object)
	{
		if($aliases && $object{0} != '\\')
		{
			$ns = $object;

			if(($pos = strrpos($object, '\\')) !== false)
			{
				$ns = substr($object, 0, $pos);
			}

			foreach($aliases as $alias)
			{
				if(($pos = strrpos($alias, $ns)) !== false)
				{
					return(substr_replace($alias, $ns, $pos));
				}
			}

			if($root_ns{strlen($root_ns) - 1} != '\\')
			{
				return($root_ns . '\\' . $object);
			}

			return($root_ns . $object);
		}

		return($object);
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
				return($start_index + $inc);
			}
		}

		return(false);
	}

	function lexical_scan(Array $tokens, $start_index, $token)
	{
		$inc			= 0;
		$searching_for_token 	= ((string)(integer) $token !== $token);

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
		$searching_for_token 	= ((string)(integer) $token !== $token);

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

	function lexical_scan_separator(Array $tokens, $start_index, $separator, $token, $skip_whitespace = true)
	{
		$buffer			= '';
		$scanned 		= Array();
		$inc			= 0;
		$searching_for_token 	= ((string)(integer) $token !== $token);

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
			elseif($token_array && $token_data[0] == $separator && !empty($buffer))
			{
				$scanned[] 	= $buffer;
				$buffer		= '';

				continue;
			}

			$buffer .= (isset($token_data[1]) ? $token_data[1] : $token_data);
		}

		if(!empty($buffer))
		{
			$scanned[] = $buffer;
		}

		return($scanned);
	}

	function lexical_scan_extends_implements(Array $tokens, $start_index, $start_token, Array $stop_tokens = Array('{'))
	{
		$inc 			= 0;
		$buffer			= '';
		$matched_tokens		= Array();
		$start_index		= lexical_next_index($tokens, $start_index, $start_token);

		if($start_index === false)
		{
			return(Array());
		}

		while(isset($tokens[$start_index + $inc++]))
		{
			$token 		= (is_array($tokens[$start_index + $inc - 1]) ? $tokens[$start_index + $inc - 1][0] : $tokens[$start_index + $inc - 1]);
			$token_data	= (is_array($tokens[$start_index + $inc - 1]) ? $tokens[$start_index + $inc - 1][1] : $token);

			if(in_array($token, $stop_tokens))
			{
				break;
			}
			elseif($token === T_WHITESPACE)
			{
				continue;
			}
			elseif($token == ',' && !empty($buffer))
			{
				$matched_tokens[] 	= $buffer;
				$buffer			= '';
			}
			elseif($token == T_STRING || $token == T_NS_SEPARATOR)
			{
				$buffer .= $token_data;
			}
		}

		if(!empty($buffer))
		{
			$matched_tokens[] = $buffer;
		}

		return($matched_tokens);
	}
?>