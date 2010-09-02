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
	 * Request handler interface, this is used to route and parse 
	 * request interfaces like HTTP and can be used for a ReST alike 
	 * interpreter.
	 *
	 * @author		Ross Masters <ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Request
	{
		/**
		 * Holds the loaded request handles for 
		 * quick re-loading
		 *
		 * @var		array
		 */
		public static $loaded_handlers 		= Array();


		/**
		 * Handler factory, this method instanciates a new 
		 * request handler
		 *
		 * @param	string				The handler name
		 * @return	\Tuxxedo\Request		Returns a new request handle
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if a driver failed to load
		 */
		final public static function factory($handler)
		{
			global $registry;

			if(\in_array($handler, self::$loaded_handlers))
			{
				return(new $class);
			}

			$class		= '\Tuxxedo\Request\\' . $handler;
			$handle 	= new $class;

			if(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt request handler, handler class does not follow the handler specification');
			}

			self::$loaded_handlers[] = $handler;

			return($handle);
		}
	}
?>