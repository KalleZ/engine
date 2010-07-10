<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
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
	}

	/**
	 * Development Tools style storage, this class overrides the 
	 * default filesystem storage engine so we can define our own 
	 * template location.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
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
			$this->path		= './templates/';
		}
	}
?>