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
	 * Basic exception type, this is used for errors that 
	 * should act as fatal errors. If an exception of this 
	 * is caught by the default exception handler it will 
	 * terminate the execution.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Basic extends \Tuxxedo\Exception
	{
	}    
