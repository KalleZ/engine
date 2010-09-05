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
	 *
	 * =============================================================================
	 */

	/**
	 * Backtrace handler
	 * 
	 * Generates a backtrace with extended information so theres 
	 * less to parse from the regular debug_backtrace() function 
	 * in PHP
	 *
	 * @param	Exception		If the current trace is combined with an exception, then pass the exception to get a better trace
	 * @return	array			Returns an array with object as keys carrying information about each trace bit
	 */
	function tuxxedo_debug_backtrace(Exception $e = NULL)
	{
		static $includes, $callbacks;

		if(!$includes)
		{
			$includes	= Array('require', 'require_once', 'include', 'include_once');
			$callbacks	= Array('array_map', 'call_user_func', 'call_user_func_array', 'call_user_method', 'call_user_method_array');
		}

		$stack 	= Array();
		$bt 	= debug_backtrace();

		if($e)
		{
			$bt = array_merge($bt, $e->getTrace());
		}

		$bts = sizeof($bt);

		foreach($bt as $n => $t)
		{
			if($n < 3)
			{
				continue;
			}

			$trace = new stdClass;

			$trace->current		= ($n == 3);
			$trace->callargs	= '';
			$trace->notes		= (isset($t['type']) && $t['type'] == '::' ? 'Static call' : '');
			$trace->line		= $trace->file = '';

			if(isset($t['function']))
			{
				$argument_list = true;

				if(isset($t['class']))
				{
					if($t['type'] == '->')
					{
						switch(strtolower($t['function']))
						{
							case('__construct'):
							{
								$trace->call 	= 'new ' . $t['class'];
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
						$trace->call = $t['class'] . '::' . $t['function'];
					}
				}
				elseif(in_array(strtolower($t['function']), $includes))
				{
					$trace->call		= $t['function'];
					$trace->callargs	= $t['function'] . ' \'' . tuxxedo_trim_path($t['args'][0]) . '\'';
					$trace->notes 		= 'Include';

					$argument_list		= false;
				}
				else
				{
					$trace->call = $t['function'];
				}

				if($argument_list)
				{
					$trace->callargs 	= $trace->call . '(' . (isset($t['args']) && sizeof($t['args']) ? join(', ', array_map('tuxxedo_debug_typedata', $t['args'])) : '') . ')';
					$trace->call 		.= '()';
				}
			}
			else
			{
				$trace->call	= 'Main()';
				$trace->notes 	= 'Called from main scope';
			}

			if($bts == 4)
			{
				if(empty($trace->call))
				{
					$trace->call = 'Main()';
				}

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

			if(!isset($bt[$n + 1]['class']) && isset($bt[$n + 1]['function']) && in_array(strtolower($bt[$n + 1]['function']), $callbacks))
			{
				$trace->notes = (!empty($trace->notes) ? $trace->notes . ', ' : '') . 'Callback';
			}

			if($trace->file !== 'Unknown')
			{
				$trace->file = tuxxedo_trim_path($trace->file);
			}

			$stack[] = $trace;
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
				return('(' . get_class($variable) . ')');
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
?>