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
	 * Style storage namespace, this is for handlers that can load templates via 
	 * different backends such as file system or database. All must extend the 
	 * \Tuxxedo\Style\Storage class.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Style\Storage;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\Style;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Style storage engine for file system based templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Filesystem extends Style\Storage
	{
		/**
		 * Directory where the compiled templates are saved
		 *
		 * @var		string
		 */
		protected $path;


		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	\Tuxxedo\Registry	The Tuxxedo object reference
		 * @param	\Tuxxedo\Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 *
		 * @changelog	1.1.0			The 'templatesdir' style table row can now be used to define a custom template directory if present
		 */
		protected function __construct(Registry $registry, Style $style, \stdClass $templates)
		{
			$this->registry 	= $registry;
			$this->style		= $style;
			$this->templates	= $templates;
			$this->path		= (isset($style['templatesdir']) ? $style['templatesdir'] : \TUXXEDO_DIR . '/styles/' . $style['styledir'] . '/templates/');
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array			A list of templates to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @param	array			An array passed by reference, this contains all the elements that where loaded if referenced
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	\Tuxxedo\Exception\SQL	Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			if(!$templates)
			{
				return(false);
			}

			$loaded = Array();

			foreach($templates as $title)
			{
				$dottuxx = $this->path . $title . '.tuxx';

				if(\is_file($dottuxx) && ($contents = @\file_get_contents($dottuxx)) !== false)
				{
					$loaded[]			= $title;
					$this->templates->{$title} 	= $contents;
				}
			}

			if(($diff = \array_diff($templates, $loaded)))
			{
				if($error_buffer !== NULL)
				{
					$error_buffer = $diff;
				}

				return(false);
			}

			return(true);			
		}
	}
?>