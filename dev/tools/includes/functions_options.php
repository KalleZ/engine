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

	/**
	 * Gets all available options from the database
	 *
	 * @return	array			Returns an array with all the option names as values, and false on error
	 */
	function options_get_all()
	{
		static $options;

		if(!$options)
		{
			global $registry;

			$query = $registry->db->query('
							SELECT 
								*
							FROM 
								`' . TUXXEDO_PREFIX . 'options`');

			if(!$query || !$query->getNumRows())
			{
				return(false);
			}

			while($row = $query->fetchAssoc())
			{
				$options[$row['option']] = $row;
			}

			return($options);
		}

		return(false);
	}

	/**
	 * Checks whether an option is valid or not
	 *
	 * @param	string			The option name
	 * @returns	boolean			Returns true if the option was valid, and false on error
	 */
	function options_is_valid($option)
	{
		return((($options = options_get_all()) !== false ? isset($options[$option]) : false));
	}

	/**
	 * Resets an option to its default value
	 *
	 * @param	string			The option name
	 * @return	boolean			True if the value was reset, and false on error
	 */
	function options_reset($option)
	{
		if(($options = options_get_all()) === false)
		{
			return(false);
		}

		$option = $options[$option];

		if($option['value'] == $option['defaultvalue']);
		{
			return(true);
		}

		global $registry;

		$result = $registry->db->query('
						UPDATE 
							`' . TUXXEDO_PREFIX . 'options` 
						SET 
							`value` = \'%s\' 
						WHERE 
							`option` = \'%s\'', $registry->db->escape($option['defaultvalue']), $registry->db->escape($option['option']));

		return($result && $registry->db->getAffectedRows($result));
	}

	/**
	 * Deletes an option
	 *
	 * @param	string			The option name
	 * @return	boolean			Returns true if the option was deleted, and false on error
	 */
	function options_delete($option)
	{
		global $registry;

		$result = $registry->db->query('
						DELETE FROM 
							`' . TUXXEDO_PREFIX . 'options`
						WHERE 
							`option` = \'%s\'', $registry->db->escape($option));

		return($result && $registry->db->getAffectedRows($result));
	}

	/**
	 * Converts a value to an option type
	 *
	 * @param	string			The one character option type: b, i etc.
	 * @param	string			The value to convert
	 * @return	string			Converts the value into the desired type, and string for unknown types
	 */
	function options_convert_type($type, $value)
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