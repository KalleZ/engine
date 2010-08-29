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
	 
    namespace Tuxxedo\Exception;
    
	/**
	 * Form data exception, this exception is used to carry form data 
	 * so it can be displayed in a form if an error should occur while 
	 * processing the request
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class FormData extends \Tuxxedo\Exception
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
		public function __construct(array $formdata, $message = NULL)
		{
			if(!sizeof($formdata))
			{
				throw new Basic('A form data exception must contain atleast one element');
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