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
	 * Style namespace, this contains all style related routines such as storage
	 * of templates within handlers and all loading functions. Extended template 
	 * routines are in the \Tuxxedo\Template namespace.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Style;

	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\Style;

	
	/**
	 * Interface for template storage engines
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class Storage
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Registry
		 */
		protected $registry;

		/**
		 * Reference to the template storage
		 *
		 * @var		object
		 */
		protected $templates;


		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 */
		abstract protected function __construct(Registry $registry, Style $style, \stdClass $templates);

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array			A list of templates to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		abstract public function cache(Array $templates, Array &$error_buffer = NULL);


		/**
		 * Factory method for creating a new storage engine instance
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	string			The storage engine to instanciate
		 * @param	object			Reference to the template storage object
		 * @return	object			Returns a style storage engine object reference
		 *
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception on invalid style storage engines
		 */ 
		final public static function factory(Registry $registry, Style $style, $engine, \stdClass $templates)
		{
			$class = '\Tuxxedo\Style\Storage\\' . $engine;

			if(!\class_exists($class))
			{
				throw new Exception\Basic('Invalid style storage engine specified');
			}
			elseif(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt style storage engine');
			}

			return(new $class($registry, $style, $templates));
		}
	}
?>