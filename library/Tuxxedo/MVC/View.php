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
	use Tuxxedo\InfoAccess;
	use Tuxxedo\Registry;


	/**
	 * The View class for MVC based components, this wraps around the 
	 * existing style API.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	class View extends InfoAccess
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * The name of the view to load
		 *
		 * @var		string
		 */
		protected $name;

		/**
		 * The variables used within the view
		 *
		 * @var		array
		 */
		protected $variables		= Array();

		/**
		 * Global variables from the registry to extract
		 *
		 * @var		array
		 */
		protected $globals		= Array(
							'user', 
							'usergroup', 
							'userinfo', 
							'usergroupinfo'
							);


		/**
		 * Constructor, constructs a new View
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The name of the view to load
		 * @param	array				Array of special globals to define as variables from the registry
		 */
		public function __construct(Registry $registry, $name, Array $globals = NULL)
		{
			$this->name 		= (string) $name;
			$this->information	= &$this->variables;

			if($globals !== NULL)
			{
				if(\sizeof($globals))
				{
					$this->globals = \array_unique(\array_merge($this->globals, $globals));
				}
				else
				{
					$this->globals = Array();
				}
			}
		}

		/**
		 * Parses a view
		 *
		 * @return	string				Returns the parsed view
		 */
		public function parse()
		{
			if(sizeof($this->variables))
			{
				foreach($this->variables as $variable => $value)
				{
					if(!isset(${$variable}))
					{
						${$variaable} = $value;
					}
				}
			}

			eval('return("' . $this->registry->style->fetch($this->name) . '");');
		}

		/**
		 * Outputs a view
		 *
		 * @return	string				Returns the parsed view for outputting
		 */
		public function __toString()
		{
			return($this->parse());
		}
	}
?>