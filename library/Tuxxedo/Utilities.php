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
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Utilities class wrapper. This class wraps around the previously 
	 * declared prodecural functions, which now exists as static methods 
	 * for primarily autoloading reasoning.
	 *
	 * Handlers does not exists within this class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @since		1.2.0
	 */
	class Utilities
	{
		/**
		 * Date formatting function
		 *
		 * If the 'datetime' registry instance is registered, then this function 
		 * will use localized timezone values.
		 *
		 * @param		integer				Optional timestamp to use, defaults to the current timestamp at script start (uses UTC)
		 * @param		string				Optional format to use, defaults to the 'date_format' option if available
		 * @return		string				Returns the formatted date
		 */
		public static function date($timestamp = NULL, $format = NULL)
		{
			static $timenow, $registry;

			if(!$timenow)
			{
				$registry	= Registry::init();
				$timenow 	= (\defined('\TIMENOW_UTC') ? \TIMENOW_UTC : \time());
			}

			if(!$format && $registry->datastore->options)
			{
				$format = $registry->datastore->options['date_format']['value'];
			}

			if(!$timestamp)
			{
				$timestamp = $timenow;
			}

			if(!$registry->datetime)
			{
				return(\date($format, $timestamp));
			}

			$old_ts = $registry->datetime->getTimestamp();
			$registry->datetime->setTimestamp($timestamp);
			$format = $registry->datetime->format($format);
			$registry->datetime->setTimestamp($old_ts);

			return($format);
		}

		/**
		 * Date format shorthand method, uses current timestamp
		 *
		 * @param		string				Optional format to use, defaults to the 'date_format' option if available
		 * @return		string				Returns the formatted date
		 *
		 * @since		1.2.0
		 */
		public static function datef($format)
		{
			return(self::date(NULL, $format));
		}

		/**
		 * Trims whitespace in SQL in a very basic way
		 *
		 * Trimming will strip all whitespace pre and post string, and indented 
		 * whitespace even if it is contained within a 'string'.
		 *
		 * @param	string				The SQL string to trim
		 * @return	string				Returns the trimmed SQL string
		 */
		public static function trimSql($sql)
		{
			$ret = '';
			$str = \str_split($sql);

			if(!$str)
			{
				return('');
			}

			foreach($str as $pos => $c)
			{
				$ret .= $c;

				$len = \strlen($ret);
				$ret = \rtrim($ret);

				if($len > \strlen($ret))
				{
					$ret .= ' ';
				}
			}

			return(\trim($ret));
		}
	}
?>