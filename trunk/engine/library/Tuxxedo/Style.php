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
	use Tuxxedo\Template;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Styling API, this enables basic styling frontend for 
	 * caching templates and fetching them for execution.
	 *
	 * To compile templates thats loadable through this class 
	 * you should look at the template compiler class.
	 * class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Style extends Design\InfoAccess implements Design\Invokable
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		\Tuxxedo\Registry
		 */
		protected $registry;

		/**
		 * Holds the current loaded templates
		 *
		 * @var		array
		 */
		protected $templates;


		/**
		 * Constructs a new style object
		 *
		 * @param	array				The style data to use
		 */
		public function __construct(Array $styleinfo)
		{
			$this->registry		= Registry::init();
			$this->information 	= $styleinfo;
			$this->templates	= new \stdClass;

			$this->storage		= Style\Storage::factory($this->registry, $this, (isset($styleinfo['storage']) ? $styleinfo['storage'] : $this->registry->options->style_storage), $this->templates);
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				The configuration array
		 * @return	object				Object instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if an invalid (or not cached) style id was used
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL)
		{
			$styleid = ($registry->options ? (isset($registry->userinfo->id) && $registry->userinfo->style_id !== NULL && $registry->userinfo->style_id != $registry->options->style_id ? $registry->userinfo->style_id : $registry->options->style_id) : 0);

			if(isset($registry->datastore->styleinfo[$styleid]))
			{
				return(new self($registry->datastore->styleinfo[$styleid]));
			}

			throw new Exception\Basic('Invalid style id');
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array				A list of templates to load
		 * @param	array				An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean				Returns true on success otherwise false
		 *
		 * @throws	\Tuxxedo\Exception\SQL		Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			return($this->storage->cache($templates, $error_buffer));
		}

		/**
		 * Checks if a template is loaded
		 *
		 * @param	string				The name of the template
		 * @return	boolean				Returns true if the template is loaded, otherwise false
		 *
		 * @since	1.1.0
		 */
		public function isLoaded($template)
		{
			return(isset($this->templates->{$template}));
		}

		/**
		 * Fetches a cached template
		 *
		 * @param	string				The name of the template to fetch
		 * @return	string				Returns the compiled template code for execution, and boolean false on error
		 */
		public function fetch($template)
		{
			$template = strtolower($template);

			if(!isset($this->templates->{$template}))
			{
				return(false);
			}

			return($this->templates->{$template});
		}

		/**
		 * Fetches a catched template and returns it as a template object
		 *
		 * @param	string				The name of the template to fetch
		 * @param	boolean				Whether to activate the layout mode option of the template object
		 * @return	\Tuxxedo\Template		Returns a template object containing the template
		 *
		 * @since	1.1.0
		 */
		public function template($template, $layout = false)
		{
			$template = strtolower($template);

			if(!isset($this->templates->{$template}))
			{
				return(false);
			}

			return(new Template($template, $layout));
		}

		/**
		 * Unloads a template from current memory
		 *
		 * @param	string|array			The name of the template(s) to remove from the cache
		 * @return	boolean				Returns true on success and false on error
		 *
		 * @since	1.1.0
		 */
		public function unload($list)
		{
			if(!$list)
			{
				return(false);
			}

			if(\is_array($list))
			{
				foreach($list as $template)
				{
					if(isset($this->templates->{$template}))
					{
						unset($this->templates->{$template});
					}
				}

				return(true);
			}
			elseif(!isset($this->templates->{$list}))
			{
				return(false);
			}

			unset($this->templates->{$list});

			return(true);
		}
	}
?>