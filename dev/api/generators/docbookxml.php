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
	 * @subpckage		APIGenerators
	 *
	 * =============================================================================
	 */


	/**
	 * Docbook XML skeleton generator
	 *
	 * This class contains root functionality for generating API skeletons
	 * for the API documentation pages.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpckage		APIGenerators
	 */
	abstract class DocbookXML
	{
		protected $compiler;
		protected $file;
		protected $index	= Array();


		public function __construct($file, Array $index)
		{
			$this->file 	= $file;
			$this->api	= $index;
			$this->compiler	= new \Tuxxedo\Template\Compiler;
		}

		abstract public function process();

		public static function factory($component, $file, Array $index)
		{
			$class = 'DocBookXML_' . $component;

			if(!class_exists($class, false) || !is_subclass_of($class, __CLASS__))
			{
				die('ERROR: Undefined component handler for: ' . $component);
			}

			$renderer = new $class($file, $index);
			$renderer->process();
		}
	}

	class DocBookXML_Namespaces extends DocBookXML
	{
		public function process()
		{
			
		}
	}

	class DocBookXML_Classes extends DocBookXML
	{
		public function process()
		{
		}
	}

	class DocBookXML_Interfaces extends DocBookXML
	{
		public function process()
		{
		}
	}

	class DocBookXML_Constants extends DocBookXML
	{
		public function process()
		{
		}
	}

	class DocBookXML_Functions extends DocBookXML
	{
		public function process()
		{
		}
	}


	date_default_timezone_set('UTC');
	ini_set('html_errors', 'Off');

	define('TUXXEDO_DEBUG', 	true);
	define('TUXXEDO_DIR', 		realpath('../../..'));
	define('TUXXEDO_LIBRARY', 	realpath('../../../library'));

	define('TIMENOW_UTC', 		isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());

	require(TUXXEDO_LIBRARY . '/Tuxxedo/Loader.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions.php');
	require(TUXXEDO_LIBRARY . '/Tuxxedo/functions_debug.php');

	set_error_handler('tuxxedo_error_handler');
	set_exception_handler('tuxxedo_exception_handler');
	register_shutdown_function('tuxxedo_shutdown_handler');
	spl_autoload_register('Tuxxedo\Loader::load');

	if(function_exists('json_decode'))
	{
		$api = json_decode(file_get_contents('../dumps/json.dump'), true);
	}
	else
	{
		$api = unserialize(file_get_contents('../dumps/serialized.dump'));
	}

	if($api === false)
	{
		tuxxedo_doc_error('Unable to parse API dumps');
	}

	foreach($api as $file => $index)
	{

		if(!$index['namespaces'] && !$index['classes'] && !$index['interfaces'] && !$index['constants'] && !$index['functions'])
		{
			echo('SKIPPING: ' . $file . ' -- No implementations found<br />');

			continue;
		}

		echo('ANALYSING: ' . $file . '<br />');

		if($index['namespaces'])
		{
			DocbookXML::factory('namespaces', $file, $index);

			continue;
		}

		if($index['classes'])
		{
			DocBookXML::factory('classes', $file, $index);
		}

		if($index['interfaces'])
		{
			DocBookXML::factory('interfaces', $file, $index);
		}

		if($index['constants'])
		{
			DocBookXML::factory('constants', $file, $index);
		}

		if($index['functions'])
		{
			DocBookXML::factory('functions', $file, $index);
		}
	}
?>