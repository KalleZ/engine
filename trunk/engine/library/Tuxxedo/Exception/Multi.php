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
	 *
	 * =============================================================================
	 */


	/**
	 * Exception namespace, this contains all the core exceptions defined within 
	 * the library.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Multi exception, this exception is used to carry multiple data 
	 * so it can be displayed when multiple causes were the reason for 
	 * a fail, for example a form validation.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 *
	 * @changelog		1.2.0			This exception used to be called 'FormData'
	 */
	class Multi extends \Tuxxedo\Exception
	{
		/**
		 * Holds the current stored data
		 *
		 * @var		array
		 */
		protected $data		= [];


		/**
		 * Constructs a new data exception from
		 *
		 * @param	array			D to store as an array
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct(Array $data, $message = NULL)
		{
			if(!$data)
			{
				throw new Basic('A multi exception must contain atleast one element');
			}

			if($message)
			{
				if(\func_num_args() > 2)
				{
					$args = \func_get_args();

					unset($args[0]);

					parent::__construct(\call_user_func_array('\sprintf', $args));
				}
				else
				{
					parent::__construct($message);
				}
			}

			$this->data = $data;
		}

		/**
		 * Gets all the data
		 *
		 * @return	array			Returns an array with all the data
		 *
		 * @changelog	1.2.0			Prior this method was called 'getFormData()'
		 */
		public function getData()
		{
			return($this->data);
		}
	}
?>