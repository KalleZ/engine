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


	namespace Tuxxedo\Style\Storage;

	use Tuxxedo\Registry;
	use Tuxxedo\Style\Storage;

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
	class DevTools extends Storage\Filesystem
	{
		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 */
		protected function __construct(Registry $registry, \Tuxxedo\Style $style, \stdClass $templates)
		{
			$this->tuxxedo 		= $registry;
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
			return(\is_file($this->path . $template . '.tuxx'));
		}
	}
?>