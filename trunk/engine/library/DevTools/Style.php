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
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Developmental Tools namespace. This namespace is for all development 
	 * tool related routines, as used by /dev/tools.
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;
	use Tuxxedo\Style\Storage;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


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
			$this->templates	= new \stdClass;
			$this->information 	= Array(
							'templatesdir' => './style/templates/'
							);

			$this->storage		= Storage::factory($this->registry, $this, 'filesystem', $this->templates, true);
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
		 * @param	boolean			Reference to check whether a hook was called or not
		 * @return	boolean			Returns the template contents on success and boolean false on failure
		 */
		public function getSidebarWidget(&$hook_called = false)
		{
			global $configuration;

			$script = ($configuration['devtools']['protective'] && !Registry::init()->session['devtools_authenticated'] ? 'index' : \SCRIPT_NAME);
			$widget = 'widget_' . $script;

			if(!$this->cache(Array($widget)))
			{
				return(false);
			}
			elseif(\function_exists('\widget_hook_' . $script))
			{
				$hook_called = true;

				return(\call_user_func('\widget_hook_' . $script, $this, $this->registry, $widget));
			}

			return($this->fetch($widget));
		}
	}
?>