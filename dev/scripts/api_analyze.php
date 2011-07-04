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
	 * Fetches all analyzable files into one huge array
	 *
	 * @param	string				The path to analyze from
	 * @return	array				Returns an array with all the files that can be analyzed from the root directory
	 */
	function analyze($path)
	{
		$files = $extra = Array();

		$iterator = new DirectoryIterator($path);

		foreach($iterator as $entry)
		{
			if($entry->isDot())
			{
				continue;
			}

			if($entry->isDir())
			{
				$extra = array_merge($extra, analyze($entry->getPathName()));
			}
			elseif(strtolower(pathinfo($path = $entry->getPathName(), PATHINFO_EXTENSION)) == 'php')
			{
				$files[] = realpath($path);
			}
		}

		$files = array_merge($files, $extra);

		return($files);
	}

	/**
	 * Dumps metadata for printf arguments
	 *
	 * @param	array				The meta data to dump
	 * @return	string				Returns a string for printf
	 */
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

	/**
	 * Resolves a class object to its full namespaced path
	 *
	 * @param	string				The root namespace, from the namespace declaration in the top of each file
	 * @param	array				Array of aliases to use for the class object when attempting to resolve
	 * @param	string				The class object
	 * @return	string				Returns the resolved namespaced path to the class object
	 */
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

			if(isset($root_ns{strlen($root_ns) - 1}) && $root_ns{strlen($root_ns) - 1} != '\\')
			{
				return($root_ns . '\\' . $object);
			}

			return($root_ns . $object);
		}

		return($object);
	}

	/**
	 * Finds the next lexical token index
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The token start index
	 * @param	integer|string			The token to find
	 * @return	integer				Returns the token index found token, and false on failure
	 */
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

	/**
	 * Scans from the current pointer until the first match token match
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The token start index
	 * @param	integer|string			The token to find
	 * @return	string				Returns the token content if found, otherwise false
	 */
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


	/**
	 * Scans and concates the matched tokens into a string
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The token start index
	 * @param	integer|string			The stop token, when this is hit the scanner returns
	 * @param	boolean				Whether to skip whitespace tokens or not
	 * @return	string				Returns the concated string with the tokens between the current pointer and the stop token
	 */
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

	/**
	 * Scans a statement and breaks it into an array based on a separator token
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The token start index
	 * @param	integer|string			The separator token
	 * @param	integer|string			The stop token, end of statement
	 * @param	boolean				Whether to skip whitespace tokens or not
	 * @return	array				Returns an array with each part as a new value, like explode() and empty array on failure
	 */
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

	/**
	 * Lexical scan extend and implements tokens to find their childs
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The token start index
	 * @param	integer|string			The start token
	 * @param	array				Stop tokens, if any of the tokens in this array is hit, the scanner will return
	 * @return	array				Returns an array with the matched child parts
	 */
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


	/**
	 * Access modifier constant - Public
	 *
	 * @var		integer
	 */
	const ACC_PUBLIC	= 1;

	/**
	 * Access modifier constant - Protected
	 *
	 * @var		integer
	 */
	const ACC_PROTECTED	= 2;

	/**
	 * Access modifier constant - Private
	 *
	 * @var		integer
	 */
	const ACC_PRIVATE	= 4;

	/**
	 * Access modifier constant - Abstract
	 *
	 * @var		integer
	 */
	const ACC_ABSTRACT	= 8;

	/**
	 * Access modifier constant - Final
	 *
	 * @var		integer
	 */
	const ACC_FINAL		= 16;

	/**
	 * Access modifier constant - Static
	 *
	 * @var		integer
	 */
	const ACC_STATIC	= 32;

	/**
	 * Access modifier constant - Docblock
	 *
	 * @var		integer
	 */
	const ACC_DOCBLOCK	= 64;


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');


	$engine_path			= realpath(__DIR__ . '/../../');
	$files 				= analyze($engine_path);
	$datamap			= Array();

	$statistics			= new stdClass;
	$statistics->no_docblock	= 0;
	$statistics->no_docblock_list	= Array();
	$statistics->elements		= 0;

	IO::headline('Lexical analyze of the Tuxxedo Engine API', 1);

	foreach($files as $real_file)
	{
		$file = substr(str_replace($engine_path, '', $real_file), 1);

		if(strpos($file, '\\') !== false)
		{
			$file = str_replace('\\', '/', $file);
		}

		if(IO::$depth)
		{
			IO::ul(IO::TAG_END);
		}

		IO::headline('/' . $file, 3);

		$context 		= new stdClass;
		$context->current 	= false;
		$context->modifiers	= 0;
		$context->depth_check	= false;
		$context->depth		= 0;
		$context->docblocks	= 0;

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
			$token = (is_array($token) ? $token : Array($token));

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

					$datamap[$file]['namespaces'][$name] 	= Array(
											'metadata'	=> Array(
															'docblock' => (boolean) ($context->modifiers & ACC_DOCBLOCK)
															)
											);

					if(!($context->modifiers & ACC_DOCBLOCK))
					{
						$statistics->no_docblock_list[] = Array(
											'file'	=> $file, 
											'name'	=> $name
											);

						++$statistics->no_docblock;
					}

					$context->modifiers			= 0;
					++$statistics->elements;

					IO::text(sprintf('NAMESPACE (%s) %s', $name, dump_metadata($datamap[$file]['namespaces'][$name]['metadata'])));
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

					++$statistics->elements;

					IO::text(sprintf('ALIAS (%s)%s', $alias[0], (isset($alias[1]) ? ' AS (' . $alias[1] . ')' : '')));
				}
				break;
				case(T_INTERFACE):
				case(T_CLASS):
				{
					if(($name = lexical_scan($tokens_copy, $index, T_STRING)) == false)
					{
						continue;
					}

					if(IO::$depth)
					{
						IO::ul(IO::TAG_END);
					}

					end($datamap[$file]['namespaces']);

					$type					= ($token[0] == T_CLASS ? 'class' : 'interface');
					$type_multiple				= ($token[0] == T_CLASS ? 'classes' : 'interfaces');
					$name 					= resolve_namespace_alias(key($datamap[$file]['namespaces']), $datamap[$file]['aliases'], $name);

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
											'namespace'	=> key($datamap[$file]['namespaces']), 
											'extends'	=> $extends, 
											'implements'	=> lexical_scan_extends_implements($tokens_copy, $index, T_IMPLEMENTS),  
											'metadata'	=> Array(
															'final'		=> (boolean) ($context->modifiers & ACC_FINAL), 
															'abstract'	=> (boolean) ($context->modifiers & ACC_ABSTRACT), 
															'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
															)
											);

					if(!($context->modifiers & ACC_DOCBLOCK))
					{
						$statistics->no_docblock_list[] = Array(
											'file'	=> $file, 
											'name'	=> $name
											);

						++$statistics->no_docblock;
					}

					$context->depth_check			= 1;
					$context->modifiers			= 0;

					++$statistics->elements;

					IO::text(sprintf('%s (%s) %s', strtoupper($type), $name, dump_metadata($datamap[$file][$type_multiple][$name]['metadata'])));
					IO::ul();

					if($extends)
					{
						IO::li(sprintf('EXTENDS (%s)', resolve_namespace_alias($datamap[$file][$type_multiple][$name]['namespace'], $datamap[$file]['aliases'], $extends)));
					}

					if($datamap[$file][$type_multiple][$name]['implements'])
					{
						foreach($datamap[$file][$type_multiple][$name]['implements'] as $interface)
						{
							IO::li(sprintf('IMPLEMENTS (%s)', resolve_namespace_alias($datamap[$file][$type_multiple][$name]['namespace'], $datamap[$file]['aliases'], $interface)));
						}
					}
				}
				break;
				case(T_FUNCTION):
				{
					if(($function = lexical_scan_separator($tokens_copy, $index, T_STRING, '(')) == false || !$function)
					{
						continue;
					}

					$function = $function[0];

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
																						'static'	=> (boolean) ($context->modifiers & ACC_STATIC), 
																						'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
																						)
																		);

						if(!($context->modifiers & ACC_DOCBLOCK))
						{
							$statistics->no_docblock_list[] = Array(
												'file'	=> $file, 
												'name'	=> $context->{$context->type} . '::' . $function . '()'
												);

							++$statistics->no_docblock;
						}

						$context->depth_check									= (!($context->modifiers & ACC_ABSTRACT) ? 1 : false);
						$context->modifiers									= 0;
						$metadata 										= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['methods']);

						++$statistics->elements;

						IO::li(sprintf('METHOD (%s) %s', $function, dump_metadata($metadata['metadata'])));

						unset($metadata);
					}
					else
					{
						$datamap[$file]['functions'][] = Array(
											'function'	=> $function, 
											'namespace'	=> end($datamap[$file]['namespaces']), 
											'metadata'	=> Array(
															'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
															)
											);

						if(!($context->modifiers & ACC_DOCBLOCK))
						{
							$statistics->no_docblock_list[] = Array(
												'file'	=> $file, 
												'name'	=> $function
												);

							++$statistics->no_docblock;
						}

						$context->modifiers		= 0;
						$metadata			= end($datamap[$file]['functions']);
						++$statistics->elements;

						IO::text(sprintf('FUNCTION (%s) %s', $function, dump_metadata($metadata['metadata'])));
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

						$const 					= substr($const, 1, strlen($const) - 2);
						$datamap[$file]['constants'][$const]	= Array(
												'metadata'	=> Array(
																'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
																)
												);

						if(!($context->modifiers & ACC_DOCBLOCK))
						{
							$statistics->no_docblock_list[] = Array(
												'file'	=> $file, 
												'name'	=> '\\' . $const
												);

							++$statistics->no_docblock;
						}

						$context->modifiers			= 0;
						++$statistics->elements;

						IO::text(sprintf('GLOBAL CONSTANT (%s) %s', $const, dump_metadata($datamap[$file]['constants'][$const]['metadata'])));
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
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['constants'][] 	= Array(
																		'constant'	=> $const, 
																		'namespace'	=> end($datamap[$file]['namespaces']), 
																		'metadata'	=> Array(
																						'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
																						)
																		);

						if(!($context->modifiers & ACC_DOCBLOCK))
						{
							$statistics->no_docblock_list[] = Array(
												'file'	=> $file, 
												'name'	=> $context->{$context->type} . '::' . $const
												);
							++$statistics->no_docblock;
						}

						$context->modifiers									= 0;
						$metadata										= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['constants']);

						++$statistics->elements;

						IO::li(sprintf('CONSTANT (%s) %s', $const, dump_metadata($metadata['metadata'])));
					}
					else
					{
						$datamap[$file]['constants'][$const] 	= Array(
												'metadata'	=> Array(
																'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
																)
												);

						if(!($context->modifiers & ACC_DOCBLOCK))
						{
							$statistics->no_docblock_list[] = Array(
												'file'	=> $file, 
												'name'	=> '\\' . $const
												);

							++$statistics->no_docblock;
						}

						$context->modifiers			= 0;
						++$statistics->elements;

						IO::text(sprintf('GLOBAL CONSTANT (%s) %s', $const, dump_metadata($datamap[$file]['constants'][$const]['metadata'])));
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
																					'static'	=> (boolean) ($context->modifiers & ACC_STATIC), 
																					'docblock'	=> (boolean) ($context->modifiers & ACC_DOCBLOCK)
																					)
																	);

					if(!($context->modifiers & ACC_DOCBLOCK))
					{
						$statistics->no_docblock_list[] = Array(
											'file'	=> $file, 
											'name'	=> $context->{$context->type} . '::$' . $property
											);

						++$statistics->no_docblock;
					}

					$context->modifiers									= 0;
					$metadata 										= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['properties']);
					++$statistics->elements;

					IO::li(sprintf('PROPERTY (%s) %s', $property, dump_metadata($metadata['metadata'])));
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
				case(T_DOC_COMMENT):
				{
					if(++$context->docblocks >= 2)
					{
						$context->modifiers |= ACC_DOCBLOCK;
					}
				}
				break;
				case(T_STATIC):
				{
					if(!$context->depth_check)
					{
						$context->modifiers |= ACC_STATIC;
					}
				}
				break;
				case('{'):
				{
					if($context->depth_check && $context->depth_check !== 1)
					{
						++$context->depth_check;
					}
				}
				break;
				case('}'):
				{
					if($context->depth_check && --$context->depth)
					{
						$context->depth_check = false;
					}
				}
				break;
			}
		}
	}

	if(IO::$depth)
	{
		IO::ul(IO::TAG_END);
	}

	file_put_contents(__DIR__ . '/../api/dumps/serialized.dump', serialize($datamap));

	if(extension_loaded('json'))
	{
		file_put_contents(__DIR__ . '/../api/dumps/json.dump', json_encode($datamap));
	}

	IO::headline('Status', 1);
	IO::ul();
	IO::li('Total number of elements: ' . $statistics->elements, IO::STYLE_BOLD);
	IO::li('Elements WITHOUT a docblock comment: ' . $statistics->no_docblock, IO::STYLE_BOLD);

	if($statistics->no_docblock_list)
	{
		IO::ul();

		foreach($statistics->no_docblock_list as $undocumented)
		{
			IO::li('[' . $undocumented['file'] . '] ' . $undocumented['name']);
		}

		IO::ul(IO::TAG_END);
	}

	IO::ul(IO::TAG_END);
?>