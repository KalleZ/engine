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
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * Styling API, this enables basic styling frontend for 
	 * caching templates and fetching them for execution.
	 *
	 * To compile templates thats loadable through this class 
	 * you should look at the {@link Tuxxedo_Template_Compiler} 
	 * class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Style extends InfoAccess implements Invokable
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
			$this->storage		= Style\Storage::factory($this->registry, $this, $this->registry->options->style_storage, $this->templates);
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
			$options	= $registry->cache->options;
			$styledata 	= $registry->cache->styleinfo;
			$styleid	= ($options ? (isset($registry->userinfo->id) && $registry->userinfo->style_id !== NULL && $registry->userinfo->style_id != $options['style_id'] ? $registry->userinfo->style_id : $options['style_id']) : 0);

			if($styleid && isset($styledata[$styleid]))
			{
				return(new self($styledata[$styleid]));
			}

			throw new Exception\Basic('Invalid style id, try rebuild the datastore or use the repair tools');
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array				A list of templates to load
		 * @param	array				An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean				Returns true on success otherwise false
		 *
		 * @throws	\Tuxxedo\Exception		Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			return($this->storage->cache($templates, $error_buffer));
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
	}
?>