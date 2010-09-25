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
	 * Gets all available options from the database
	 *
	 * @return	array			Returns an array with all the option names as values, and false on error
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
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

		return($options);
	}

	/**
	 * Checks whether an option is valid or not
	 *
	 * @param	string			The option name
	 * @returns	boolean			Returns true if the option was valid, and false on error
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_is_valid($option)
	{
		return((($options = options_get_all()) !== false ? isset($options[$option]) : false));
	}

	/**
	 * Gets a single option
	 *
	 * @param	string			The option name
	 * @returns	array			Returns the option value if the option was valid, and false on error
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_get_single($option)
	{
		return((($options = options_get_all()) !== false && isset($options[$option]) ? $options[$option] : false));
	}

	/**
	 * Resets an option to its default value
	 *
	 * @param	string			The option name
	 * @return	boolean			True if the value was reset, and false on error
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_reset($option)
	{
		if(($options = options_get_all()) === false)
		{
			return(false);
		}

		$option = $options[$option];

		if($option['value'] === $option['defaultvalue'])
		{
			return(true);
		}

		$registry	= Registry::init();
		$result 	= $registry->db->equery('
								UPDATE 
									`' . TUXXEDO_PREFIX . 'options` 
								SET 
									`value` = \'%s\' 
								WHERE 
									`option` = \'%s\'', $option['defaultvalue'], $option['option']);

		return($result && $registry->db->getAffectedRows($result));
	}

	/**
	 * Deletes an option
	 *
	 * @param	string			The option name
	 * @return	boolean			Returns true if the option was deleted, and false on error
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_delete($option)
	{
		$registry	= Registry::init();
		$result 	= $registry->db->equery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'options`
								WHERE 
									`option` = \'%s\'', $option);

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

	/**
	 * Converts an option type to a shorthand name
	 *
	 * @param	string			The long option type
	 * @return	string			Returns the shorthand option type
	 */
	function options_shorthand_type($type)
	{
		switch($type)
		{
			case('boolean'):
			{
				return('b');
			}
			break;
			case('integer'):
			{
				return('i');
			}
			break;
		}

		return('s');
	}

	/**
	 * Adds a new option
	 *
	 * @param	string			The option name
	 * @param	string			The option data type
	 * @param	string			The option value
	 * @return	boolean			Returns true if the option were added, otherwise false
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_add($name, $type, $value)
	{
		if(options_is_valid($name) || empty($name))
		{
			return(false);
		}

		$result = Registry::init()->db->equery('
							INSERT INTO 
								`' . TUXXEDO_PREFIX . 'options` 
								(
									`option`, 
									`value`, 
									`defaultvalue`, 
									`type`
								)
							VALUES
								(
									\'%1$s\', 
									\'%2$s\', 
									\'%2$s\', 
									\'%3$s\'
								)', $name, options_convert_type($type, $value), options_shorthand_type($type));

		return($result);
	}

	/**
	 * Edits an option
	 *
	 *
	 * @param	string			The old option name
	 * @param	string			The new option name
	 * @param	string			The new option data type
	 * @param	string			The new option value
	 * @return	boolean			Returns true if the option were edited, otherwise false
	 *
	 * @throws	\Tuxxedo\Exception\SQL	Throws a SQL exception if the query should fail
	 */
	function options_edit($original, $name, $type, $value)
	{
		if(!options_is_valid($original) || empty($original) || ($original !== $name && (options_is_valid($name) || empty($name))))
		{
			return(false);
		}

		$default 	= options_get_single($original);
		$default 	= $default['defaultvalue'];
		$registry	= Registry::init();
		$result 	= $registry->db->equery('
								UPDATE 
									`' . TUXXEDO_PREFIX . 'options` 
								SET
									`option` = \'%2$s\', 
									`value` = \'%3$s\', 
									`type` = \'%4$s\'
								WHERE 
									`option` = \'%1$s\'', $original, $name, options_convert_type($type, $value), options_shorthand_type($type));

		return($result && $registry->db->getAffectedRows($result));
	}
?>