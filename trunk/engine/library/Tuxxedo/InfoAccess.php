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
	 *
	 * =============================================================================
	 */


	/**
	 * Core engine namespace, standard exceptions are integrated within this 
	 * part of the namespace, functions that previously were procedural is 
	 * defined as static classes.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	namespace Tuxxedo;

	/**
	 * Information access, enables the ability for classes 
	 * to access their loaded information through the array-alike 
	 * syntax.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	abstract class InfoAccess implements \ArrayAccess
	{
		/**
		 * Information array
		 * 
		 * @var		array
		 */
		protected $information		= Array();


		/**
		 * Checks whether an information is available 
		 *
		 * @param	scalar			The information row name to check
		 * @return	boolean			Returns true if the information is stored, otherwise false
		 */
		public function offsetExists($offset)
		{
			if(is_object($this->information))
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
		 */
		public function offsetGet($offset)
		{
			if(is_object($this->information))
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
		 */
		public function offsetSet($offset, $value)
		{
			if(is_object($this->information))
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
		 */
		public function offsetUnset($offset)
		{
			if(is_object($this->information))
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