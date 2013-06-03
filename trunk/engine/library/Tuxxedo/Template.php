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
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
	class Template extends Design\InfoAccess
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
		 * Template name - header
		 *
		 * @var		string
		 */
		public static $header_template	= 'header';

		/**
		 * Template name - footer
		 *
		 * @var		string
		 */
		public static $footer_template	= 'footer';

		/**
		 * The variables used within the template
		 *
		 * @var		array
		 */
		protected $variables		= Array();

		/**
		 * Holds the globally declared variables
		 *
		 * @var		array
		 */
		protected static $globals	= Array();


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
		 * Sets a global variable
		 *
		 * @param	string				The name of the variable
		 * @param	mixed				The value of the variable
		 * @return	void				No value is returned
		 */
		public static function globalSet($variable, $value)
		{
			self::$globals[$variable] = $value;
		}

		/**
		 * Gets a global variable
		 *
		 * @param	string				The name of the variable
		 * @return	mixed				Returns the variable value, and NULL on non existant variable
		 */
		public static function globalGet($variable)
		{
			if(isset(self::$globals[$variable]))
			{
				return(self::$globals[$variable]);
			}
		}

		/**
		 * Checks if a global variable exists
		 *
		 * @param	string				The name of the variable
		 * @return	boolean				Returns true if the variable exists otherwise false
		 */
		public static function globalExists($variable)
		{
			return(isset(self::$globals[$variable]));
		}

		/**
		 * Unsets a global variable
		 *
		 * @param	string				The name of the variable
		 * @return	void				No value is returned
		 */
		public static function globalUnset($variable)
		{
			if(isset(self::$globals[$variable]))
			{
				unset(self::$globals[$variable]);
			}
		}

		/**
		 * Parses a template
		 *
		 * @return	string				Returns the parsed template
		 */
		public function parse()
		{
			if($this->layout && $this->name != self::$footer_template && $this->name != self::$header_template)
			{
				$header = new self(self::$header_template, true);
				$footer = new self(self::$footer_template, true);
			}

			if($this->variables || self::$globals)
			{
				$this->buffer = (self::$globals ? \array_merge(self::$globals, $this->variables) : $this->variables);

				foreach($this->buffer as $variable => $value)
				{
					if(!isset(${$variable}))
					{
						${$variable} = $value;
					}
				}

				$this->buffer = '';
			}

			if(!$this->registry->style->isLoaded($this->name))
			{
				$this->registry->style->cache(Array($this->name)) or \tuxxedo_errorf('Unable to load template \'%s\'', $this->name);
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