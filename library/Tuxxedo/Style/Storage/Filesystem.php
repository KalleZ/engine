<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	namespace Tuxxedo\Style\Storage;
	use Tuxxedo\Exception;
	
	/**
	 * Style storage engine for file system based templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class FileSystem extends \Tuxxedo\Style\Storage
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
	 	 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 */
		protected function __construct(Registry $registry, \Tuxxedo\Style $style, stdClass $templates)
		{
			$this->registry 		= $registry;
			$this->templates	= $templates;
			$this->path		= TUXXEDO_DIR . '/styles/' . $style['styledir'] . '/templates/';
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
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			if(!sizeof($templates))
			{
				return(false);
			}

			foreach($templates as $title)
			{
				if(($contents = @file_get_contents($this->path . $title . '.tuxx')) !== false)
				{
					$this->templates->{$title} = $contents;
				}
				else
				{
					if($error_buffer !== NULL)
					{
						$error_buffer[] = $title;
					}

					return(false);
				}
			}

			return(true);			
		}
	}
