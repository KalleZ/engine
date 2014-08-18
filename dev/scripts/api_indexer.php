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
	 * Template class
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 * @since		1.2.0
	 */
	class Template
	{
		/**
		 * Name of the template currently loaded
		 *
		 * @var		string
		 */
		protected $name;

		/**
		 * Output directory
		 *
		 * @var		string
		 */
		public static $outputdir		= '';

		/**
		 * Output extension (if custom)
		 *
		 * @var		string
		 */
		public static $outputext		= 'html';

		/**
		 * Template directory
		 *
		 * @var		string
		 */
		public static $templatedir		= '';

		/**
		 * Application name (if custom)
		 *
		 * @var		string
		 */
		public static $appname			= 'Tuxxedo Engine';

		/**
		 * Timestamps for when the API doc were generated
		 *
		 * @var		array
		 */
		public static $timestamps		= Array(
								'parsed'	=> NULL, 
								'rendered'	=> NULL
								);

		/**
		 * Template variables
		 *
		 * @var		array
		 */
		protected $variables			= [];

		/**
		 * Template cache
		 *
		 * @var		array
		 */
		protected static $templates		= [];


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

			if(!isset(self::$templates[$template]))
			{
				if(!is_file(self::$templatedir . '/' . $template . '.raw'))
				{
					IO::text('Error: Template file does not exists (' . $template . '.raw)');
					exit;
				}

				self::$templates[$template] = file_get_contents(self::$templatedir . '/' . $template . '.raw');
			}

			$this->name 			= $template;
			$this->variables['version']	= Version::FULL;
			$this->variables['appname']	= self::$appname;
			$this->variables['gendate']	= '';
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
		 * Property overloader, to get template variables
		 *
		 * @param	string			The name of the variable
		 * @return	void			Returns the value of the variable
		 */
		public function __get($variable)
		{
			$variable = strtolower((string) $variable);

			if(isset($this->variables[$variable]))
			{
				return($this->variables[$variable]);
			}
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
			$cache = self::$templates[$this->name];

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
		 * @param	string			Name of the file (will be: './apidump/output/XXX.YYY')
		 * @return	boolean			Returns true if the file was saved with success, otherwise false
		 */
		public function save($file)
		{
			return(file_put_contents(self::$outputdir . '/' . $file . '.' . self::$outputext, $this->parse()));
		}
	}

	/**
	 * Layout class
	 *
	 * Layout wrapper class around the base template, see the 
	 * constructor for more information.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 * @since		1.2.0
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

			if(self::$timestamps['parsed'] || self::$timestamps['rendered'])
			{
				$this->variables['gendate'] = rtrim((self::$timestamps['parsed'] ? 'Parsed: ' . date('H:i:s j/n - Y', self::$timestamps['parsed']) . ', ' : '') . (self::$timestamps['rendered'] ? 'Rendered: ' . date('H:i:s j/n - Y', self::$timestamps['rendered']) : ''), ', ');
			}
		}
	}

	/**
	 * Hash registry
	 *
	 * This class is a local cache for the hashes generated.
	 *
	 * It will attempt to load the 'api_hashes.json' file if it 
	 * exists in the output directory.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Dev
	 * @since		1.2.0
	 */
	class HashRegistry
	{
		/**
		 * Stored hashes
		 *
		 * @var		\stdClass
		 */
		protected $hashes;

		/**
		 * Output directory
		 *
		 * @var		string
		 */
		protected $outputdir		= '';


		/**
		 * Constructor, this will attempt to see if the file 'api_hashes.json' 
		 * exists within the output directory, and load it.
		 *
		 * @param	string			The output directory
		 */
		public function __construct($outputdir)
		{
			$this->outputdir = $outputdir;

			if(is_file($outputdir . '/api_hashes.json') && ($json = json_decode(file_get_contents($outputdir . '/api_hashes.json'))) !== false)
			{
				$this->hashes = (object) $json;
			}
			else
			{
				$this->hashes = new stdClass;
			}
		}

		/**
		 * Destructor, saves the hashes to the 'api_hashes.json' file if possible
		 */
		public function __destruct()
		{
			if($this->hashes)
			{
				file_put_contents($this->outputdir . '/api_hashes.json', json_encode($this->hashes));
			}
		}

		/**
		 * Gets a hash (or generates a new one)
		 *
		 * @param	string				The type ('constant', 'function', 'class', 'interface', 'trait', 'property', 'method' or 'namespace'), note that class constants uses 'constant'
		 * @param	string				The meta information
		 * @param	string				The file of where the object exists (fx. 'library/Tuxxedo/Bootstrap.php'), this is case sensitive
		 * @return	string				Returns a file name without an extension (fx. 'constant-tuxxedo-library-123456') or false on failure
		 */
		public function hash($type, Array $meta, $file)
		{
			$hinfo = self::getHashInfo($type, $meta, $file);

			if(!$hinfo)
			{
				return(false);
			}

			if(!isset($this->hashes->{$hinfo['hash']}))
			{
				return($this->hashes->{$hinfo['hash']} = $hinfo['file']);
			}

			return($this->hashes->{$hinfo['hash']});
		}

		/**
		 * Generates hash info based on the name of the object and 
		 * its meta data.
		 *
		 * @param	string				The type ('constant', 'function', 'class', 'interface', 'trait', 'property', 'method' or 'namespace'), note that class constants uses 'constant'
		 * @param	array				The meta information
		 * @param	string				The file of where the object exists (fx. 'library/Tuxxedo/Bootstrap.php'), this is case sensitive
		 * @return	array				Returns an array containing the hash values, returns false on error
		 */
		public static function getHashInfo($type, Array $meta, $file)
		{
			static $types, $tsymb;

			if(!$types)
			{
				$types	= ['constant', 'function', 'class', 'interface', 'trait', 'property', 'method', 'namespace'];
				$tsymb	= [
						'constant'	=> '#', 
						'property'	=> '$', 
						'method'	=> '::'
						];
			}

			$type = strtolower($type);

			if(!in_array($type, $types))
			{
				return(false);
			}

			return([
				'name'	=> $name = $file . '|' . $type . '|' . (isset($tsymb[$type]) ? $tsymb[$type] : '') . $mname = (isset($meta[$type]) ? $meta[$type] : $meta['name']), 
				'hash'	=> sha1($name), 
				'file'	=> $type . '-' . str_replace(['_', '.', '\\'], (!in_array($type, ['class', 'interface', 'trait', 'namespace']) ? '-' : ''), strtolower($mname)) . '-' . str_pad(mt_rand(0, 999999), 6, 0, STR_PAD_LEFT)
				]);
		}
	}


	$toc_cl = $toc_news = [];


	$docblock = function(Array $meta, $tag, $strip_html = true)
	{
		$tag = strtolower($tag);

		if($tag == 'tags' || !isset($meta['docblock']) || !isset($meta['docblock']->{$tag}))
		{
			return('Undefined value');
		}
		elseif($tag == 'description' && $strip_html)
		{
			return(utf8_decode(strip_tags($meta['docblock']->{$tag})));
		}

		return(utf8_decode($meta['docblock']->{$tag}));
	};

	$docblock_tag = function(Array $meta, $tag)
	{
		if(!isset($meta['docblock']) || !isset($meta['docblock']->tags) || !isset($meta['docblock']->tags->{$tag}))
		{
			return('Undefined value');
		}

		return($meta['docblock']->tags->{$tag});
	};

	$hashlookup = function($ltype, $name)
	{
		static $types;

		if(!$types)
		{
			$types = [
					'class'		=> 'classes', 
					'interface'	=> 'interfaces', 
					'trait'		=> 'traits'
					];
		}

		$ltype = explode('|', $ltype);

		if(!$ltype)
		{
			return(false);
		}

		foreach($ltype as $type)
		{
			if(!isset($types[$type]))
			{
				continue;
			}

			$ptr = $GLOBALS[$types[$type]];

			if(!$ptr)
			{
				continue;
			}

			foreach($ptr as $element)
			{
				if($element['name'] == $name)
				{
					return($element['hash']);
				}
			}
		}

		return(false);
	};

	$link = function($href, $title, $temp_name = 'link')
	{
		$template		= new Template($temp_name);
		$template->link		= $href;
		$template->title	= $title;

		return((string) $template);
	};

	$hashlink = function($datatype, $type = 'class|interface|trait') use($link, $hashlookup)
	{
		if(!($hash = $hashlookup($type, $datatype)))
		{
			return($datatype);
		}

		return($link($hash . '.' . Template::$outputext, $datatype));
	};

	$prototype = function($name, Array $meta) use($hashlink)
	{
		$return = 'void';
		$params = '';

		if($meta['docblock'] && isset($meta['docblock']->tags))
		{
			if(isset($meta['docblock']->tags->param))
			{
				foreach($meta['docblock']->tags->param as $param)
				{
					if($param[0]{0} == '\\' || strpos($param[0], '\\') !== false)
					{
						if($param[0]{0} !== '\\')
						{
							$param[0] = '\\' . $param[0];
						}

						$param[0] = $hashlink($param[0]);
					}

					$params .= $param[0] . ', ';
				}

				$params = rtrim($params, ', ');
			}

			if(isset($meta['docblock']->tags->return))
			{
				$return = $hashlink($meta['docblock']->tags->return[0]);
			}
		}

		if(!empty($meta['namespace']))
		{
			$name = $meta['namespace'] . '\\' . $name;
		}

		return(sprintf('%s %s(%s)', $return, $name, $params));
	};

	$nl2br = function($string)
	{
		return(str_replace("\n\n", '<br />', str_replace(["\n\r", "\r\n", "\r"], "\n", $string)));
	};

	$mformat = function($name, $as, $prefix = '')
	{
		$prefix = (!empty($prefix) ? $prefix . '::' : '');

		if($as == 'properties')
		{
			return($prefix . '$' . $name);
		}

		if($as == 'methods' || $as == 'functions')
		{
			return($prefix . $name . '()');
		}

		return($prefix . $name);
	};

	$desc = function(Array $meta, $strip_html = false, Array &$examples = NULL) use($docblock)
	{
		$desc = $docblock($meta, 'description', $strip_html);

		if(empty($desc))
		{
			return('No description available');
		}

		if(!$strip_html && $examples !== NULL && ($spos = strpos($desc, '<code>')) !== false)
		{
			$examples = [];

			do
			{
				if(($epos = strpos($desc, '</code>', $spos)) === false)
				{
					continue;
				}

				$examples[]	= '<?php' . PHP_EOL . PHP_EOL . trim(substr($desc, $spos + 6, $epos - $spos - 6)) . PHP_EOL . PHP_EOL . '?>';
				$desc 		= substr_replace($desc, '', $spos, $epos - $spos + 7);
			}
			while(isset($desc{$spos + 1}) && ($spos = strpos($desc, '<code>', ++$spos)) !== false);
		}

		return(htmlspecialchars($desc, ENT_NOQUOTES, 'ISO-8859-1'));
	};

	$tags = function(Array $meta, $text)
	{
		$tags	= '';

		if(($pos = strpos($text, "\n")) !== false)
		{
			$text = substr($text, 0, $pos);
		}

		if($meta['dev'])
		{
			$template	= new Template('tag');
			$template->tag	= 'Dev';

			$tags		= $template;
		}

		if(isset($meta['docblock']) && isset($meta['docblock']->tags) && (isset($meta['docblock']->tags->todo) || isset($meta['docblock']->tags->wip)))
		{
			if(isset($meta['docblock']->tags->todo))
			{
				$template	= new Template('tag');
				$template->tag	= 'TODO';

				$tags		.= $template;
			}

			if(isset($meta['docblock']->tags->wip))
			{
				$template	= new Template('tag');
				$template->tag	= 'WIP';

				$tags		.= $template;
			}
		}

		foreach(['final', 'private', 'protected', 'static'] as $modifier)
		{
			if($meta['metadata']->{$modifier})
			{
				$template	= new Template('tag');
				$template->tag	= ucfirst($modifier);

				$tags		.= $template;
			}
		}

		if(strlen($text) > 100)
		{
			$t 	= '';
			$text 	= substr($text, 0, 100);

			for($x = 0; $x < 100; ++$x)
			{
				if($text{$x} == '<')
				{
					break;
				}

				$t .= $text{$x};
			}
	
			return($tags . $t . '...');
		}

		return($tags . htmlentities($text));
	};

	$desct = function(Array $meta) use($desc, $tags)
	{
		return($tags($meta, $desc($meta, false)));
	};

	$examplecode = function(Array $examples)
	{
		if(!$examples)
		{
			return('');
		}

		$x	= 0;
		$bits	= '';
		$ex 	= new Template('examples');

		foreach($examples as $example)
		{
			$template 	= new Template('example_bit');
			$template->num	= ++$x;
			$template->code	= highlight_string($example, true);

			$bits		.= $template;
		}

		$ex->examples = $bits;

		return($ex);
	};

	$nst = function($nsname) use($link)
	{
		$template 		= new Template('obj_element');
		$template->element	= 'Namespace';

		if(empty($nsname))
		{
			$template->value = 'Global namespace';

			return($template);
		}

		global $nscache;

		$template->value = $link($nscache[$nsname]['meta']['hash'] . '.' . Template::$outputext, $nsname);

		return($template);
	};

	$throws = function(Array $meta) use($docblock_tag, $link, $hashlookup)
	{
		$t = $docblock_tag($meta, 'throws');

		if($t == 'Undefined value')
		{
			return('');
		}

		$exceptions = '';

		foreach($t as $e)
		{
			if(($hash = $hashlookup('class', $e[0])))
			{
				$href = $link($hash . '.' . Template::$outputext, $e[0]);
			}

			$template 		= new Template('throws_bit');
			$template->exception	= (isset($href) ? $href : $e[0]);
			$template->condition	= $e[1];

			$exceptions		.= $template;

			unset($href);
		}
		
		$template 		= new Template('throws');
		$template->exceptions	= $exceptions;

		return($template);
	};

	$warnings = function(Array $meta) use($docblock_tag)
	{
		$tags = '';

		if($docblock_tag($meta, 'wip') !== 'Undefined value')
		{
			$template		= new Template('warning');
			$template->warning	= 'This element is currently marked as a \'work-in-progress\', its behavior may change without notice and it may otherwise not function at all!';

			$tags			.= $template;
		}

		if($docblock_tag($meta, 'todo') !== 'Undefined value')
		{
			$template		= new Template('warning');
			$template->warning	= 'This element is currently marked with one or more TODO items, meaning it may not be functioning or documented entirely!';

			$tags			.= $template;
		}

		return($tags); 
	};

	$notices = function(Array $meta)
	{
		if(!$meta['dev'])
		{
			return('');
		}

		$template		= new Template('notice');
		$template->notice	= 'This element is a part of the developmental code and is only available in builds that includes developmental related APIs!';

		return($template);
	};

	$infobox = function(Array $meta, $tag, $name, $idname, $descname, \Closure $id_cb = NULL, \Closure $desc_cb = NULL) use($docblock_tag)
	{
		if(($t = $docblock_tag($meta, $tag)) === 'Undefined value')
		{
			return('');
		}

		$bits			= '';
		$template		= new Template('infobox');
		$template->name		= $name;
		$template->id_name	= $idname;
		$template->desc_name	= $descname;

		foreach($t as $d)
		{
			if(!is_array($d))
			{
				$dc 	= [];
				$dc[0]	= 0;
				$dc[1]	= $d;

				$d	= $dc;
			}

			$bit		= new Template('infobox_bit');
			$bit->id	= ($id_cb ? $id_cb($d[0]) : $d[0]);
			$bit->desc	= ($desc_cb ? $desc_cb($d[1]) : $d[1]);

			$bits		.= $bit;
		}

		$template->bits = $bits;

		return($template);
	};

	$todo = function(Array $meta) use($infobox)
	{
		return($infobox($meta, 'todo', 'TODO', '#', 'Note', function($id)
		{
			static $counter;

			if($counter === NULL)
			{
				$counter = 0;
			}

			return(++$counter);
		}));
	};

	$changelog = function(Array $meta, $name = '', $name_hash = '') use($infobox, &$toc_cl, $docblock_tag)
	{
		if(!empty($name) && ($vh = $docblock_tag($meta, 'changelog')) !== 'Undefined value')
		{
			foreach($vh as $vho)
			{
				if(!isset($toc_cl[$vho[0]]))
				{
					$toc_cl[$vho[0]] = [];
				}

				if(!isset($toc_cl[$vho[0]][$name]))
				{
					$toc_cl[$vho[0]][$name] = [
									'hash'		=> $name_hash, 
									'changes'	=> [], 
									'meta'		=> $meta
									];
				}

				$toc_cl[$vho[0]][$name]['changes'][] = $vho[1];
			}
		}

		return($infobox($meta, 'changelog', 'Version history', 'Version', 'Note'));
	};

	$since = function(Array $meta, Array $rmeta = NULL, $name = '', $name_hash = '', $name_type = '') use($docblock_tag, &$toc_news)
	{
		if(($s = $docblock_tag($meta, 'since')) === 'Undefined value')
		{
			if($rmeta && ($s = $docblock_tag($rmeta, 'since')) !== 'Undefined value')
			{
				return($s);
			}

			return('1.0.0');
		}

		if(!empty($name))
		{
			if(!isset($toc_news[$s]))
			{
				$toc_news[$s] = [];
			}

			$toc_news[$s][] = [
						'name'	=> $name, 
						'hash'	=> $name_hash, 
						'type'	=> $name_type, 
						'meta'	=> $meta
						];
		}

		return($s);
	};

	$seealso = function(Array $meta) use($docblock_tag, $link, $hashlookup)
	{
		$s = $docblock_tag($meta, 'see');

		if($s == 'Undefined value')
		{
			return('');
		}

		$bits = '';

		foreach($s as $see)
		{
			$hash = $hashlookup('class|interface|trait', $see);

			if(!$hash)
			{
				continue;
			}

			$bits .= $link($hash . '.' . Template::$outputext, $see);
		}

		return($bits);
	};

	$resource = function($file)
	{
		global $output, $tmpdir;

		return(@copy($tmpdir . '/' . $file, $output . '/' . $file));
	};


	IO::signature();
	IO::headline('API Indexer', 1);

	date_default_timezone_set('UTC');

	$warns	= [];
	$nodev	= IO::input('nodev');
	$output	= IO::input('outputdir');
	$tmpdir	= IO::input('templatedir');
	$ext 	= IO::input('outputext');

	$json 	= json_decode(file_get_contents(((($inputf = IO::input('inputfile')) !== false) ? $inputf : './apidump/engine_api.json')));

	if(!$json)
	{
		IO::text('Error: Unable to read API from the exported JSON file');
		exit;
	}

	if(!is_dir($output))
	{
		if($output !== false)
		{
			$warns[] = 'Invalid output directory, using default (./apidump/templates)';
		}

		$output = './apidump/output';
	}

	if(!is_dir($tmpdir))
	{
		if($tmpdir !== false)
		{
			$warns[] = 'Invalid template directory, using default (./apidump/templates)';
		}

		$tmpdir = './apidump/templates';
	}


	IO::ul();
	IO::li('Reading API dump...');


	Template::$outputdir 	= $output;
	Template::$outputext	= (empty($ext) ? 'html' : $ext);
	Template::$templatedir	= $tmpdir;

	if(($appname = IO::input('appname')) !== false)
	{
		Template::$appname = $appname;
	}

	$dottuxxedo		= false;
	$hashreg		= new HashRegistry($output);
	$constants 		= $functions = $classes = $interfaces = $traits = $namespaces = [];

	foreach($json as $file => $struct)
	{
		$isdev = (substr($file, 0, 3) == 'dev' || substr($file, 0, 11) == 'library/Dev');

		if(($nodev && $isdev) || $file == '.tuxxedo')
		{
			if($file == '.tuxxedo' && $struct->version !== 2 && $struct->version !== 3)
			{
				IO::error('The API must be format must be version 2 or 3, detected version: ' . $struct->version);
			}

			if(IO::input('timestamps'))
			{
				Template::$timestamps = [
								'parsed' 	=> $struct->timestamp, 
								'rendered'	=> time()
								];
			}

			$dottuxxedo = true;

			continue;
		}

		if($struct->functions)
		{
			foreach($struct->functions as $meta)
			{
				$functions[] = array_merge(['file' => $file, 'hash' => $hashreg->hash('function', (array) $meta, $file), 'dev' => $isdev], (array) $meta);
			}
		}

		if($struct->constants)
		{
			foreach($struct->constants as $name => $meta)
			{
				$constants[] = array_merge(['name' => $name, 'file' => $file, 'hash' => $hashreg->hash('constant', array_merge((array) $meta, ['name' => $name]), $file), 'dev' => $isdev], (array) $meta);
			}
		}

		foreach(['classes' => 'class', 'interfaces' => 'interface', 'traits' => 'trait'] as $type => $types)
		{
			if(!$struct->{$type})
			{
				continue;
			}

			foreach($struct->{$type} as $name => $meta)
			{
				${$type}[] = array_merge(['name' => $name, 'file' => $file, 'hash' => $hashreg->hash($types, array_merge((array) $meta, ['name' => $name]), $file), 'dev' => $isdev], (array) $meta);
			}
		}

		if($struct->namespaces)
		{
			foreach($struct->namespaces as $name => $meta)
			{
				$namespaces[] = array_merge(['name' => $name, 'file' => $file, 'hash' => $hashreg->hash('namespace', array_merge((array) $meta, ['name' => $name]), $file), 'dev' => $isdev], (array) $meta);
			}
		}
	}

	if(!$dottuxxedo)
	{
		IO::ul(IO::TAG_END);

		IO::text('Error: No meta information retrieved, invalid format');
		exit;
	}

	$generated_tocs = [];
	$obj_types	= ['constants', 'functions', 'classes', 'interfaces', 'traits', 'namespaces'];

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
		IO::ul(IO::TAG_END);

		IO::text('Error: No generatable elements found for table of contents');
		exit;
	}

	IO::li('Generating API pages...');

	$nscache	= [];
	$mtypes 	= [
				'constants'	=> [
							'constant', 
							'api_obj_constant'
							], 
				'properties'	=> [
							'property', 
							'api_property'
							], 
				'methods'	=> [
							'method', 
							'api_method'
							]
				];

	foreach($generated_tocs as $type)
	{
		IO::ul();
		IO::li(ucfirst($type));
		IO::ul();

		${$type . '_ptr'} 	= [];
		$ptr 			= &${$type . '_ptr'};

		foreach(${$type} as $gtype => $meta)
		{
			${$type . '_ptr'}[$gtype] = ($type == 'namespaces' ? $meta : (isset($meta['function']) ? $meta['function'] : $meta['name']));

			if($type == 'namespaces')
			{
				if(!isset($nscache[$meta['name']]))
				{
					$nscache[$meta['name']] = [
									'meta'		=> [], 
									'constants'	=> [], 
									'functions'	=> [], 
									'classes'	=> [], 
									'interfaces'	=> [], 
									'traits'	=> [], 
									'files'		=> []
									];
				}

				if(!$nscache[$meta['name']]['meta'])
				{
					$nscache[$meta['name']]['meta'] = $meta;
				}

				$nscache[$meta['name']]['files'][] = $meta['file'];
			}
			elseif(!empty($meta['namespace']))
			{
				if(!isset($nscache[$meta['namespace']]))
				{
					$nscache[$meta['namespace']] 	= [
										'meta'		=> [], 
										'constants'	=> [], 
										'functions'	=> [], 
										'classes'	=> [], 
										'interfaces'	=> [], 
										'traits'	=> [], 
										'files'		=> []
										];
				}

				foreach($namespaces as $n)
				{
					if($n['name'] == $meta['namespace'])
					{
						$nscache[$meta['namespace']]['meta'] = $n;
					}
				}

				$nscache[$meta['namespace']][$type][] = $gtype;
			}
		}

		asort(${$type . '_ptr'});

		switch($type)
		{
			case('constants'):
			{
				foreach($ptr as $const => $name)
				{
					$examples		= [];
					$meta 			= $constants[$const];

					$template 		= new Layout('api_constant');
					$template->name		= $name;
					$template->file		= $meta['file'];
					$template->datatype	= $hashlink($docblock_tag($meta, 'var'));
					$template->namespace	= $nst($meta['namespace']);
					$template->since	= $since($meta, NULL, $name, $meta['hash'], 'Global constant');
					$template->description	= $desc($meta, false, $examples);
					$template->todo		= $todo($meta);
					$template->changelog	= $changelog($meta, $name, $meta['hash']);
					$template->notices	= $notices($meta);
					$template->warnings	= $warnings($meta);
					$template->examples	= $examplecode($examples);
					$template->seealso	= $seealso($meta);

					$template->save($meta['hash']);

					IO::li($name);
				}
			}
			break;
			case('functions'):
			{
				foreach($ptr as $function => $name)
				{
					$examples		= [];
					$meta 			= $functions[$function];
					$parameters		= $returns = '';

					if(($p = $docblock_tag($meta, 'param')) !== 'Undefined value')
					{
						$ps		= sizeof($p);
						$pl		= '';
						$sep		= new Template('parameter_separator');
						$parameters 	= new Template('parameters');

						foreach($p as $pa)
						{
							if($hash = $hashlookup('class|interface|trait', $pa[0]))
							{
								$pa[0] = $link($hash . '.' . Template::$outputext, htmlspecialchars($pa[0], ENT_QUOTES));
							}
							else
							{
								$pa[0] = htmlspecialchars($pa[0], ENT_QUOTES);
							}

							$pt 			= new Template('parameter');
							$pt->datatype 		= $pa[0];
							$pt->description	= htmlspecialchars($pa[1], ENT_QUOTES);

							$pl 			.= $pt . (--$ps ? $sep : '');
						}

						$parameters->parameter_list = $pl;
					}

					if(($p = $docblock_tag($meta, 'return')) !== 'Undefined value')
					{
						$returns	= new Template('returns');
						$returns->value	= $p[1];
					}

					$template		= new Layout('api_function');
					$template->name		= $mformat($name, $type);
					$template->file		= $meta['file'];
					$template->prototype	= $prototype($name, $meta);
					$template->namespace	= $nst($meta['namespace']);
					$template->since	= $since($meta, NULL, $template->name, $meta['hash'], 'Global function');
					$template->description	= $desc($meta, false, $examples);
					$template->todo		= $todo($meta);
					$template->changelog	= $changelog($meta, $template->name, $meta['hash']);
					$template->notices	= $notices($meta);
					$template->warnings	= $warnings($meta);
					$template->examples	= $examplecode($examples);
					$template->seealso	= $seealso($meta);
					$template->parameters	= $parameters;
					$template->returns	= $returns;
					$template->throws	= $throws($meta);

					$template->save($meta['hash']);

					IO::li($name);
				}
			}
			break;
			case('classes'):
			case('interfaces'):
			case('traits'):
			{
				$rtype = ($type == 'classes' ? 'class' : ($type == 'interfaces' ? 'interface' : 'trait'));

				foreach($ptr as $obj_id => $name)
				{

					$meta 		= ${$type}[$obj_id];
					$contents 	= $extendedinfo = '';

					IO::li($name);
					IO::ul();

					foreach(['constants', 'properties', 'methods'] as $mtype)
					{
						if(!isset($mtype_singluar))
						{
							$mtype_singluar = [
										'constants'	=> 'Object constant', 
										'properties'	=> 'Property', 
										'methods'	=> 'Method'
										];
						}

						if(!$meta[$mtype])
						{
							continue;
						}

						$content 	= '';
						$mptr 		= [];

						foreach($meta[$mtype] as $m_id => $mmeta)
						{
							$mptr[$mmeta->{$mtypes[$mtype][0]}] = $m_id;
						}

						ksort($mptr);

						IO::li(ucfirst($mtype) . ':');
						IO::ul();

						foreach($mptr as $m_name => $m_id)
						{
							$examples	= [];
							$tmeta		= &$meta[$mtype][$m_id];
							$tmeta->hash	= $hashreg->hash($mtypes[$mtype][0], (array) $tmeta, $meta['file']);
							$tmeta->dev	= $meta['dev'];

							if(isset($meta['docblock']) && isset($meta['docblock']->tags) && isset($meta['docblock']->tags->wip))
							{
								$tmeta->docblock->tags->wip = true;
							}

							$template 		= new Template('obj_contents_bit');
							$template->name		= $mformat($m_name, $mtype);
							$template->link		= $tmeta->hash . '.' . Template::$outputext;
							$template->description	= $desct((array) $tmeta);

							$content		.= $template;

							$template		= new Layout($mtypes[$mtype][1]);
							$template->name		= $mformat($m_name, $mtype, $name);
							$template->file		= $meta['file'];
							$template->namespace	= $nst($meta['namespace']);
							$template->since	= $since((array) $tmeta, $meta, $template->name, $tmeta->hash, $mtype_singluar[$mtype]);
							$template->description	= $desc((array) $tmeta, false, $examples);
							$template->todo		= $todo((array) $tmeta);
							$template->changelog	= $changelog((array) $tmeta, $template->name, $tmeta->hash);
							$template->notices	= $notices((array) $tmeta);
							$template->warnings	= $warnings((array) $tmeta);
							$template->examples	= $examplecode($examples);
							$template->seealso	= $seealso((array) $tmeta);
							$template->obj		= $meta['name'];
							$template->obj_link	= $meta['hash'] . '.' . Template::$outputext;
							$template->obj_type	= ucfirst($rtype);
							$template->obj_types	= $type;

							if($mtype == 'properties' || $mtype == 'methods')
							{
								$counter 	= 0;
								$extendedinfo	= '';

								foreach(['abstract', 'final', 'static', 'protected', 'private', 'public'] as $flag)
								{
									if(!isset($tmeta->metadata->{$flag}) || !$tmeta->metadata->{$flag})
									{
										continue;
									}

									$mtemplate 		= new Template((!$counter ? 'obj_element' : 'obj_element_bit'));
									$mtemplate->value	= $flag;

									if(!$counter++)
									{
										$mtemplate->element = 'Modifiers';
									}

									$extendedinfo .= $mtemplate;
								}

								$template->extendedinfo = $extendedinfo;
							}

							switch($mtype)
							{
								case('constants'):
								case('properties'):
								{
									$template->datatype = $hashlink($docblock_tag((array) $tmeta, 'var'));
								}
								break;
								case('methods'):
								{
									$parameters = $returns = '';

									if(($p = $docblock_tag((array) $tmeta, 'param')) !== 'Undefined value')
									{
										$pl		= '';
										$ps		= sizeof($p);
										$sep		= new Template('parameter_separator');
										$parameters 	= new Template('parameters');

										foreach($p as $pa)
										{
											if($hash = $hashlookup('class|interface|trait', $pa[0]))
											{
												$pa[0] = $link($hash . '.' . Template::$outputext, htmlspecialchars($pa[0], ENT_QUOTES));
											}
											else
											{
												$pa[0] = htmlspecialchars($pa[0], ENT_QUOTES);
											}

											$pt 			= new Template('parameter');
											$pt->datatype 		= $pa[0];
											$pt->description	= htmlspecialchars($pa[1], ENT_QUOTES);

											$pl 			.= $pt . (--$ps ? $sep : '');
										}

										$parameters->parameter_list = $pl;
									}

									if(($p = $docblock_tag((array) $tmeta, 'return')) !== 'Undefined value')
									{
										$returns	= new Template('returns');
										$returns->value	= $p[1];
									}

									$template->prototype	= $prototype($m_name, (array) $tmeta);
									$template->parameters 	= $parameters;
									$template->returns	= $returns;
									$template->throws	= $throws((array) $tmeta);
								}
								break;
							}

							$template->save($tmeta->hash);

							IO::li($mformat($m_name, $mtype));
						}

						IO::ul(IO::TAG_END);

						$template 		= new Template('obj_contents');
						$template->type		= ucfirst($mtypes[$mtype][0]);
						$template->mtype	= ucfirst($mtype);
						$template->lcmtype	= strtolower($mtype);
						$template->content	= $content;

						$contents		.= $template;
					}

					IO::ul(IO::TAG_END);

					$extendedinfo		= $nst($meta['namespace']);

					$template		= new Template('obj_element');
					$template->element	= 'Declared in';
					$template->value	= $meta['file'];

					$extendedinfo		.= $template;

					if($meta['metadata']->abstract || $meta['metadata']->final)
					{
						$counter = 0;

						foreach(['abstract', 'final'] as $flag)
						{
							if(!$meta['metadata']->{$flag})
							{
								continue;
							}

							$template 		= new Template((!$counter ? 'obj_element' : 'obj_element_bit'));
							$template->value	= $flag;

							if(!$counter++)
							{
								$template->element = 'Modifiers';
							}

							$extendedinfo .= $template;
						}
					}

					if($meta['extends'])
					{
						if($hash = $hashlookup('class|interface|trait', $meta['extends']))
						{
							$href = $link($hash . '.' . Template::$outputext, $meta['extends']);
						}

						$template 		= new Template('obj_element');
						$template->element	= 'Extends';
						$template->value	= (isset($href) ? $href : $meta['extends']);

						$extendedinfo		.= $template;

						unset($href);
					}

					foreach([['implements', 'interface', 'Implements'], ['reuses', 'trait', 'Reuses']] as $mdata)
					{
						if(!$meta[$mdata[0]])
						{
							continue;
						}

						asort($meta[$mdata[0]]);

						$counter = 0;

						foreach($meta[$mdata[0]] as $metavalue)
						{
							if($hash = $hashlookup($mdata[1], $metavalue))
							{
								$href = $link($hash . '.' . Template::$outputext, $metavalue);
							}

							$template 		= new Template((!$counter ? 'obj_element' : 'obj_element_bit'));
							$template->value	= (isset($href) ? $href : $metavalue);

							if(!$counter++)
							{
								$template->element = $mdata[2];
							}

							$extendedinfo .= $template;

							unset($href);
						}
					}

					if(empty($contents))
					{
						$contents = 'None';
					}

					$examples		= [];

					$template		= new Layout('api_object');
					$template->name		= $name;
					$template->type		= ucfirst($rtype);
					$template->mtype	= $type;
					$template->since	= $since($meta, NULL, $name, $meta['hash'], ucfirst($rtype));
					$template->description	= $nl2br($desc($meta, false, $examples));
					$template->todo		= $todo($meta);
					$template->changelog	= $changelog($meta, $name, $meta['hash']);
					$template->notices	= $notices($meta);
					$template->warnings	= $warnings($meta);
					$template->examples	= $examplecode($examples);
					$template->seealso	= $seealso($meta);
					$template->contents 	= $contents;
					$template->extendedinfo	= $extendedinfo;

					$template->save($meta['hash']);
				}
			}
			break;
			case('namespaces'):
			{
				asort($nscache);

				foreach($nscache as $name => $meta)
				{
					IO::li($meta['meta']['name']);

					$counter		= 0;
					$extendedinfo		= $contents = '';
					$examples		= [];

					$nstemp 		= new Layout('api_object');
					$nstemp->name		= $meta['meta']['name'];
					$nstemp->type		= 'Namespace';
					$nstemp->mtype		= $type;
					$nstemp->file		= $meta['meta']['file'];
					$nstemp->extendedinfo	= '';
					$nstemp->since		= $since($meta['meta'], NULL, $meta['meta']['name'], $meta['meta']['hash'], 'Namespace');
					$nstemp->description	= $nl2br($desc($meta['meta'], false, $examples));
					$nstemp->todo		= $todo($meta['meta']);
					$nstemp->changelog	= $changelog($meta, $meta['meta']['name'], $meta['meta']['hash']);
					$nstemp->notices	= $notices($meta['meta']);
					$nstemp->warnings	= $warnings($meta['meta']);
					$nstemp->examples	= $examplecode($examples);
					$nstemp->seealso	= $seealso($meta);

					foreach($nscache[$meta['meta']['name']]['files'] as $file)
					{
						$template		= new Template((!$counter ? 'obj_element' : 'obj_element_bit'));
						$template->value	= $file;

						if(!$counter++)
						{
							$template->element = 'Declared in';
						}

						$extendedinfo .= $template;
					}

					$nstemp->extendedinfo = $extendedinfo;

					foreach(['constant' => 'constants', 'function' => 'functions', 'class' => 'classes', 'interface' => 'interfaces', 'trait' => 'traits'] as $single => $plural)
					{
						if(!$nscache[$meta['meta']['name']][$plural])
						{
							continue;
						}

						$ocontent	= '';

						$content 	= new Template('obj_contents');
						$content->mtype	= ucfirst($plural);
						$content->type	= ucfirst($single);

						foreach($nscache[$meta['meta']['name']][$plural] as $gtype)
						{

							$template 		= new Template('obj_contents_bit');
							$template->name		= ${$plural}[$gtype]['name'];
							$template->link		= ${$plural}[$gtype]['hash'] . '.' . Template::$outputext;
							$template->description	= $desct(${$plural}[$gtype]);

							$ocontent		.= $template;
						}

						$content->content 	= $ocontent;
						$contents 		.= $content;
					}

					$nstemp->contents = (empty($contents) ? 'None' : $contents);

					$nstemp->save($meta['meta']['hash']);
				}
			}
			break;
			default:
			{
				IO::text('Error: Unable to handle unknown type: ' . $type);
				exit;
			}
			break;
		}

		IO::ul(IO::TAG_END);
		IO::ul(IO::TAG_END);
	}

	IO::li('Generating table of contents');

	foreach($generated_tocs as $obj)
	{
		$bits		= '';
		$toc		= new Layout('toc');
		$toc->name	= ucfirst($obj);
		$toc->seealso	= new Template('toc_seealso');

		foreach(($obj == 'namespaces' ? $nscache : ${$obj . '_ptr'}) as $key => $name)
		{
			$data 			= ($obj == 'namespaces' ? $nscache[$key]['meta'] : ${$obj}[$key]);
			$name			= (is_scalar($data) ? $data : (is_array($data) && isset($data['name']) ? $data['name'] : $data['function']));

			$bit 			= new Template('toc_bit');
			$bit->link		= $data['hash'] . '.' . Template::$outputext;
			$bit->name		= $name;
			$bit->description	= $desct($data);

			$bits 			.= $bit;
		}

		$toc->toc = $bits;

		$toc->save($obj);
	}

	IO::ul();

	$descriptions	= [
				'constants'	=> 'Global constants', 
				'functions'	=> 'Procedural functions', 
				'classes'	=> 'Class synopsises', 
				'interfaces'	=> 'Interface models', 
				'traits'	=> 'Trait definitions', 
				'namespaces'	=> 'Namespace structures'
				];

	$bits		= '';
	$toc 		= new Layout('toc');
	$toc->name	= 'Table of contents';
	$toc->seealso	= '';

	foreach($generated_tocs as $gtoc)
	{
		$bit 			= new Template('toc_bit');
		$bit->link		= $gtoc . '.' . Template::$outputext;
		$bit->name		= ucfirst($gtoc);
		$bit->description	= $descriptions[$gtoc];

		$bits			.= $bit;

		IO::li($bit->name);
	}

	foreach([['Changelog', 'changelog.' . Template::$outputext, 'List of changes in previous versions'], ['News', 'news.' . Template::$outputext, 'List of introduced functionality in previous versions']] as $backlog)
	{
		$bit			= new Template('toc_bit');
		$bit->link		= $backlog[1]; 
		$bit->name		= $backlog[0];
		$bit->description	= $backlog[2];

		$bits			.= $bit;
	}

	$toc->toc = $bits;

	$toc->save('index');

	foreach([['Changelog', 'changelog', 'cl', [['News', 'news.' . Template::$outputext]]], ['News', 'news', 'news', [['Changelog', 'changelog.' . Template::$outputext]]]] as $blv)
	{
		IO::li($blv[0]);

		$template 		= new Layout('backlog');
		$template->title	= $blv[0];

		$seealso_refs		= '';

		$toc_r			= &${'toc_' . $blv[2]};

		if($blv[3])
		{
			foreach($blv[3] as $ref)
			{
				$seealso_refs .= $link($ref[1], $ref[0], 'seealso_link');
			}
		}

		if(!$toc_r)
		{
			$nt			= new Template('notice');
			$nt->notice		= 'No data found to generate this page';

			$template->toc 		= $nt;
			$template->seealso	= $seealso_refs;

			$template->save($blv[1]);

			continue;
		}

		$toc = '';

		krsort($toc_r);

		foreach($toc_r as $version => $changes)
		{
			if(!$changes)
			{
				continue;
			}

			$vt 			= new Template('backlog_version');
			$vt->change_version	= $version;
			$vth			= '';

			foreach($changes as $obj => $obj_c)
			{
				switch($blv[1])
				{
					case('changelog'):
					{
						if(!$vt->description)
						{
							$vt->description = 'Change description';
						}

						if(!$obj_c['changes'])
						{
							continue;
						}

						$checked = false;

						foreach($obj_c['changes'] as $change)
						{
							$ct = new Template('backlog_version_bit' . ($checked ? '_sep' : ''));

							if(!$checked)
							{
								$ct->element = $tags($obj_c['meta'], '') . $link($obj_c['hash'] . '.' . Template::$outputext, $obj);
							}

							$ct->value	= $change;
							$checked 	= true;

							$vth		.= $ct;
						}
					}
					break;
					case('news'):
					{
						if(!$vt->description)
						{
							$vt->description = 'Type';
						}

						$ct 		= new Template('backlog_version_bit');
						$ct->element	= $link($obj_c['hash'] . '.' . Template::$outputext, $obj_c['name']);
						$ct->value	= $tags($obj_c['meta'], $obj_c['type']);

						$vth		.= $ct;
					}
					break;
					default:
					{
						IO::text('Error: Unable to handle TOC element: \'' . $blv[1] . '\'');
						exit;
					}
					break;
				}
			}

			if(!empty($vth))
			{
				$vt->content 	= $vth;
				$toc		.= $vt;
			}
		}

		$template->toc 		= $toc;
		$template->seealso	= $seealso_refs;

		$template->save($blv[1]);
	}

	IO::ul(IO::TAG_END);
	IO::li('Copying resources');
	IO::ul();

	foreach(['style.css'] as $rsrc)
	{
		IO::li($rsrc);

		if(!$resource($rsrc))
		{
			$warns[] = 'Could not copy resource \'' . $rsrc . '\'';
		}
	}

	IO::ul(IO::TAG_END);
	IO::ul(IO::TAG_END);

	if($warns)
	{
		IO::headline('WARNING');
		IO::ul();

		foreach($warns as $w)
		{
			IO::li($w);
		}

		IO::ul(IO::TAG_END);
	}
?>