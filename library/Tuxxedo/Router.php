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
	 * @subpackage		Library
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
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Loader;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * The router can detect from a range of sources the required controller and
	 * action to execute.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Router
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Application namespace
		 *
		 * @var		string
		 */
		protected $prefix;

		/**
		 * Current controller
		 *
		 * @var		string
		 */
		protected $controller;

		/**
		 * Current action
		 *
		 * @var		string
		 */
		protected $action;

		/**
		 * Additional parameters
		 *
		 * @var		array
		 */
		protected $parameters			= Array();

		/**
		 * Default controller
		 *
		 * @var		string
		 */
		public static $default_controller	= 'index';

		/**
		 * Default action
		 *
		 * @var		string
		 */
		public static $default_action		= 'index';


		/**
		 * Constructor, set the controller and action to their 
		 * default names
		 *
		 * @param	string						The application prefix (namespace)
		 */
		public function __construct($prefix = NULL)
		{
			$this->registry		= Registry::init();
			$this->controller 	= self::$default_controller;
			$this->action 		= self::$default_action;

			if($prefix !== NULL)
			{
				$this->prefix = $prefix;
			}
		}

		/**
		 * Set the normalised controller name
		 *
		 * @param	string						The controller name
		 * @return	void						No value is returned
		 */
		public function setController($controller)
		{
			$this->controller = $controller;
		}

		/**
		 * Get the routed controller name
		 *
		 * @return	string						The Controller name
		 */
		public function getController()
		{
			return($this->controller);
		}

		/**
		 * Set the normalised action name
		 *
		 * @param	string						The action name
		 * @return	void						No value is returned
		 */
		public function setAction($action)
		{
			$this->action = $action;
		}

		/**
		 * Get the routed action name
		 *
		 * @return	string						The action name
		 */
		public function getAction()
		{
			return($this->action);
		}

		/**
		 * Get the routed action method name
		 *
		 * @return	string						The action name
		 */
		public function getActionMethod()
		{
			return('Action' . $this->action);
		}

		/**
		 * Get a parameter value
		 *
		 * @param	string						Index of the parameter
		 * @return	string						The Parameter value, and NULL on undefined parameters
		 */
		public function __get($parameter)
		{
			if(!isset($this->parameters[$parameter]))
			{
				return;
			}

			return($this->parameters[$parameter]);
		}

		/**
		 * Check if a parameter is set
		 *
		 * @param	string						Index of the parameter to check
		 * @return	bool						Returns true if the parameter exists, otherwise false
		 */
		public function __isset($parameter)
		{
			return(isset($this->params[$parameter]));
		}

		/**
		 * Route (start the controller)
		 *
		 * @return	\Tuxxedo\MVC\Controller				Returns a new controller instance
		 *
		 * @throws	\Tuxxedo\Exception\MVC\InvalidController	Throws an invalid controller exception if the controller could not be loaded
		 */
		public function route()
		{
			try
			{
				$controller = $this->prefix . $this->controller;
				$controller = new $controller($this->registry);
				$controller->setRouter($this);
			}
			catch(Exception\Basic $e)
			{
				throw new Exception\MVC\InvalidController($e);
			}

			return($controller);
		}
	}
?>