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
	 * Helper namespace, this namespace is for standard helpers that comes 
	 * with Engine.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Helper;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\InfoAccess;
	use Tuxxedo\Helper\Pagination\Page;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Pagination helper
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Pagination extends InfoAccess implements \Iterator, \Countable
	{
		/**
		 * Iterator instance
		 *
		 * @var		\Iterator
		 */
		protected $iterator;

		/**
		 * Iterator pointer position
		 *
		 * @var		integer
		 */
		protected $pointer		= 0;

		/**
		 * First page, always static
		 *
		 * @var		integer
		 */
		protected $first_page		= 1;

		/**
		 * Last page, generated by valid()
		 *
		 * @var		integer
		 */
		protected $last_page;

		/**
		 * Next page start limit, generated by valid()
		 *
		 * @var		integer
		 */
		protected $calculated_limit;


		/**
		 * Dummy constructor
		 *
	 	 * @param	\Tuxxedo\Registry		The Tuxxedo object reference
		 */
		public function __construct(Registry $registry)
		{
		}

		/**
		 * Sets a new iterator
		 *
		 * @param	\Iterator			The iterator to use for the pagination
		 * @return	void				No value is returned
		 */
		public function setIterator(\Iterator $iterator)
		{
			$this->iterator = $iterator;
		}

		/**
		 * Gets the inner iterator
		 *
		 * @return	\Iterator			Returns the inner iterator
		 */
		public function getIterator()
		{
			return($this->iterator);
		}

		/**
		 * Checks if its possible to iterate and if a page 
	 	 * is valid
		 *
		 * @return	boolean				Returns true if the page is valid, otherwise false
		 */
		public function valid()
		{
			if(!$this->iterator || !isset($this->information['page']) || !isset($this->information['pages']) || !isset($this->information['limit']) || !$this->information['page'] || !$this->information['limit'] || !($size = \sizeof($this->iterator)) || $this->pointer == $this->information['limit'])
			{
				return(false);
			}

			$this->last_page	= (integer) \ceil($size / $this->information['limit']);
			$this->calculated_limit = (integer) \ceil($this->information['page'] * $this->information['limit']);

			return(true);
		}

		/**
		 * Advances the inner iterator pointer
		 *
		 * @return	void				No value is returned
		 */
		public function next()
		{
			++$this->pointer;
		}

		/**
		 * Gets the current page object
		 *
		 * @return	\Tuxxedo\Helper\Pagination\Page	Returns a page object for the current page
		 */
		public function current()
		{
			return(new Page($this->pointer, $this->information));
		}

		/**
		 * Returns the index for the current position
		 *
		 * @return	integer				Returns the current index
		 */
		public function key()
		{
			return($this->pointer);
		}

		/**
		 * Rewinds the inner pointer position
		 *
		 * @return	void				No value is returned
		 */
		public function rewind()
		{
			$this->pointer = 0;
		}

		/**
		 * Counts the number of rows within the inner iterator
		 *
		 * @return	integer				Returns the number of rows within the inner iterator, 0 on error
		 */
		public function count()
		{
			return(($this->iterator ? \sizeof($this->iterator) : 0));
		}

		/**
		 * Gets the current page limit, requires the validity check 
		 * to be executed first
		 *
		 * @return	integer				Returns the current page limit
		 */
		public function getPageLimit()
		{
			return(($this->calculated_limit !== NULL ? $this->calculated_limit : 0));
		}
	}
?>