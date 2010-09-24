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
	use Tuxxedo\Exception;
	use Tuxxedo\MVC\View;
	use Tuxxedo\Registry;
	use Tuxxedo\Router;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * The base controller class for the MVC components
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	abstract class Controller
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Router object
		 *
		 * @var		\Tuxxedo\Router
		 */
		protected $router;

		/**
		 * Layout template object
		 *
		 * @var		\Tuxxedo\MVC\View
		 */
		protected $layout;

		/**
		 * Current view template
		 *
		 * @var		\Tuxxedo\MVC\View
		 */
		protected $view;

		/**
		 * Template buffer
		 *
		 * @var		string
		 */
		protected $buffer		= '';


		/**
		 * Constructor
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 */
		public function __construct(Registry $registry)
		{
			$this->registry = $registry;
		}

		/**
		 * Set the router object
		 *
		 * @param	\Tuxxedo\Router\Uri		Router object used
		 * @return	void				No value is returned
		 */
		final public function setRouter(Router\Uri $router)
		{
			$this->router = $router;
		}

		/**
		 * Set the template object used for the layout
		 *
		 * @param	\Tuxxedo\MVC\View		Layout template
		 * @return	void				No value is returned
		 */
		final public function setLayout(View $layout)
		{
			$layout->setLayout(true);

			$this->layout = $layout;
		}

		/**
		 * Set the view object
		 *
		 * @param	\Tuxxedo\MVC\View		View template for the current action
		 * @return	void				No value is returned
		 */
		final public function setView(View $view)
		{
			$this->view = $view;
		}

		/**
		 * Dispatches the controller and renders the page content
		 *
		 * @return	string				Rendered view
		 *
		 * @throws	\Tuxxedo\Exception		If the controller does not exists
		 */
		final public function dispatch()
		{
			if($this instanceof Controller\Dispatchable)
			{
				$this->dispatcher(self::DISPATCH_PRE);
			}

			$action		= strtolower($this->router->getAction());
			$action_method 	= $this->router->getActionMethod();

			if(!\method_exists($this, $action_method))
			{
				throw new Exception\MVC\InvalidAction;
			}

			if(isset($this->acl) && $this->registry->user && (!\is_array($this->acl) && !$this->registry->user->isGranted((integer) $this->acl) || \is_array($this->acl) && isset($this->acl[$action]) && !$this->registry->user->isGranted($this->acl[$action])))
			{
				throw new Exception\MVC\InvalidPermission;
			}

			\ob_start();
			$this->{$action_method}($this->router->getParameters());

			$content = \ob_get_clean();

			if($this instanceof Controller\Dispatchable)
			{
				$this->dispatcher(self::DISPATCH_POST);
			}

			if($this->layout || $this->view)
			{
				if(!empty($content))
				{
					echo($content);

					$content = '';
				}

				if($this->view)
				{
					eval('$content = "' . (string) $this->view . '";');
				}

				if($this->layout)
				{
					eval('$this->buffer = "' . (string) $this->layout . '";');
					return($this->buffer);
				}
			}

			return($content);
		}
	}
?>