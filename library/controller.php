<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright	Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Interface for dispatchable controller hooks
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	interface Tuxxedo_Controller_Dispatchable
	{
		/**
		 * Dispatch hook constant - Pre dispatching
		 *
		 * @var		integer
		 */
		const DISPATCH_PRE		= 1;

		/**
		 * Dispatch hook constant - Post dispatching
		 *
		 * @var		integer
		 */
		const DISPATCH_POST		= 2;


		/**
		 * Controller dispatch hook, this hook is called for 
		 * both pre and post dispatching and uses its only 
		 * parameter to determine which state we currently are 
		 * in
		 *
		 * @param	integer				The current dispatcher state
		 * @return	void				No value is returned
		 */
		public function dispatcher($mode);
	}

	/**
	 * The base controller class for the MVC components
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	abstract class Tuxxedo_Controller
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * HTTP request object
		 *
		 * @var		Tuxxedo_Request_HTTP
		 */
		protected $request;

		/**
		 * Router object
		 *
		 * @var		Tuxxedo_Router
		 */
		protected $router;

		/**
		 * Layout template object
		 *
		 * @var		Tuxxedo_Template
		 */
		protected $layout;

		/**
		 * Current view template
		 *
		 * @var		Tuxxedo_Template
		 */
		protected $view;


		/**
		 * Constructor
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 */
		public function __construct(Tuxxedo $tuxxedo)
		{
			$this->tuxxedo = $tuxxedo;
		}

		/**
		 * Set the request object
		 * @param	Tuxxedo_Request		Request object
		 */
		final public function setRequest(Tuxxedo_Request $request) 
		{
			$this->request = $request;
		}

		/**
		 * Set the router object
		 * @param	Tuxxedo_Router		Router object used
		 */
		final public function setRouter(Tuxxedo_Router $router)
		{
			$this->router = $router;
		}

		/**
		 * Set the template object used for the layout
		 * @param		Tuxxedo_Template	Layout template
		 */
		final public function setLayout(Tuxxedo_View $layout)
		{
			$this->layout = $layout;
		}

		/**
		 * Set the view object
		 * @param		Tuxxedo_Template	View template for the current action
		 */
		final public function setView(Tuxxedo_View $view)
		{
			$this->view = $view;
		}

		/**
		 * Dispatches the controller and renders the page content
		 *
		 * @return		string			Rendered view
		 *
		 * @throws		Tuxxedo_Basic_Exception	If the controller does not
		 */
		final public function dispatch()
		{
			if($this instanceof Tuxxedo_Controller_Dispatchable)
			{
				$this->dispatcher(self::DISPATCH_PRE);
			}

			$action = $this->router->getAction() . 'Action';

			if(!method_exists($this, $method))
			{
				throw new Tuxxedo_Exception('Unknown action called');
			}

			$this->$method();

			if($this instanceof Tuxxedo_Controller_Dispatchable)
			{
				$this->dispatcher(self::DISPATCH_POST);
			}

			/**
		 	 * @TODO	This will change once the Tuxxedo_View class is implemented
			 */
			eval('$view = "' . $this->tuxxedo->style->fetch($this->view) . '";');
			eval('return("' . $this->layout . ");');
		}
	}
?>