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
	 * The base controller class for Tuxxedo's MVC components
	 * @package		Engine
	 * @subpackage	MVC
	 */
	class Tuxxedo_Controller
	{
		/**
		 * HTTP request object
		 * @var		Tuxxedo_Request
		 */
		protected $request;
		
		/**
		 * Router object
		 * @var		Tuxxedo_Router
		 */
		protected $router;
		
		/**
		 * Layout template object
		 * @var		Tuxxedo_Template
		 */
		protected $layout;
		
		/**
		 * Current view template
		 * @var		Tuxxedo_Template
		 */
		protected $view;
	
		/**
		 * Set the request object
		 * @param	Tuxxedo_Request		Request object
		 */
		public function setRequest(Tuxxedo_Request $request) 
		{
			$this->request = $request;
		}
	
		/**
		 * Get the request object (externally)
		 * @return		Tuxxedo_Request
		 */
		public function getRequest()
		{
			return $this->request;
		}
	
		/**
		 * Set the router object
		 * @param	Tuxxedo_Router		Router object used
		 */
		public function setRouter(Tuxxedo_Router $router)
		{
			$this->router = $router;
		}
		
		/**
 		 * Get the router object externally
 		 * @return		Tuxxedo_Router
		 */
		public function getRouter()
		{
			return $this->router;
		}
		
		/**
		 * Set the template object used for the layout
		 * @param		Tuxxedo_Template	Layout template
		 */
		public function setLayout(Tuxxedo_Template $layout)
		{
			$this->layout = $layout;
		}
	
		/**
		 * Get the layout object externally
		 * @return		Tuxxedo_Template	Layout template
		 */
		public function getLayout()
		{
			return $this->layout;
		}
	
		/**
		 * Set the view object
		 * @param		Tuxxedo_Template	View template for the current action
		 */
		public function setView(Tuxxedo_View $view)
		{
			$this->view = $view;
		}
	
		/**
		 * Get the view object externally
		 * @return		Tuxxedo_Template	View template
		 */
		public function getView()
		{
			return $this->view;
		}
	
		/**
		 * Init hook for controllers to run code before the controller's action
		 * is dispatched. The first method called in the controller's dispatch
		 * routine. Generally used for instantiating common variables.
		 * @return		void
		 */
		public function init() { }
	
		/**
		 * Hook for the controller to execute code before it is dispatched.
		 * Called after init and generally used for checking authentication - 
		 * e.g. whether the user is authenticated and has permissions.
		 * @return		void
		 */
		public function preDispatch() { }
		
		/**
		 * Hook for the controller to execute code after it is dispatched.
		 * The last method called during the dispatch before the view is
		 * returned. Generally used for any last minute view modifications.
		 * @return		void
		 */
		public function postDispatch() { }
	
		/**
		 * Dispatch the controller, returning a rendered View object.
		 * Calls a series of hooks in the order: {@link init}, 
		 * {@link preDispatch}, action and {@link postDispatch}.
		 * @throws		Tuxxedo_Basic_Exception		If the controller does not
		 * 											define the action.
		 * @return		Tuxxedo_View	Rendered view
		 */
		public function dispatch()
		{
			// Execute any initialisation logic the controller needs
			$this->init();
			
			// Call pre-dispatch hook method
			$this->preDispatch();
		
			// Get the action name and call its method
			$method = $this->router()->getAction() . "Action";
		
			// Check the method exists first
			if (!method_exists($this, $method))
			{
				$className = get_class($this);
				throw new Tuxxedo_Basic_Exception(
					"Action method does not exist in $className.");
			}
		
			$this->$method();
		
			// Call the post-dispatch hook
			$this->postDispatch();
		
			// Return view object
			return $this->view;
		}
	}
