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
	 * Basic exception type, this is used for errors that 
	 * should act as fatal errors. If an exception of this 
	 * is caught by the default exception handler it will 
	 * terminate the execution.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Intl extends Exception
	{
		public function __construct($message, $translation = NULL)
		{
			global $registry;

			if(!$registry->intl)
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
			else
			{
				if(!$registry->intl->find($message))
				{
					if($translation !== NULL)
					{
						throw new Basic('Unable to find phrase (%s) for internationalized exception, no translation defined', $message);
					}

					throw new Exception($translation);
				}

				$args = \func_get_args();

				unset($args[1]);

				parent::__construct(\call_user_func_array(Array($registry->intl, 'format'), $args));
			}
		}
	}
?>