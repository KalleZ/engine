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


	/**
	 * Development namespace. This contains all the various additional 
	 * that the development tools uses to hack into Engine and alter 
	 * components for bypassing certain design limits to hook in various 
	 * places
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace Tuxxedo\Development;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


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
	class Style extends \Tuxxedo\Style
	{
		/**
		 * Constructs a new style object
		 */
		public function __construct()
		{
			$this->registry		= Registry::init();
			$this->information 	= Array();
			$this->templates	= new \stdClass;
			$this->storage		= \Tuxxedo\Style\Storage::factory($this->registry, $this, 'DevTools', $this->templates);
		}


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry	The Registry reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	void			No value is returned
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL, Array $options = NULL)
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
?>