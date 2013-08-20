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
	 * @subpackage		Dev
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Utilities\IO;


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');



	/**
	 * Template cache for templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 */
	abstract class TemplateCache
	{
		/**
		 * Holds the loaded templates
		 *
		 * @var		array
		 */
		protected static $templates		= Array();
	}

	/**
	 * Template class
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 */
	class Template extends TemplateCache
	{
		/**
		 * Name of the template currently loaded
		 *
		 * @var		string
		 */
		protected $name;

		/**
		 * Template variables
		 *
		 * @var		array
		 */
		protected $variables			= Array();


		/**
		 * Loads a new template and constructs the object
		 *
		 * Invalid templates halts the execution of the script.
		 *
		 * @param	string			The name of the template
		 */
		public function __construct($template)
		{
			$template = strtolower($template);

			if(!isset(TemplateCache::$templates[$template]))
			{
				if(!is_file('./apidump/templates/' . $template . '.raw'))
				{
					IO::text('Error: Template file does not exists (' . $template . '.raw)');
					exit;
				}

				TemplateCache::$templates[$template] = file_get_contents('./apidump/templates/' . $template . '.raw');
			}

			$this->name = $template;
		}

		/**
		 * Property overloader, to register template variables
		 *
		 * @param	string			The name of the variable
		 * @param	string			The value of the variable
		 * @return	void			No value is returned
		 */
		public function __set($variable, $value)
		{
			$this->variables[strtolower((string) $variable)] = (string) $value;
		}

		/**
		 * String conversation overloader
		 *
		 * @return	string			 Returns the parsed template
		 */
		public function __toString()
		{
			return((string) $this->parse());
		}

		/**
		 * Parses the template variables
		 *
		 * @return	string			 Returns the parsed template
		 */
		public function parse()
		{
			$cache = TemplateCache::$templates[$this->name];

			if($this->variables)
			{
				foreach($this->variables as $variable => $value)
				{
					$cache = str_replace('{$' . $variable . '}', $value, $cache);
				}
			}

			return($cache);
		}

		/**
		 * Saves the contents to a file
		 *
		 * @param	string			Name of the file (will be: './apidump/output/XXX.html')
		 * @return	boolean			Returns true if the file was saved with success, otherwise false
		 */
		public function save($file)
		{
			return(file_put_contents('./apidump/output/' . $file . '.html', $this->parse()));
		}
	}

	/**
	 * Layout class
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 */
	class Layout extends Template
	{
		/**
		 * Loads a new template in layout mode
		 *
		 * This causes the template cache to be invoked and loads in 'header' and 'footer' 
		 * which then will be declared as variables.
		 *
		 * @param	string			The name of the template
		 */
		public function __construct($template)
		{
			parent::__construct($template);

			$this->variables['header']	= (string) new Template('header');
			$this->variables['footer']	= (string) new Template('footer');
		}
	}


	$json = @json_decode(@file_get_contents('./apidump/engine_api.json'));

	if(!$json)
	{
		IO::text('Error: Unable to read Engine API from the exported JSON file');
		exit;
	}

	$files = $constants = $functions = $classes = $interfaces = Array();

	foreach($json as $file => $struct)
	{
		$files[] = $file;

		if($struct->functions)
		{
			foreach($struct->functions as $meta)
			{
				$functions[] = $meta;
			}
		}

		if($struct->constants)
		{
			foreach($struct->constants as $name => $meta)
			{
				$constants[] = array_merge(Array('name' => $name), (array) $meta);
			}
		}

		foreach(Array('classes', 'interfaces') as $type)
		{
			if(!$struct->{$type})
			{
				continue;
			}

			foreach($struct->{$type} as $name => $meta)
			{
				${$type}[] = array_merge(Array('name' => $name), (array) $meta);
			}
		}
	}

	$generated_tocs = Array();

	foreach(Array('files', 'constants', 'functions', 'classes', 'interfaces') as $obj)
	{
		$obj_struct = ${$obj};

		if(!sizeof($obj_struct))
		{
			continue;
		}

		$toc		= new Layout('toc');
		$toc->name	= $generated_tocs[] = $obj;
		$bits		= '';

		foreach($obj_struct as $data)
		{
			$name		= (is_scalar($data) ? $data : (is_array($data) && isset($data['name']) ? $data['name'] : $data->function));

			$bit 		= new Template('toc_bit');
			$bit->link	= '#';
			$bit->name	= $name;

			$bits 		.= (string) $bit;
		}

		$toc->toc = $bits;

		$toc->save($obj);
	}
?>