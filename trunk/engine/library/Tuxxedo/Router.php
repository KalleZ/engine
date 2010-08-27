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

    namespace Tuxxedo;

	/**
	 * The router can detect from a range of sources the required controller and
	 * action to execute.
	 * @package		Engine
	 */
	class Router
	{
		/**
		 * @var		string	Controller name
		 * Private, set using {@see setController}
		 */
		protected $controller;
		
		/**
		 * @var		string	Action name
		 * Private, set using {@see setAction}
		 */
		protected $action;
		
		/**
		 * @var		array	Any parameters encoded in the input
		 */
		protected $params = array();

		/**
		 * @var		string	The default controller name
		 */
		protected static $defaultController = "Index";
		
		/**
		 * @var		string	The default action name
		 */
		protected static $defaultAction = "index";

		/**
		 * Constructor, set the controller and action to their default names
		 */
		public function __construct() {
			// Set default names
			$this->controller = self::$defaultController;
			$this->action = self::$defaultAction;
		}

		/**
		 * Set the normalised controller name
		 * @param	string	Controller name
		 */
		public function setController($controller) {
			$this->controller = $controller;
		}
		
		/**
		 * Get the routed controller name
		 * @return	string	Controller name
		 */
		public function getController() {
			return $this->controller;
		}
		
		/**
		 * Set the normalised action name
		 * @param	string	Action name
		 */
		public function setAction($action) {
			$this->action = $action;
		}
		
		/**
		 * Get the routed action name
		 * @return	string	Action name
		 */
		public function getAction() {
			return $this->action;
		}

		/**
		 * Get a parameter value
		 * @param	string	Index of the parameter
		 * @return	string	Parameter value
		 */
		public function __get($paramName) {
			return $this->params[$paramName];
		}

		/**
		 * Check if a parameter is set
		 * @param	string	Index of the parameter to check
		 * @return	bool
		 */
		public function __isset($paramName) {
			return isset($this->params[$paramName]);
		}
	}
