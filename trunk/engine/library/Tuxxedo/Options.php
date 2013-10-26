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
	use Tuxxedo\Datamanager;
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
	 * @since		1.2.0
	 */
	class Options extends Design\InfoAccess implements Design\Invokable
	{
		/**
		 * Boolean flag to for the saving method
		 *
		 * @var		boolean
		 */
		protected $changed			= false;

		/**
		 * Holds the options, this is a reference to 
		 * the datastore
		 *
		 * @var		array
		 */
		protected $options			= Array();

		/**
		 * Holds a list of options and their categories
		 *
		 * @var		array
		 */
		protected $categories			= Array();


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
						$this->categories[$option] 				= $info['category'];
						$this->information[$info['category']]->{$option} 	= &$this->options[$option]['value'];
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

		/**
		 * Sets an option
		 *
		 * @param	string				The name of the option
		 * @param	mixed				The value of the option
		 * @return	void				Returns the old option value, and null on error
		 */
		public function __set($option, $value)
		{
			if(!isset($this->options[$option]))
			{
				return;
			}

			$old_value 		= $this->options[$option];
			$this->changed		= true;
			$this->options[$option] = Array(
							'category'	=> $this->categories[$option], 
							'value'		=> $value
							);
			return($old_value);
		}

		/**
		 * Saves the current options within the datastore
		 *
		 * This is useful for when the options are updated here on the fly to reflect changes directly in the code
		 *
		 * @return	boolean				Returns true if the options were saved with success (or if nothing was changed), otherwise false
		 */
		public function save()
		{
			if(!$this->changed)
			{
				return(true);
			}

			$dm 		= Datamanager\Adapter::factory('datastore', 'options');
			$dm['data']	= $this->options;

			$retval 	= $dm->save();

			if($retval)
			{
				$this->changed = false;
			}

			return($retval);
		}
	}
?>