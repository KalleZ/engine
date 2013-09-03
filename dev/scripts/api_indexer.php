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
	use Tuxxedo\Version;


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

			$this->name 			= $template;
			$this->variables['version']	= Version::FULL;
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

		/**
		 * Saves the file using a generated hash
		 *
		 * Calls api_file_hash() and then Template::save(), meaning the 
		 * parameters and return values will match that of those.
		 *
		 * @param	string			The file type ('file', 'constant', 'function', 'class' or 'interface')
		 * @param	string			The name of the object (fx. for function: 'api_file_hash')
		 * @param	string			The generated hash name (as a reference)
		 * @return	boolean			Returns true if the file was saved with success, otherwise false
		 */
		public function hash($type, $name, &$hash = NULL)
		{
			return($this->save($hash = api_file_hash($type, $name)));
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


	/**
	 * File hash
	 *
	 * Generates a file hash (filename) based on the name and type to 
	 * avoid possible naming conflicts.
	 *
	 * Type can be either one of:
	 *
	 *   - file
	 *   - constant
	 *   - function
	 *   - class
	 *   - interface
	 *
	 * @param	string				The file type ('file', 'constant', 'function', 'class' or 'interface')
	 * @param	string				The name of the object (fx. for function: 'api_file_hash')
	 * @return	string				Returns a file name without an extension (fx. 'constant-tuxxedo-library-123456') or false on failure
	 */
	function api_file_hash($type, $name)
	{
		static $rng, $lcache, $types;

		if(!$rng)
		{
			$lcache	= Array();
			$types	= Array('file', 'constant', 'function', 'class', 'interface');
			$rng 	= function()
			{
				return(str_pad(mt_rand(0, 999999), 6, 0, STR_PAD_LEFT));
			};
		}

		$type = strtolower($type);

		if(!in_array($type, $types))
		{
			return(false);
		}

		do
		{
			$n = $rng();

			if(!isset($lcache[$n]))
			{
				$lcache[$n] = Array(
							$type, 
							$name
							);
			}
		}
		while(!isset($lcache[$n]));

		return($type . '-' . str_replace('_', '-', strtolower($name)) . '-' . $n);
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
				$functions[] = array_merge(Array('file' => $file), (array) $meta);
			}
		}

		if($struct->constants)
		{
			foreach($struct->constants as $name => $meta)
			{
				$constants[] = array_merge(Array('name' => $name, 'file' => $file), (array) $meta);
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
				${$type}[] = array_merge(Array('name' => $name, 'file' => $file), (array) $meta);
			}
		}
	}

	$generated_tocs = Array();
	$obj_types	= Array('files', 'constants', 'functions', 'classes', 'interfaces');

	foreach($obj_types as $obj)
	{
		if(!sizeof(${$obj}))
		{
			continue;
		}

		$generated_tocs[] = $obj;
	}

	if(!$generated_tocs)
	{
		IO::text('Error: No generatable elements found for table of contents');
		exit;
	}

	$docblock = function(Array $meta, $tag)
	{
		if(strtolower($tag) == 'tags' || !isset($meta['docblock']) || !isset($meta['docblock']->{$tag}))
		{
			return('Undefined value');
		}

		return($meta['docblock']->{$tag});
	};

	$docblock_tag = function(Array $meta, $tag)
	{
		if(!isset($meta['docblock']) || !isset($meta['docblock']->tags) || !isset($meta['docblock']->tags->{$tag}))
		{
			return('Undefined value');
		}

		return($meta['docblock']->tags->{$tag});
	};

	foreach($generated_tocs as $type)
	{
		switch($type)
		{
			case('constants'):
			{
				$const_ptr = Array();

				foreach($constants as $const => $meta)
				{
					$const_ptr[$const] = $meta['name'];
				}

				ksort($const_ptr);

				foreach($const_ptr as $const => $name)
				{
					$meta = $constants[$const];

					$template 		= new Layout('api_constant');
					$template->name		= $name;
					$template->file		= $meta['file'];
					$template->datatype	= $docblock_tag($meta, 'var');
					$template->namespace	= (empty($meta['namespace']) ? 'Global namespace' : $meta['namespace']);
					$template->description	= $docblock($meta, 'description');

					$template->hash('constant', $name);
				}
			}
			break;
			default:
			{
/* HACK HACK HACK */
				continue;

				IO::text('Error: Unable to handle unknown type: ' . $type);
				exit;
			}
			break;
		}
	}

	foreach($generated_tocs as $obj)
	{
		$toc		= new Layout('toc');
		$toc->name	= $obj;
		$bits		= '';

		foreach(${$obj} as $data)
		{
			$name		= (is_scalar($data) ? $data : (is_array($data) && isset($data['name']) ? $data['name'] : $data['function']));

			$bit 		= new Template('toc_bit');
			$bit->link	= '#';
			$bit->name	= $name;

			$bits 		.= (string) $bit;
		}

		$toc->toc = $bits;

		$toc->save($obj);
	}

	$bits		= '';
	$toc 		= new Layout('toc');
	$toc->name	= 'Table of contents';

	foreach($generated_tocs as $gtoc)
	{
		$bit 		= new Template('toc_bit');
		$bit->link	= $gtoc . '.html';
		$bit->name	= ucfirst($gtoc);

		$bits		.= (string) $bit;
	}

	$toc->toc = $bits;

	$toc->save('index');
?>