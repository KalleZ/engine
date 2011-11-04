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
	 * Router namespace, this contains various interfaces for routing using 
	 * different types of protocols
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	namespace Tuxxedo\MVC\Router;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\MVC\Router;

	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Uri based router interface.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	class Uri extends Router
	{
		/**
		 * Rule type constant - Controller
		 *
		 * @var		integer
		 */
		const TYPE_CONTROLLER		= 1;

		/**
		 * Rule type constant - Action
		 *
		 * @var		integer
		 */
		const TYPE_ACTION		= 2;


		/**
		 * Parses a uri and dispatches them all into controller, action and 
		 * parameters.
		 *
	 	 * The general pattern for the URI routing in this case is fairly common
		 * Assumed default names are Index and index for controller and action,
		 * though these can be changed using the methods.
		 * 
		 * Default routes:
		 * #1 / (default controller, default action, no params)
		 * #2 /<1> (<1> controller, default action, no params)
		 * #3 /<1>/<2> (<1> controller, <2> action, no params)
		 * #4 /<1>/<2>/<3> (<1> controller, default action, {<2>: <3>} params)
		 * #5 /<1>/<2>/<3>/<4> (<1> controller, <2> action, {<3>: <4>} params)
		 *
		 * Follows #4 and #5 based on whether number of pieces is even or odd (
		 * odd = #4, even = #5)
		 *
		 * @param	string				The Uri to parse
		 * @return	void				No value is returned
		 */
		public function parse($uri)
		{
			$parts = \explode('/', \trim($uri, '/'));

			foreach($parts as $key => $value)
			{
				if(empty($value))
				{
					unset($parts[$key]);
				}
			}

			switch($parts_len = \sizeof($parts))
			{
				case(1):
				{
					$controller 	= $parts[0];
				}
				break;
				case(2):
				{
					$controller	= $parts[0];
					$action		= $parts[1];
				}
				break;
				default:
				{
					if(!$parts_len)
					{
						break;
					}

					$controller = \array_shift($parts);
					--$parts_len;

					if($parts_len || !($parts_len % 2))
					{
						$action	= \array_shift($parts);
						--$parts_len;
					}

					if($parts_len)
					{
						for($i = 0; $i < $parts_len; $i += 2)
						{
							$parameters[$parts[$i]] = (isset($parts[$i + 1]) ? $parts[$i + 1] : NULL);
						}
					}
				}
				break;
			}

			$this->controller 	= (isset($controller) && ($controller = self::canonical($controller, self::TYPE_CONTROLLER)) ? $controller : self::$default_controller);
			$this->action		= (isset($action) && ($action = self::canonical($action, self::TYPE_ACTION)) ? $action : self::$default_action);
			$this->parameters	= (isset($parameters) ? $parameters : Array());
		}

		/**
		 * Generates a canonical name for various components
		 *
		 * @param	string 				The path or component to convert
		 * @param	integer				A rule type, this is used to make sure things are callable
		 * @return	string				Returns the canonical name
		 */
		public static function canonical($name, $type)
		{
			switch($type)
			{
				case(self::TYPE_CONTROLLER):
				{
					if(($pos = \strpos($name, '.')) !== false)
					{
						return(\substr($name, 0, $pos - 1));
					}
				}
				break;
				case(self::TYPE_ACTION):
				{
					return((\is_callable($name, true) && ((string)(integer) $name{0} === $name{0}) ? $name : false));
				}
			}

			return($name);
		}
	}
?>