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
		 * The layout mode
		 *
		 * @var		boolean
		 */
		protected $layout		= false;

		/**
		 * Template buffer
		 *
		 * @var		string
		 */
		protected $buffer		= '';

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
		protected $globals		= Array();


		/**
		 * Constructor, constructs a new View
		 *
		 * @param	string				The name of the view to load
		 * @param	array				Array of special globals to define as variables from the registry
		 */
		public function __construct($name, Array $globals = NULL)
		{
			global $registry;

			$this->registry		= $registry;
			$this->name 		= (string) $name;
			$this->information	= &$this->variables;

			if($globals !== NULL)
			{
				$this->globals = $globals;
			}
		}

		/**
		 * Whether to set this as a layout or not
		 *
		 * @param	boolean				Set to true to activate layout mode, and false to not
		 * @return	void				No value is returned
		 */
		public function setLayout($mode)
		{
			$this->layout = (boolean) $mode;
		}

		/**
		 * Parses a view
		 *
		 * @return	string				Returns the parsed view
		 */
		public function parse()
		{
			if($this->layout)
			{
				eval('$header = "' . $this->registry->style->fetch('header') . '";');
				eval('$footer = "' . $this->registry->style->fetch('footer') . '";');
			}

			if(\sizeof($this->variables))
			{
				foreach($this->variables as $variable => $value)
				{
					if(!isset(${$variable}))
					{
						${$variable} = $value;
					}
				}
			}

			eval('$this->buffer = "' . $this->registry->style->fetch($this->name) . '";');
			return(\str_replace('"', '\"', $this->buffer));
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