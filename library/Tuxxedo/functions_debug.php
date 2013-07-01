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
	 * @subpackage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Backtrace handler
	 * 
	 * Generates a backtrace with extended information so there is 
	 * less to parse from the regular debug_backtrace() function 
	 * in PHP
	 *
	 * @param	\Exception		If the current trace is combined with an exception, then pass the exception to get a better trace
	 * @return	array			Returns an array with object as keys carrying information about each trace bit
	 */
	function tuxxedo_debug_backtrace(Exception $e = NULL)
	{
		static $includes, $callbacks, $fulltrace, $debug_args;

		if(!$includes)
		{
			$debug_args	= (defined('DEBUG_BACKTRACE_PROVIDE_OBJECT') ? DEBUG_BACKTRACE_PROVIDE_OBJECT : true);

			$includes	= Array('require', 'require_once', 'include', 'include_once');
			$callbacks	= Array('array_map', 'call_user_func', 'call_user_func_array');
		}

		$descriptions	= Array(
					tuxxedo_handler('shutdown')	=> 'Shutdown handler', 
					tuxxedo_handler('exception')	=> 'Exception handler', 
					tuxxedo_handler('error')	=> 'Error handler', 
					tuxxedo_handler('autoload')	=> 'Auto loader'
					);

		$stack 	= Array();
		$bt 	= debug_backtrace($debug_args);

		if($e)
		{
			$bt = array_merge($bt, $e->getTrace());
		}

		$bts 			= sizeof($bt);
		$exception_handler	= tuxxedo_handler('exception');

		foreach($bt as $n => $t)
		{
			if($n < 1)
			{
				continue;
			}

			$is_exception		= false;
			$trace 			= new stdClass;
			$trace->current		= ($n == 3);
			$trace->callargs	= '';
			$trace->notes		= (isset($t['type']) && $t['type'] == '::' ? 'Static call' : '');
			$trace->line		= $trace->file = '';

			if(isset($t['function']))
			{
				$argument_list 	= true;
				$function 	= strtolower($t['function']);
				$is_exception	= ($function === $exception_handler);

				if(isset($t['class']))
				{
					if($t['type'] == '->')
					{
						switch($function)
						{
							case('__construct'):
							{
								$trace->call 	= 'new \\' . $t['class'];
								$trace->notes	= 'Class constructor';
							}
							break;
							case('__destruct'):
							{
								$trace->call 	= '(unset) $' . $t['class'];
								$trace->notes	= 'Class destructor';

								$argument_list	= false;
							}
							break;
							default:
							{
								$trace->call = '$' . $t['class'] . '->' . $t['function'];
							}
						}
					}
					elseif($t['type'] == '::')
					{
						$trace->call = '\\' . $t['class'] . '::' . $t['function'];
					}

					$is_exception = in_array($exception_handler, tuxxedo_debug_object_variants($t));
				}
				elseif(in_array($function, $includes))
				{
					$trace->call		= $t['function'];
					$trace->callargs	= $t['function'] . ' \'' . tuxxedo_trim_path($t['args'][0]) . '\'';
					$trace->notes 		= 'Include';

					$argument_list		= false;
				}
				elseif(strpos($t['function'], '{closure}'))
				{
					$trace->call 	= '$closure';
					$trace->notes	= 'Closure';
				}
				else
				{
					$trace->call = '\\' . $t['function'];
				}

				if($argument_list)
				{
					$trace->callargs 	= $trace->call . '(' . (isset($t['args']) && $t['args'] ? join(', ', array_map('tuxxedo_debug_typedata', $t['args'])) : 'void') . ')';
					$trace->call 		.= '()';
				}
			}
			else
			{
				$trace->call	= 'Main()';
				$trace->notes 	= 'Called from main scope';
			}

			if(isset($t['line']))
			{
				$trace->line = $t['line'];
			}

			if(isset($t['file']))
			{
				$trace->file = $t['file'];
			}

			if(($is_closure = strpos($trace->call, '{closure}')) !== false || !isset($bt[$n + 1]['class']) && isset($bt[$n + 1]['function']) && in_array(strtolower($bt[$n + 1]['function']), $callbacks) || empty($t['file']) && empty($t['line']) && isset($bt[$n + 1]))
			{
				$trace->notes = (!empty($trace->notes) ? $trace->notes . ', ' : '') . (isset($is_closure) && $is_closure !== false ? 'Closure, ' : '') . 'Callback';

				unset($is_closure);
			}

			if(isset($t['function']) && isset($descriptions[$function]))
			{
				$trace->notes = (!empty($trace->notes) ? $trace->notes . ', ' : '') . $descriptions[$function];
			}

			if($trace->file != 'Unknown')
			{
				$trace->file = tuxxedo_trim_path($trace->file);
			}

			if($is_exception)
			{
				$etrace 		= new stdClass;
				$etrace->call		= 'throw new \\' . get_class($t['args'][0]);
				$etrace->callargs	= $etrace->call . '(' . tuxxedo_debug_typedata($t['args'][0]->getMessage()) . ')';
				$etrace->current	= ($trace->current || $bts === $n + 1);
				$etrace->line		= $t['args'][0]->getLine();
				$etrace->file		= tuxxedo_trim_path($t['args'][0]->getFile());
				$etrace->notes		= 'Exception';

				$trace->current		= false;
				$trace->notes 		= (!empty($trace->notes) ? $trace->notes : '') . (isset($t['function']) && !isset($descriptions[$function]) ? (!empty($trace->notes) ? ', ' : '') . 'Exception handler' : '');
			}

			$stack[] = $trace;

			if($is_exception)
			{
				$stack[] 	= $etrace;
				$is_exception	= false;
			}
		}

		return($stack);
	}

	/**
	 * Dumps type data for argument call lists within the debug backtraces
	 *
	 * @param	mixed			Any type of variable to dump
	 * @return	string			Returns a formatted string with the variable data
	 */
	function tuxxedo_debug_typedata($variable)
	{
		switch(gettype($variable))
		{
			case('object'):
			{
				if($variable instanceof \Exception)
				{
					return('Exception(\\' . get_class($variable) . ')');
				}
				elseif($variable instanceof \Closure)
				{
					return('$closure');
				}

				return('Object(\\' . get_class($variable) . ')');
			}
			case('array'):
			{
				return('Array(' . sizeof($variable) . ')');
			}
			default:
			{
				ob_start();
				var_dump($variable);

				return(rtrim(ob_get_clean()));
			}
		}
	}

	/**
	 * Generates a list of method variants for comparison
	 *
	 * @param	array			The trace array to use
	 * @return	array			Returns an array with possible method variants
	 */
	function tuxxedo_debug_object_variants(Array $trace)
	{
		$variants = Array(
					$trace['class'] . '::' . $trace['function'], 
					'\\' . $trace['class'] . '::' . $trace['function'], 
					Array($trace['class'], $trace['function']), 
					Array('\\' . $trace['class'], $trace['function'])
					);

		if(isset($trace['object']))
		{
			$variants[] = Array($trace['object'], $trace['function']);
		}

		return($variants);
	}
?>