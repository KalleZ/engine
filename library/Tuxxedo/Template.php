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
	use Tuxxedo\InfoAccess;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Template class, this class serves as an object oriented way of creating 
	 * templates, mainly designed for use with the MVC View class
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	class Template extends InfoAccess
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * The name of the template to load
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
		 * The variables used within the template
		 *
		 * @var		array
		 */
		protected $variables		= Array();


		/**
		 * Constructor, constructs a new template
		 *
		 * @param	string				The name of the template to load
		 * @param	boolean				Set to true to activate layout mode, and false to not
		 * @param	array				Default variables to set
		 */
		public function __construct($name, $layout = false, Array $variables = NULL)
		{
			$this->registry		= Registry::init();
			$this->name 		= (string) $name;
			$this->information	= &$this->variables;
			$this->layout		= (boolean) $layout;

			if($variables)
			{
				$this->variables = $variables;
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
		 * Parses a template
		 *
		 * @return	string				Returns the parsed template
		 */
		public function parse()
		{
			$lowered_name = strtolower($this->name);

			if($this->layout && $lowered_name != 'header' && $lowered_name != 'footer')
			{
				$header = new self('header', true);
				$footer = new self('footer', true);
			}

			if($this->variables)
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

			if($this->layout)
			{
				return($this->buffer);
			}

			return(\str_replace('"', '\"', $this->buffer));
		}

		/**
		 * Outputs a template
		 *
		 * @return	string				Returns the parsed template for outputting
		 */
		public function __toString()
		{
			return($this->parse());
		}
	}
?>