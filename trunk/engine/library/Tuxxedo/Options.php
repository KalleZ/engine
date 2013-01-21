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


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Options class, allows the options registry entry to allow 
	 * usage of options as virtual properties and array access as 
	 * option categories, so that its possible to pass namespaced 
	 * options to objects.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Options extends Design\InfoAccess implements Design\Invokable
	{
		/**
		 * Holds the options, this is a reference to 
		 * the datastore
		 *
		 * @var		array
		 */
		protected $options;


		/**
		 * Constructs the options class
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 */
		public function __construct(Registry $registry)
		{
			$categories = Array();

			$registry->datastore->getRef('options', $this->options);
			$registry->datastore->getRef('optioncategories', $categories);

			if($categories)
			{
				foreach($categories as $category)
				{
					$this->information[$category] = new \stdClass;
				}

				if($this->options)
				{
					foreach($this->options as $option => $info)
					{
						$this->information[$info['category']]->{$option} = $info['value'];
					}
				}
			}
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				The configuration array
		 * @return	object				Object instance
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if neither the 'options' and 'optioncategories' datastore is loaded
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL)
		{
			return(new self($registry));
		}

		/**
		 * Gets an option
		 *
		 * @param	string				The name of the option
		 * @return	mixed				Returns the option value, and null on invalid options
		 */
		public function __get($option)
		{
			if(!isset($this->options[$option]))
			{
				return;
			}

			return($this->options[$option]['value']);
		}
	}
?>