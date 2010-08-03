<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Default exception, mainly used for general errors. All 
	 * Tuxxedo specific exceptions extend this exception.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Exception extends Exception
	{
		/**
		 * Indicates whenever this is a fatal error or not
		 *
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct()
		{
			$args = func_get_args();

			if(!sizeof($args))
			{
				$args[0] = 'Unknown error';
			}

			parent::__construct(call_user_func_array('sprintf', $args));
		}
	}

	/**
	 * Basic exception type, this is used for errors that 
	 * should act as fatal errors. If an exception of this 
	 * is caught by the default exception handler it will 
	 * terminate the execution.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Basic_Exception extends Tuxxedo_Exception
	{
	}

	/**
	 * Form data exception, this exception is used to carry form data 
	 * so it can be displayed in a form if an error should occur while 
	 * processing the request
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_FormData_Exception extends Tuxxedo_Exception
	{
		/**
		 * Holds the current stored form data
		 *
		 * @var		array
		 */
		protected $formdata		= Array();


		/**
		 * Constructs a new formdata exception from an extended class
		 *
		 * @param	array			Form data to store as an array
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct(Array $formdata, $message = NULL)
		{
			if(!sizeof($formdata))
			{
				throw new Tuxxedo_Basic_Exception('A form data exception must contain atleast one element');
			}

			if($message)
			{
				if(func_num_args() > 2)
				{
					$args = func_get_args();

					unset($args[0]);

					parent::__construct(call_user_func_array('sprintf', $args));
				}
				else
				{
					parent::__construct($message);
				}
			}

			$this->formdata = $formdata;
		}

		/**
		 * Gets all the fields within the form data exception
		 *
		 * @return	array			Returns an array with all the registered elements
		 */
		public function getFields()
		{
			return($this->formdata);
		}
	}
?>