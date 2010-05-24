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
	 * Data filter class, this class cleans data 
	 * with magic quotes in mind. It will use the filter 
	 * extension if its available or use its own filtering 
	 * functions otherwise.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Filter
	{
		/**
		 * Whether the filter extension is available
		 *
		 * @var		boolean
		 */
		public static $have_filter_ext	= false;

		/**
		 * Whether magic_quotes_gpc is enabled or not
		 *
		 * @var		boolean
		 */
		public static $have_magic_quotes	= false;


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration, Array $options)
		{
			self::$have_filter_ext 		= extension_loaded('filter');
			self::$have_magic_quotes	= get_magic_quotes_gpc();
		}

		/**
		 * Validates data using a user specified callback method
		 *
		 * @param	mixed			The data to validate
		 * @param	callback		A callback thats used to validate the data
		 * @return	boolean			Returns true if the callback returned success, otherwise false
		 */
		public function validate($data, $callback)
		{
			if(is_callable($callback))
			{
				return(call_user_func($callback, $data));
			}

			return(false);
		}

		/**
		 * Filters 'GET' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function get($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(1, $field, $type, $options));
		}

		/**
		 * Filters 'POST' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function post($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(2, $field, $type, $options));
		}

		/**
		 * Filters 'COOKIE' data
		 *
		 * @param	string			Field name in the input source
		 * @param	integer			Type of input filtering performed
		 * @param	integer			Additional filtering options
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function cookie($field, $type = TYPE_STRING, $options = 0)
		{
			return($this->filter(3, $field, $type, $options));
		}

		/**
		 * Filters 'user' data, as passed to this method
		 *
		 * @param	string			The data to clean
		 * @param	integer			Type of input filtering performed
		 * @return	mixed			Returns the filtered value, returns NULL on error
		 */
		public function user($field, $type = TYPE_STRING)
		{
			return($this->filter(4, $field, $type, 0));
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
		private function filter($source, $field, $type = TYPE_STRING, $options = 0)
		{
			switch($source)
			{
				case(1):
				{
					$data = (self::$have_filter_ext ? INPUT_GET : $_GET);
				}
				break;
				case(2):
				{
					$data = (self::$have_filter_ext ? INPUT_POST : $_POST);
				}
				break;
				case(3):
				{
					$data = (self::$have_filter_ext ? INPUT_COOKIE : $_COOKIE);
				}
				break;
				case(4):
				{
					$data = $field;

					if(!self::$have_filter_ext)
					{
						$data 	= Array($field);
						$field	= 0;
					}
				}
				break;
				default:
				{
					return;
				}
				break;
			}


			if(self::$have_filter_ext)
			{
				if($source != 4 && !filter_has_var($data, $field))
				{
					return;
				}

				if($options & INPUT_OPT_RAW)
				{
					return(filter_input($data, $field, FILTER_UNSAFE_RAW));
				}

				switch($type)
				{
					case(TYPE_NUMERIC):
					{
						$flags = FILTER_VALIDATE_INT;
					}
					break;
					case(TYPE_EMAIL):
					{
						$flags = FILTER_VALIDATE_EMAIL;
					}
					break;
					case(TYPE_BOOLEAN):
					{
						$flags = FILTER_VALIDATE_BOOLEAN;
					}
					break;
					default:
					{
						$flags = FILTER_DEFAULT;
					}
					break;
				}

				if($source == 4)
				{
					$input = filter_var($data, $flags, 0);
				}
				else
				{
					$input = filter_input($data, $field, $flags, ($options & INPUT_OPT_ARRAY ? FILTER_REQUIRE_ARRAY | FILTER_FORCE_ARRAY : 0));
				}

				return($input);
			}
			else
			{
				if($source != 4 && !isset($data[$field]))
				{
					return;
				}

				if($options & INPUT_OPT_RAW)
				{
					return($data[$field]);
				}

				if($options & INPUT_OPT_ARRAY)
				{
					$data[$field] = (array) $data[$field];

					if(self::$have_magic_quotes)
					{
						$data[$field] = array_map('stripslashes', $data[$field]);
					}

					if(sizeof($data[$field]))
					{
						foreach($data[$field] as $var => $tmp)
						{
							switch($type)
							{
								case(TYPE_NUMERIC):
								{
									$data[$field][$var] = (integer) $tmp;
								}
								break;
								case(TYPE_EMAIL):
								{
									$data[$field][$var] = (is_valid_email($data[$field]) ? $data[$field] : false);
								}
								break;
								case(TYPE_BOOLEAN):
								{
									$data[$field][$var] = (boolean) $tmp;
								}
								break;
								default:
								{
									$data[$field][$var] = (string) $tmp;
								}
								break;
							}
						}
					}
				}
				else
				{
					if($source != 4 && self::$have_magic_quotes)
					{
						$data[$field] = stripslashes($data[$field]);
					}

					switch($type)
					{
						case(TYPE_NUMERIC):
						{
							$data[$field] = (integer) $data[$field];
						}
						break;
						case(TYPE_EMAIL):
						{
							$data[$field] = (is_valid_email($data[$field]) ? $data[$field] : false);
						}
						break;
						case(TYPE_BOOLEAN):
						{
							$data[$field] = (boolean) $tmp;
						}
						break;
						default:
						{
							$data[$field] = (string) $data[$field];
						}
						break;
					}
				}

				if($type == TYPE_NUMERIC && $data[$field] == 0)
				{
					$data[$field] = NULL;
				}

				return($data[$field]);
			}
		}
	}
?>