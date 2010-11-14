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
	 * Helper loading interface
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class Helper
	{
		/**
		 * Contains the loaded helpers
		 *
		 * @var		array
		 */
		protected static $loaded_helpers	= Array()


		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	\Tuxxedo\Registry		The Tuxxedo object reference
		 */
		abstract public function __construct(Registry $registry);

		/**
		 * Factory method for loading a new helper
		 *
		 * When loading multiple helpers and set them to register in the registry, then 
		 * the last registered one will override the old one in the registry
		 *
		 * @param	\Tuxxedo\Registry		The Tuxxedo object reference
		 * @param	string				The helper handle to instanciate
		 * @param	string				Whether to register this helper in the registry
		 * @param	boolean				Whether this is a custom storage engine
		 * @return	object				Returns a helper handle object reference
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception on invalid helpers
		 */ 
		final public static function factory(Registry $registry, $helper, $register = true, $custom = false)
		{
			$class = (!$custom ? '\Tuxxedo\Helper\\' : '') . ucfirst($helper);

			if(isset(self::$loaded_helpers[$helper]))
			{
				$ref = new $class($registry);

				if($register)
				{
					$registry->register($helper, $ref);
				}

				return($ref);
			}
			elseif(!\class_exists($class))
			{
				throw new Exception\Basic('Invalid helper handle specified');
			}
			elseif(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt helper handle');
			}

			self::$loaded_helpers[$helper] 	= true;
			$ref 				= new $class($registry);

			if($register)
			{
				$registry->register($helper, $ref);
			}

			return($ref);
		}
	}
?>