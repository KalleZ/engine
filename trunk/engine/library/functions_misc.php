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

		foreach($tuxxedo->cache->timezones as $name => $offset)
		{
			$timezones[$n++] = $name . ' (' . ($offset > -1 ? '+' : '') . $offset . ')';

			if($timezone == $name)
			{
				$index = $n;
			}
		}

		return($timezones);
	}
?>