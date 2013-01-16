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
	 * Basic Multi error exception
	 *
	 * Can throw multiple errors at the same time, and works like the 'Basic' 
	 * exception and halts the execution.
	 *
	 * The 'FormData' exception is using the same basic design and is application 
	 * friendly and should be used for 'Runtime' errors.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class BasicMulti extends Basic
	{
		/**
		 * Holds the current stored errors
		 *
		 * @var		array
		 */
		protected $errors		= Array();


		/**
		 * Constructs a new basic multi error exception
		 *
		 * @param	array			Form data to store as an array
		 * @param	string			The error message, in a printf-alike formatted string or just a normal string
		 * @param	mixed			Optional argument #n for formatting
		 */
		public function __construct(Array $errors, $message = NULL)
		{
			if(!$errors)
			{
				throw new Basic('A multi error exception must contain atleast one error');
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

			$this->errors = $errors;
		}

		/**
		 * Gets all the errors
		 *
		 * @return	array			Returns an array with all the registered errors
		 */
		public function getErrors()
		{
			return($this->errors);
		}
	}
?>