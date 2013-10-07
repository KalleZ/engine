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
	 * Design namespace. This namespace is meant for abstract concepts and 
	 * in most cases simply just interfaces that in someway structures the 
	 * general design used in the core components.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Design;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Information access, enables the ability for classes 
	 * to access their loaded information through the array-alike 
	 * syntax.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	abstract class InfoAccess implements \ArrayAccess
	{
		/**
		 * Information event prefix, if this have a value then 
		 * events are triggered when overloading
		 *
		 * @var		string
		 * @since	1.2.0
		 */
		protected $information_event	= '';

		/**
		 * Information array
		 * 
		 * @var		array|object
		 */
		protected $information;


		/**
		 * Imports multiple information in one go
		 *
		 * @param	array|object		The information to import
		 * @return	void			No value is returned
		 *
		 * @since	1.2.0
		 */
		final public function import($information)
		{
			if(!\is_object($information) && !\is_array($information))
			{
				return;
			}

			$this->information = $information;
		}

		/**
		 * Exports the entire information in one go
		 *
		 * @return	array|object		Returns an object if the internal information is an object, otherwise an array
		 *
		 * @since	1.2.0
		 */
		public function export()
		{
			return($this->information);
		}

		/**
		 * Checks whether an information is available 
		 *
		 * @param	scalar			The information row name to check
		 * @return	boolean			Returns true if the information is stored, otherwise false
		 *
		 * @changelog	1.2.0			This method can now trigger the '{$prefix}exists' event
		 */
		public function offsetExists($offset)
		{
			if($this->information_event)
			{
				Event::fire($this->information_event . 'exists', $this, Array(
												'offset' => $offset
												));
			}

			if(\is_object($this->information))
			{
				return(isset($this->information->{$offset}));
			}

			return(isset($this->information[$offset]));
		}

		/**
		 * Gets a value from the information store
		 * 
		 * @param	scalar			The information row name to get
		 * @return	mixed			Returns the information value, and NULL if the value wasn't found
		 *
		 * @changelog	1.2.0			This method can now trigger the '{$prefix}get' event
		 */
		public function offsetGet($offset)
		{
			if($this->information_event)
			{
				Event::fire($this->information_event . 'get', $this, Array(
												'offset' => $offset
												));
			}

			if(\is_object($this->information))
			{
				return($this->information->{$offset});
			}
			else
			{
				return($this->information[$offset]);
			}
		}

		/**
		 * Sets a new information value
		 *
		 * @param	scalar			The information row name to set
		 * @param	mixed			The new/update value for this row
		 * @return	void			No value is returned
		 *
		 * @changelog	1.2.0			This method can now trigger the '{$prefix}set' event
		 */
		public function offsetSet($offset, $value)
		{
			if($this->information_event)
			{
				Event::fire($this->information_event . 'set', $this, Array(
												'offset' 	=> $offset, 
												'value'		=> $value
												));
			}

			if(\is_object($this->information))
			{
				$this->information->{$offset} = $value;
			}
			else
			{
				$this->information[$offset] = $value;
			}
		}

		/**
		 * Deletes an information value
		 *
		 * @param	scalar			The information row name to delete
		 * @return	void			No value is returned
		 *
		 * @changelog	1.2.0			This method can now trigger the '{$prefix}unset' event
		 */
		public function offsetUnset($offset)
		{
			if($this->information_event)
			{
				Event::fire($this->information_event . 'unset', $this, Array(
												'offset' => $offset
												));
			}

			if(\is_object($this->information))
			{
				unset($this->information->{$offset});
			}
			else
			{
				unset($this->information[$offset]);
			}
		}
	}
?>