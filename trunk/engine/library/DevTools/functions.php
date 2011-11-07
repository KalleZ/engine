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
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * A recursive glob function
	 *
	 * @param	string			The glob expression to execute
	 * @return	array			Returns an array containing the matched elements and false on error
	 */
	function recursive_glob($expression)
	{
		static $expression_prefix;

		if(!$expression_prefix)
		{
			$expression_prefix = strlen($expression) + 1;
		}

		$glob = glob($expression . '/*');

		if(!$glob)
		{
			return(false);
		}

		$return_value = Array();

		foreach($glob as $entry)
		{
			if(is_dir($entry))
			{
				if(($entries = recursive_glob($entry)) !== false)
				{
					foreach($entries as $sub_entry)
					{
						$return_value[] = $sub_entry;
					}
				}

				continue;
			}

			$entry = substr_replace($entry, '', 0, $expression_prefix);

			if(strpos($entry, '\\') !== false)
			{
				$entry = str_replace('\\', '/', $entry);
			}

			$return_value[] = $entry;
		}

		return($return_value);
	}


	/**
	 * Returns a var_dump() a-like syntax for an option
	 * and its datatype
	 *
	 * @param	string			The option datatype
	 * @param	string			The option value
	 * @return	string			Returns a string containing the datatype and its value and 'unknown' on error
	 */
	function var_dump_option($type, $value)
	{
		if(empty($type))
		{
			return('unknown');
		}

		switch(strtolower($type{0}))
		{
			case('s'):
			{
				return('string(' . strlen($value) . ') "' . $value . '"');
			}
			case('i'):
			{
				return('integer(' . (integer) $value . ')');
			}
			case('b'):
			{
				return('boolean(' . ($value ? 'true' : 'false') . ')');
			}
		}

		return('unknown');
	}

	/**
	 * Converts a value to an option type
	 *
	 * @param	string			The one character option type: b, i etc.
	 * @param	string			The value to convert
	 * @return	string			Converts the value into the desired type, and string for unknown types
	 */
	function var_typecast_option($type, $value)
	{
		switch($type)
		{
			case('b'):
			{
				return((boolean) $value);
			}
			break;
			case('i'):
			{
				return((integer) $value);
			}
			break;
		}

		return((string) $value);
	}

	/**
	 * Extended exception handler
	 *
	 * @param	\Exception		The exception to handle
	 * @return	void			No value is returned
	 */
	function devtools_exception_handler(\Exception $e)
	{
		if($e instanceof Exception\FormData)
		{
			$list 		= '';
			$style		= Registry::init()->style;
			$message	= $e->getMessage();

			foreach($e->getFields() as $field)
			{
				eval('$list .= "' . $style->fetch('multierror_itembit') . '";');
			}

			eval(page('multierror'));
		}
		else
		{
			tuxxedo_exception_handler($e);
		}
	}
?>