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
	 * Gets the timezones as an array with their offsets in 
	 * their name. Note that this requires the timezone datastore 
	 * cache to be loaded
	 *
	 * @param	string			If this parameter is set with the current timezone name, then the index parameter will be populated
	 * @param	integer			Reference, populated with the timezone offset in the cache table
	 * @return	array			Returns an array with timezones and their respective offsets, and false on error
	 */
	function get_timezones($timezone = NULL, &$index = NULL)
	{
		global $tuxxedo;

		if(!$tuxxedo->cache->timezones)
		{
			return(false);
		}

		$n		= 0;
		$timezones 	= Array();

		foreach($cache->timezones as $name => $offset)
		{
			$timezones[$n++] = $name . ' (' . ($offset > -1 ? '+' : '') . $offset . ')';

			if($timezone == $name)
			{
				$index = $n;
			}
		}

		return($timezones);
	}

	/**
	 * Generates a user salt for password hashing
	 *
	 * @param	integer			How many bytes the salt should be, defaults to 8
	 * @return	string			Returns the salt as a string
	 */
	function get_password_salt($length = 8)
	{
		$length = (integer) $length;

		if($length < 1)
		{
			return(false);
		}

		$salt 		= '';
		$salt_range 	= 'abcdefghijklmnopqrstuvwxyz0123456789!"#¤%&/()=?^*_-.,;:<>|@£$€{[]}~\'';

		for($char = 0; $char < $length; ++$char)
		{
			$c 	= $salt_range{mt_rand(0, strlen($salt_range) - 1)};
			$salt 	.= (mt_rand(0, 1) ? strtoupper($c) : $c);
		}

		return($salt);
	}
?>