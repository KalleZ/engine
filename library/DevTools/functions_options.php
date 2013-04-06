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
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


	/**
	 * Generates the markup for the available option categories
	 *
	 * @param	string			The current option category, if any
	 * @return	string			Returns the markup code for the option categories
	 */
	function options_categories_dropdown($current = NULL)
	{
		$dropdown 	= '';
		$registry	= Registry::init();
		$categories	= $registry->datastore->optioncategories;

		sort($categories, SORT_REGULAR);

		foreach($categories as $name)
		{
			$value 		= $name;
			$selected	= ($name == $current);

			eval('$dropdown .= "' . $registry->style->fetch('option') . '";');
		}

		return($dropdown);
	}

	/**
	 * Categorizes the a list of options
	 *
	 * @param	array			The options to categorize, uses the key as option name, and value as an array where an index named 'category' must exists
	 * @return	array			Returns the categorized array
	 */
	function options_categorize(Array $options)
	{
		$optcategories 	= Registry::init()->datastore->optioncategories;
		$categories	= array_fill_keys($optcategories, Array());

		foreach($options as $name => $data)
		{
			if(in_array($data['category'], $optcategories))
			{
				if(!isset($categories[$data['category']]))
				{
					continue;
				}

				$categories[$data['category']][$name] = $data;
			}
		}

		ksort($categories, SORT_REGULAR);

		return($categories);
	}

	/**
	 * Returns a var_dump() a-like syntax for an option
	 * and its datatype
	 *
	 * @param	string			The option datatype
	 * @param	string			The option value
	 * @param	boolean			Whether to escape HTML characters for string values
	 * @return	string			Returns a string containing the datatype and its value and 'unknown' on error
	 */
	function var_dump_option($type, $value, $htmlize = true)
	{
		if(empty($type))
		{
			return('unknown');
		}

		switch(strtolower($type{0}))
		{
			case('s'):
			{
				if(is_array($value))
				{
					$value = $value['value'];
				}

				return('string(' . strlen($value) . ') "' . ($htmlize ? htmlspecialchars($value) : $value) . '"');
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
?>