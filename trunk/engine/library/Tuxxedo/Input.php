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
	 * Input filtering class, this class cleans data 
	 * with magic quotes in mind. It will use the filter 
	 * extension if its available or use its own filtering 
	 * functions to emulate it.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Input
	{
		/**
		 * Data filter constant, numeric value
		 *
		 * @var		integer
		 */
		const TYPE_NUMERIC			= 1;

		/**
		 * Data filter constant, string value
		 *
		 * @var		integer
		 */
		const TYPE_STRING			= 2;

		/**
		 * Data filter constant, email value
		 *
		 * @var		integer
		 */
		const TYPE_EMAIL			= 3;

		/**
		 * Data filter constant, boolean value
		 *
		 * @var		integer
		 */
		const TYPE_BOOLEAN			= 4;

		/**
		 * Data filter option, gets the raw value 
		 * of the input without any type of santizing
		 *
		 * @var		integer
		 */
		const OPT_RAW				= 1;

		/**
		 * Data filter option, tells the cleaner that this 
		 * is an array input and any of its elements must be of 
		 * the given type. Note that recursive operations are not 
		 * done by the data filter
		 *
		 * @var		integer
		 */
		const OPT_ARRAY				= 2;


		/**
		 * Filters 'GET' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function get($field, $type = self::TYPE_STRING, $options = 0)
		{
			return($this->process(1, $field, $type, $options));
		}

		/**
		 * Filters 'POST' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function post($field, $type = self::TYPE_STRING, $options = 0)
		{
			return($this->process(2, $field, $type, $options));
		}

		/**
		 * Filters 'COOKIE' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function cookie($field, $type = self::TYPE_STRING, $options = 0)
		{
			return($this->process(3, $field, $type, $options));
		}

		/**
		 * Filters 'user' data, as passed to this method
		 *
		 * @param	string			The data to clean
		 * @param	integer			Type of input filtering performed
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function user($field, $type = self::TYPE_STRING)
		{
			return($this->process(4, $field, $type, 0));
		}

		/**
		 * Private filter method used by the GPC methods 
		 * to filter data.
		 *
		 * @param	integer			Where the data to filter is coming from (1 = GET, 2 = POST, 3 = COOKIE & 4 = User)
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		protected function process($source, $field, $type = self::TYPE_STRING, $options = 0)
		{
			static $sources, $flags_map;

			if(!$sources)
			{
				$sources 	= Array(
							\INPUT_GET, 
							\INPUT_POST, 
							\INPUT_COOKIE
							);

				$flags_map	= Array(
							self::TYPE_NUMERIC	=> \FILTER_VALIDATE_INT, 
							self::TYPE_EMAIL	=> \FILTER_VALIDATE_EMAIL, 
							self::TYPE_BOOLEAN	=> \FILTER_VALIDATE_BOOLEAN
							);
			}

			if($source != 4 && (!isset($sources[$source - 1])))
			{
				return;
			}

			$data = ($source == 4 ? $field : $sources[$source - 1]);

			if(!\filter_has_var($data, $field))
			{
				return;
			}
			elseif($options & self::OPT_RAW)
			{
				return(\filter_input($data, $field, \FILTER_UNSAFE_RAW, ($options & self::OPT_ARRAY ? \FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY : 0)));
			}

			$flags = (isset($flags_map[$type]) ? $flags_map[$type] : \FILTER_DEFAULT);

			if($source == 4)
			{
				$input = \filter_var($data, $flags, 0);
			}
			elseif($options & self::OPT_ARRAY && ($type == self::TYPE_NUMERIC || $type == self::TYPE_BOOLEAN))
			{
				$input = \array_map(function($var) use($type)
				{
					if($type == self::TYPE_NUMERIC)
					{
						return((integer) $var);
					}

					return((boolean) $var);
				}, \filter_input($data, $field, \FILTER_UNSAFE_RAW, \FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY));
			}
			else
			{
				$input = \filter_input($data, $field, $flags, ($options & self::OPT_ARRAY ? \FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY : 0));
			}

			if($type == self::TYPE_STRING && empty($input))
			{
				return;
			}

			return($input);
		}
	}
?>