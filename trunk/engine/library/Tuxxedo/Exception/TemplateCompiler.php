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
	 * Template compiler exception, any compilation error will be 
	 * of this exception type.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class TemplateCompiler extends \Tuxxedo\Exception
	{
		/**
		 * Exception type - None
		 *
		 * @var		integer
		 * @since	1.2.0
		 */
		const TYPE_NONE			= 0;

		/**
		 * Exception type - Condition
		 *
		 * @var		integer
		 * @since	1.2.0
		 */
		const TYPE_CONDITION		= 1;

		/**
		 * Exception type - Phrase
		 *
		 * @var		integer
		 * @since	1.2.0
		 */
		const TYPE_PHRASE		= 2;


		/**
		 * Compiler stack data
		 *
		 * @var		\stdClass
		 */
		protected $stack_data;


		/**
		 * Constructs a template compiler excepton
		 *
		 * @param	string			The error message
		 * @param	\stdClass		The current compiler stack data
		 *
		 * @changelog	1.1.0			Added the $stack_data parameter
		 */
		public function __construct($message, \stdClass $stack_data = NULL)
		{
			if($stack_data && isset($stack_data->type) && $stack_data->type != self::TYPE_NONE)
			{
				parent::__construct('%s at %s #%d', $message, ($stack_data->type == self::TYPE_CONDITION ? 'condition' : 'phrase'), $stack_data->conditions);
			}
			else
			{
				parent::__construct($message);
			}

			$this->stack_data = $stack_data;
		}

		/**
		 * Fetches the compiler stack data
		 *
		 * @return	\stdClass		Returns the compiler stack data, and NULL if non was available
		 *
		 * @since	1.1.0
		 */
		public function getStackData()
		{
			return($this->stack_data);
		}
	}
?>