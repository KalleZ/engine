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

	namespace Tuxxedo\Exception;

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
		 * Constructs a template compiler excepton
		 *
		 * @param	string			The error message
		 * @param	array			The current condition this error occured at
		 */
		public function __construct($message, $conditions = NULL)
		{
			if($conditions !== NULL && !empty($conditions))
			{
				parent::__construct('%s at condition #%d', $message, $conditions);
			}
			else
			{
				parent::__construct($message);
			}
		}
	}
