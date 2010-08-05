<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Converts an option short hand name into 
	 * its full name
	 *
	 * @param	string			The option datatype
	 * @param	string			Returns the expanded datatype name or 'unknown' on error
	 */
	function options_long_type($short)
	{
		switch(strtolower($short{0}))
		{
			case('s'):
			{
				return('string');
			}
			case('i'):
			{
				return('integer');
			}
			case('b'):
			{
				return('boolean');
			}
		}

		return('unknown');
	}

	/**
	 * Returns a var_dump() a-like syntax for an option
	 * and its datatype
	 *
	 * @param	string			The option datatype
	 * @param	string			The option value
	 * @return	string			Returns a string containing the datatype and its value and 'unknown' on error
	 */
	function options_value_dump($type, $value)
	{
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
?>