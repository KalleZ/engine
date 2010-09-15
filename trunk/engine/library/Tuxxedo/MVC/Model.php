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
	 * MVC (Model-View-Controller) namespace, this contains all the base 
	 * implementation of each of the building bricks and extensions for 
	 * extending them even further.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	namespace Tuxxedo\MVC;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Model class
	 *
	 * In a MVC application this would be extended to create logical models,
	 * that are populated with data from another source (e.g. a datamanager).
	 *
	 * This class implements a few methods that make writing models less
	 * tedious, without having to write the same methods for each model.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		MVC
	 */
	class Model
	{
		/**
		 * The methods to map
		 *
		 * @var		array
		 */
		protected $methods	= Array();


		/**
		 * Re-indexes the mapper
		 *
		 * @return	void			No value is returned
		 */
		protected function remap()
		{
			if($this instanceof Model\Mapper)
			{
				$this->methods = \get_class_method($this->getMapper());
			}
		}

		/**
		 * Caller method, this emulates the defined method mappings
		 *
		 * @param	string			The method to call
		 * @param	array			The method arguments if any
		 * @return	mixed			Returns the value of the mapped method
		 */
		public function __call($method, Array $arguments)
		{
			$method = \strtolower($method);

			if($this->methods && \in_array($method, $this->methods))
			{
				return(\call_user_func_array(Array($this, $this->methods[$method]), $arguments));
			}

			$prefix 	= \substr($method, 0, 3);
			$property	= \substr($method, 3);

			if(empty($property) || !isset($this->{$property}))
			{
				return;
			}
			elseif($prefix == 'set')
			{
				$this->{$property} = ($arguments ? $arguments[0] : NULL);
			}
			else
			{
				return($this->{$property});
			}
		}
	}
?>