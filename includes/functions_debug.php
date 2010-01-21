<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


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
			$callbacks	= Array('call_user_func', 'call_user_func_array', 'call_user_method', 'call_user_method_array');
		}

		$stack 	= Array();
		$skip	= ($e ? 3 : 2);
		$bt 	= debug_backtrace();

		if($e)
		{
			$bt = array_merge($bt, $e->getTrace());
		}

		foreach($bt as $n => $t)
		{
			if($n < $skip)
			{
				continue;
			}

			$trace = new stdClass;

			$trace->current		= ($n == $skip);
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
								$trace->notes	= 'Class construction';
							}
							break;
							case('__destruct'):
							{
								$trace->call 	= '(unset) $' . $t['class'];
								$trace->notes	= 'Class destruction';

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
					$trace->callargs 	= $trace->call . '(' . (isset($t['args']) && sizeof($t['args']) ? join(', ', array_map('gettype', $t['args'])) : '') . ')';
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

			if($n > $skip && !isset($bt[$n + 1]['class']) && isset($bt[$n + 1]['function']) && in_array(strtolower($bt[$n + 1]['function']), $callbacks))
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
?>