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
	 * @package		DevTools
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Development Tools styling class, this class overrides the 
	 * default styling class so we can overload the default style 
	 * storage engines and use our own.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	class Tuxxedo_Dev_Style extends Tuxxedo_Style
	{
		/**
		 * Constructs a new style object
		 */
		public function __construct()
		{
			global $tuxxedo;

			$this->tuxxedo		= $tuxxedo;
			$this->information 	= Array();
			$this->templates	= new stdClass;
			$this->storage		= Tuxxedo_Style_Storage::factory($tuxxedo, $this, 'DevTools', $this->templates);
		}


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	void			No value is returned
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
		}

		/**
		 * Gets a sidebar widget template
		 *
		 * @return	boolean			Returns the template contents on success and boolean false on failure
		 */
		public function getSidebarWidget()
		{
			$widget = 'widget_' . SCRIPT_NAME;

			if(!$this->storage->exists($widget))
			{
				return(false);
			}

			$this->cache(Array($widget));

			return($this->fetch($widget));
		}
	}

	/**
	 * Development Tools style storage, this class overrides the 
	 * default filesystem storage engine so we can define our own 
	 * template location.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	class Tuxxedo_Style_Storage_DevTools extends Tuxxedo_Style_Storage_Filesystem
	{
		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 */
		protected function __construct(Tuxxedo $tuxxedo, Tuxxedo_Style $style, stdClass $templates)
		{
			$this->tuxxedo 		= $tuxxedo;
			$this->templates	= $templates;
			$this->path		= './style/templates/';
		}

		/**
		 * Checks whether a template file exists on the file system
		 *
		 * @param	string			The name of the template to check
		 * @return	boolean			Returns true if the template file exists otherwise false
		 */
		public function exists($template)
		{
			return(is_file($this->path . $template . '.tuxx'));
		}
	}
?>