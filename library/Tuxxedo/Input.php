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
		private function process($source, $field, $type = self::TYPE_STRING, $options = 0)
		{
			switch($source)
			{
				case(1):
				{
					$data = \INPUT_GET;
				}
				break;
				case(2):
				{
					$data = \INPUT_POST;
				}
				break;
				case(3):
				{
					$data = \INPUT_COOKIE;
				}
				break;
				case(4):
				{
					$data = $field;
				}
				break;
				default:
				{
					return;
				}
				break;
			}

			if($source != 4 && !\filter_has_var($data, $field))
			{
				return;
			}

			if($options & self::OPT_RAW)
			{
				return(\filter_input($data, $field, \FILTER_UNSAFE_RAW, ($options & self::OPT_ARRAY ? \FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY : 0)));
			}

			switch($type)
			{
				case(self::TYPE_NUMERIC):
				{
					$flags = \FILTER_VALIDATE_INT;
				}
				break;
				case(self::TYPE_EMAIL):
				{
					$flags = \FILTER_VALIDATE_EMAIL;
				}
				break;
				case(self::TYPE_BOOLEAN):
				{
					$flags = \FILTER_VALIDATE_BOOLEAN;
				}
				break;
				default:
				{
					$flags = \FILTER_DEFAULT;
				}
				break;
			}

			if($source == 4)
			{
				$input = \filter_var($data, $flags, 0);
			}
			elseif($options & self::OPT_ARRAY && ($type == self::TYPE_NUMERIC || $type == self::TYPE_BOOLEAN))
			{
				$array = \filter_input($data, $field, \FILTER_UNSAFE_RAW, \FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY);

				if($type == self::TYPE_NUMERIC)
				{
					$input = function($var)
					{
						return((integer) $var);
					};
				}
				else
				{
					$mapper = function($var)
					{
						return((boolean) $var); 
					};
				}

				$input = \array_map($mapper, $array);
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