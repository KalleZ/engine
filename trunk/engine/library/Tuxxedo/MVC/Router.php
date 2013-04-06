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
	 * MVC (Model-View-Controller) namespace, this contains all the base 
	 * implementation of each of the building bricks and extensions for 
	 * extending them even further.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	namespace Tuxxedo\MVC;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
	use Tuxxedo\Loader;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
	class Router extends Design\InfoAccess
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
		 * Default controller
		 *
		 * @var		string
		 */
		public static $default_controller	= 'main';

		/**
		 * Default action
		 *
		 * @var		string
		 */
		public static $default_action		= 'main';

		/**
		 * Additional parameters
		 *
		 * @var		array
		 */
		protected $parameters			= Array();


		/**
		 * Constructor, set the controller and action to their 
		 * default names
		 *
		 * @param	string						The application prefix (namespace), e.g. \Application\Controllers\, must end with a \
		 */
		public function __construct($prefix = NULL)
		{
			$this->registry		= Registry::init();
			$this->controller 	= self::$default_controller;
			$this->action 		= self::$default_action;
			$this->parameters	= &$this->information;

			if($prefix !== NULL)
			{
				if($prefix{\strlen($prefix) - 1} != '\\')
				{
					$prefix .= '\\';
				}

				$this->prefix = $prefix;
			}
		}

		/**
		 * Gets the preloadables for the bootstrap before dispatching 
		 * the router
		 *
		 * @return	array						Returns a multi dimentional array with preloadable data
		 *
		 * @throws	\Tuxxedo\Exception\MVC\InvalidController	Throws an invalid controller exception if the controller could not be loaded
		 */
		public function getPreloadables()
		{
			$controller	= $this->route();
			$preloadables 	= Array();

			foreach(Array('datastore', 'views', 'actionviews', 'phrasegroups') as $preloadable)
			{
				$preloadables[$preloadable] = (isset($controller->{$preloadable}) ? (array) $controller->{$preloadable} : Array());
			}

			if(!empty($this->action) && isset($controller->actionviews) && isset($controller->actionviews[$this->action]))
			{
				$preloadables['views'] = \array_merge($preloadables['views'], (array) $controller->actionviews[$this->action]);
			}

			return($preloadables);
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
		 * Gets all parameters
		 *
		 * @return	array						Returns all the parameters defined
		 */
		public function getParameters()
		{
			return($this->parameters);
		}

		/**
		 * Check if a parameter is set
		 *
		 * @param	string						Index of the parameter to check
		 * @return	bool						Returns true if the parameter exists, otherwise false
		 */
		public function __isset($parameter)
		{
			return(isset($this->parameters[$parameter]));
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
			$controller = $this->prefix . \ucfirst(\strtolower($this->controller));

			if(!Loader::load($controller, true))
			{
				throw new Exception\MVC\InvalidController;
			}

			$controller = new $controller($this->registry);

			$controller->setRouter($this);

			return($controller);
		}

		/**
		 * Route (shorthand method for the route() method) and calls the controller dispatching mechanism
		 *
		 * @return	\Tuxxedo\MVC\Controller				Returns a new controller instance
		 *
		 * @throws	\Tuxxedo\Exception\MVC\InvalidController	Throws an invalid controller exception if the controller could not be loaded
		 */
		public function __invoke()
		{
			return($this->route()->dispatch());
		}
	}
?>