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

		return((empty($dump) ? '' : '[meta=' . rtrim($dump, ', ')) . ']');
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
		if($object{0} == '\\')
		{
			return($object);
		}

		if($aliases)
		{
			$object_clone = $object;

			if(($pos = strpos($object, '\\')) !== false)
			{
				$object_clone = substr($object, 0, $pos);
			}

			$object_clone = ucfirst(strtolower($object_clone));

			foreach(array_keys($aliases) as $alias)
			{
				if(!$alias)
				{
					continue;
				}

				$pos = strrpos($alias, '\\');

				if(substr($alias, $pos + 1) == $object_clone)
				{
					return(substr_replace($alias, '', $pos + 1) . $object);
				}
			}

			if($root_ns{strlen($root_ns) - 1} != '\\')
			{
				return($root_ns . '\\' . $object);
			}

			return($root_ns . $object);
		}
		elseif($root_ns)
		{
			return($root_ns . '\\' . $object);
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
	 * Lexical scan extends and implements tokens to find their children
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
	 * Lexical docblock scanner
	 *
	 * @param	array				The tokens copy array
	 * @param	integer				The index of the docblock ($context->last_docblock)
	 * @param	\stdClass			The Work-In-Progress instance
	 * @return	array				Returns an array with structured docblock information on success and false on error
	 */
	function lexical_docblock(Array $tokens, $index, \stdClass $wip)
	{
		if(!isset($tokens[$index]) || !is_array($tokens[$index]) || $tokens[$index][0] != T_DOC_COMMENT)
		{
			return(false);
		}

		return(lexical_docblock_parse($tokens[$index][1], $wip));
	}

	/**
	 * Lexical docblock parser
	 * 
	 * @param	string				The docblock tag value to parse
	 * @param	\stdClass			The Work-In-Progress instance
	 * @return	array				Returns a structured array with the docblock variables and false on error
	 */
	function lexical_docblock_parse($dump, \stdClass $wip)
	{
		static $special_tags, $multi_tags, $flag_tags;

		if(!$special_tags)
		{
			$special_tags 	= Array(
						'copyright'	=> 0, 
						'license'	=> 1, 
						'author'	=> 0, 
						'param'		=> -1, 
						'return'	=> -1, 
						'throws'	=> -1
						);

			$multi_tags	= Array(
						'param'		=> 0, 
						'throws'	=> 0
						);

			$flag_tags	= Array(
						'ignore'	=> 0, 
						'abstract'	=> 0, 
						'private'	=> 0, 
						'public'	=> 0, 
						'protected'	=> 0, 
						'final'		=> 0, 
						'static'	=> 0
						);
		}

		$docblock 	= Array(
					'description'	=> '', 
					'tags'		=> Array()
					);

		$incode		= false;
		$dump 		= explode("\n", str_replace("\n\n", "\n", str_replace("\r", "\n", $dump)));
		$lines		= sizeof($dump) - 1;

		foreach($dump as $n => $line)
		{
			if(!$n)
			{
				$line = substr($line, -2);
			}
			elseif($n === $lines)
			{
				$line = substr($line, 0, -2);
			}

			$line = trim($line);

			if(isset($line{0}))
			{
				if($line{0} == '*')
				{
					$line = ltrim(substr($line, 1));
				}
				elseif($line{0} == '<')
				{
					$incode = !$incode;
				}
			}

			if($incode || empty($line) || !preg_match('#[a-zA-Z@]#Ui', $line{0}) && $line{0} !== '<')
			{
				if($incode || empty($line) && !empty($docblock['description']) && !sizeof($docblock['tags']))
				{
					$docblock['description'] .= PHP_EOL;
				}

				continue;
			}

			if($line{0} == '@')
			{
				$next		= true;
				$current	= -1;
				$split		= Array();
				$line 		= str_split(substr($line, 1));

				for($x = 0, $size = sizeof($line); $x < $size; ++$x)
				{
					if($line[$x] == ' ' || $line[$x] == "\t")
					{
						$next = true;

						continue;
					}
					elseif($next)
					{
						++$current;

						$next = false;
					}

					if(!isset($split[$current]))
					{
						$split[$current] = '';
					}

					$split[$current] .= $line[$x];
				}

				$tag = strtolower($split[0]);

				if(!isset($split[1]))
				{
					if(isset($flag_tags[$tag]))
					{
						$docblock['tags'][$tag] = true;
					}

					continue;
				}

				unset($split[0]);

				if(!isset($docblock['tags'][$tag]))
				{
					$docblock['tags'][$tag] = Array();
				}

				if(isset($special_tags[$tag]))
				{
					if(($times = $special_tags[$tag]) !== 0 && $times > 0)
					{
						$current = 0;
						$shifted = Array();

						do
						{
							$shifted[] = $split[$current + 1];

							unset($split[$current + 1]);

							--$times;
						}
						while($times);
					}

					if($times < 0)
					{
						$temp = $split[1];

						unset($split[1]);

						$parsed_split 	= Array($temp, implode(' ', $split));
						$split[1]	= $temp;
					}
					else
					{
						$parsed_split = Array(implode(' ', $split));
					}

					if(isset($shifted))
					{
						foreach($shifted as $temp)
						{
							array_unshift($parsed_split, $temp);
						}

						unset($shifted);
					}

					if($times >= 0)
					{
						$parsed_split = $parsed_split[0];
					}
				}
				elseif(sizeof($split) === 1)
				{
					$split = $split[1];
				}

				if(isset($multi_tags[$tag]) || (is_array($docblock['tags'][$tag]) && $docblock['tags'][$tag]))
				{
					$docblock['tags'][$tag][] = (isset($parsed_split) ? $parsed_split : $split);
				}
				else
				{
					$docblock['tags'][$tag] = (isset($parsed_split) ? $parsed_split : $split);
				}

				if(isset($parsed_split))
				{
					unset($parsed_split);
				}
			}
			else
			{
				$docblock['description'] .= $line . PHP_EOL;
			}
		}

		if(!empty($docblock['description']))
		{
			$docblock['description'] = rtrim($docblock['description']);
		}
		elseif(empty($docblock['description']) && !sizeof($docblock['tags']))
		{
			return(false);
		}

		if(isset($docblock['tags']['wip']))
		{
			$wip->is_wip = true;
		}

		return($docblock);
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


	if(!extension_loaded('json'))
	{
		IO::error('The JSON extension is required to export the lexical output');
	}


	$engine_path				= realpath(__DIR__ . '/../../');
	$cli					= IO::isCli();
	$files 					= analyze($engine_path);
	$datamap				= Array();

	$wip					= new stdClass;
	$wip->is_wip				= false;
	$wip->elements				= Array();

	$statistics				= new stdClass;
	$statistics->no_docblock		= 0;
	$statistics->no_docblock_list		= Array();
	$statistics->elements			= 0;
	$statistics->counter			= new stdClass;
	$statistics->counter->namespaces	= Array();
	$statistics->counter->constants		= $statistics->counter->functions = $statistics->counter->aliases = $statistics->counter->classes = $statistics->counter->interfaces = $statistics->counter->class_constants = $statistics->counter->properties = $statistics->counter->methods = 0;

	IO::signature();
	IO::headline('Lexical API analyze', 1);

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

		if($cli)
		{
			IO::headline(IO::eol() . '/' . $file);
		}
		else
		{
			IO::headline('/' . $file, 3);
		}

		$context 		= new stdClass;
		$context->current 	= false;
		$context->modifiers	= 0;
		$context->depth_check	= false;
		$context->depth		= 0;
		$context->docblocks	= 0;
		$context->last_docblock	= -1;

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
					if(($name = lexical_scan_concat($tokens_copy, $index, ';')) == false && ($name = lexical_scan_concat($tokens_copy, $index, '{')))
					{
						continue;
					}

					if($name{0} != '\\')
					{
						$name = '\\' . $name;
					}

					$datamap[$file]['namespaces'][$name] 	= Array(
											'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
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

					$context->modifiers 			= 0;
					$statistics->counter->namespaces[] 	= $name;

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

					if($alias[0]{0} == '(')
					{
						continue;
					}

					if($alias[0]{0} != '\\')
					{
						$alias[0] = '\\' . $alias[0];
					}

					$datamap[$file]['aliases'] = array_merge($datamap[$file]['aliases'], Array($alias[0] => (isset($alias[1]) ? $alias[1] : '')));

					++$statistics->elements;
					++$statistics->counter->aliases;

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

					$name 					= resolve_namespace_alias(key($datamap[$file]['namespaces']), $datamap[$file]['aliases'], $name);

					$context->current 			= $token[0];
					$context->type				= $type = ($token[0] == T_CLASS ? 'class' : 'interface');
					$context->type_multiple			= $type_multiple = ($token[0] == T_CLASS ? 'classes' : 'interfaces');
					$context->{$type} 			= $name;

					$extends				= lexical_scan_extends_implements($tokens_copy, $index, T_EXTENDS, Array(T_IMPLEMENTS, '{'));
					$extends 				= ($extends ? resolve_namespace_alias(key($datamap[$file]['namespaces']), $datamap[$file]['aliases'], $extends[0]) : '');
					$implements				= lexical_scan_extends_implements($tokens_copy, $index, T_IMPLEMENTS);

					if($implements)
					{
						foreach($implements as $index => $iface)
						{
							$implements[$index] = resolve_namespace_alias(key($datamap[$file]['namespaces']), $datamap[$file]['aliases'], $iface);
						}
					}

					$datamap[$file][$type_multiple][$name]	= Array(
											'constants'	=> Array(), 
											'properties'	=> Array(), 
											'methods'	=> Array(), 
											'namespace'	=> (($ns = key($datamap[$file]['namespaces'])) ? $ns : ''), 
											'extends'	=> $extends, 
											'implements'	=> $implements,  
											'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
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

					$context->modifiers = 0;

					++$statistics->elements;
					++$statistics->counter->{$type_multiple};

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
						$name											= $context->{$context->type} . '::' . $function . '()';
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['methods'][] 	= Array(
																		'method'	=> $function, 
																		'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
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
												'name'	=> $name
												);

							++$statistics->no_docblock;
						}

						$context->depth_check	= (!($context->modifiers & ACC_ABSTRACT) ? 1 : false);
						$context->modifiers	= $context->depth = 0;
						$metadata 		= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['methods']);

						++$statistics->elements;
						++$statistics->counter->methods;

						IO::li(sprintf('METHOD (%s) %s', $function, dump_metadata($metadata['metadata'])));

						unset($metadata);
					}
					else
					{
						$name				= $function . '()';
						$datamap[$file]['functions'][] 	= Array(
											'function'	=> $function, 
											'namespace'	=> (($ns = end($datamap[$file]['namespaces'])) !== false ? $ns : ''), 
											'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
											'metadata'	=> Array(
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

						$context->modifiers	= 0;
						$metadata		= end($datamap[$file]['functions']);

						++$statistics->elements;
						++$statistics->counter->functions;

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
						$name					= '\\' . $const;
						$datamap[$file]['constants'][$const]	= Array(
												'namespace'	=> (($ns = end($datamap[$file]['namespaces'])) !== false ? $ns : ''), 
												'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
												'metadata'	=> Array(
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

						$context->modifiers = 0;

						++$statistics->elements;
						++$statistics->counter->constants;

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
						$name											= $context->{$context->type} . '::' . $const;
						$datamap[$file][$context->type_multiple][$context->{$context->type}]['constants'][] 	= Array(
																		'constant'	=> $const, 
																		'namespace'	=> (($ns = end($datamap[$file]['namespaces'])) !== false ? $ns : ''), 
																		'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
																		'metadata'	=> Array(
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

						$context->modifiers	= 0;
						$metadata		= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['constants']);

						++$statistics->elements;
						++$statistics->counter->class_constants;

						IO::li(sprintf('CONSTANT (%s) %s', $const, dump_metadata($metadata['metadata'])));
					}
					else
					{
						$name					= '\\' . $const;
						$datamap[$file]['constants'][$const] 	= Array(
												'namespace'	=> (($ns = end($datamap[$file]['namespaces'])) !== false ? $ns : ''), 
												'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
												'metadata'	=> Array(
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

						$context->modifiers = 0;

						++$statistics->elements;
						++$statistics->counter->constants;

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
					$name											= $context->{$context->type} . '::$' . $property;
					$datamap[$file][$context->type_multiple][$context->{$context->type}]['properties'][]	= Array(
																	'property'	=> $property, 
																	'docblock'	=> lexical_docblock($tokens_copy, $context->last_docblock, $wip), 
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
											'name'	=> $name
											);

						++$statistics->no_docblock;
					}

					$context->modifiers	= 0;
					$metadata 		= end($datamap[$file][$context->type_multiple][$context->{$context->type}]['properties']);

					++$statistics->elements;
					++$statistics->counter->properties;

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
						$context->last_docblock	= $index;
						$context->modifiers 	|= ACC_DOCBLOCK;
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
					if($context->depth_check)
					{
						++$context->depth;
					}
				}
				break;
				case('}'):
				{
					if($context->depth_check && !--$context->depth)
					{
						$context->depth_check = false;
					}
				}
				break;
			}

			if($wip->is_wip)
			{
				$wip->elements[] 	= $name;
				$wip->is_wip		= false;
			}
		}
	}

	$statistics->counter->namespaces = sizeof(array_unique($statistics->counter->namespaces));

	if(IO::$depth)
	{
		IO::ul(IO::TAG_END);
	}

	ksort($datamap);

	file_put_contents(__DIR__ . '/apidump/engine_api.json', json_encode($datamap));

	IO::headline('Status', 1);
	IO::ul();
	IO::li('Total scanned files: ' . sizeof($files), IO::STYLE_BOLD);
	IO::li('Total number of scanned elements: ' . $statistics->elements, IO::STYLE_BOLD);
	IO::ul();

	foreach(Array('constants', 'functions', 'namespaces', 'aliases', 'classes', 'interfaces', 'class_constants', 'properties', 'methods') as $element)
	{
		IO::li(ucfirst(str_replace('_', ' ', $element)) . ': ' . $statistics->counter->{$element});
	}

	IO::ul(IO::TAG_END);

	if($statistics->no_docblock_list)
	{
		IO::li('Elements WITHOUT a docblock comment: ' . $statistics->no_docblock, IO::STYLE_BOLD);
		IO::ul();

		foreach($statistics->no_docblock_list as $undocumented)
		{
			IO::li('[' . $undocumented['file'] . '] ' . $undocumented['name']);
		}

		IO::ul(IO::TAG_END);
	}

	if($wip->elements)
	{
		IO::li('Elements that is marked work-in-progress: ' . sizeof($wip->elements), IO::STYLE_BOLD);
		IO::ul();

		foreach($wip->elements as $name)
		{
			IO::li($name);
		}

		IO::ul(IO::TAG_END);
	}

	IO::ul(IO::TAG_END);
?>