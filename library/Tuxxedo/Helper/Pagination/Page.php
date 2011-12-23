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
	 * Pagination helper namespace, this is for utilities related to the 
	 * pagination object to ease use of it.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Helper\Pagination;


	/**
	 * Alias rules
	 */
	use Tuxxedo\Helper\Pagination;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Page pagination class
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Page
	{
		/**
		 * Iterator pointer from the associated pagination 
		 * object
		 *
		 * @var		integer
		 */
		protected $pointer;

		/**
		 * The pagination object
		 *
		 * @var		\Tuxxedo\Helper\Pagination
		 */
		protected $pagination;


		/**
		 * Constructor, constructs a new page object
		 *
		 * @param	\Tuxxedo\Helper\Pagination	The pagination object
		 */
		public function __construct(Pagination $pagination)
		{
			$this->pointer 	= \key($pagination);
			$this->instance	= $pagination;
		}

		/**
		 * Gets the current page number
		 *
		 * @return	integer				Returns the current page number (as a string)
		 */
		public function __toString()
		{
			return((string) $this->pointer);
		}

		/**
		 * Checks if this is the first page over all
		 *
		 * @return	boolean				Returns true if the page is the first page, otherwise false
		 */
		public function isFirst()
		{
		}

		/**
		 * Checks if this is the last page over all
		 *
		 * @return	boolean			
		 */
		public function isLast()
		{
		}

		/**
		 * Gets the current page number
		 *
		 * @return	integer				Returns the current page number
		 */
		public function getCurrent()
		{
			return($this->pointer);
		}

		/**
		 * Checks if the current page b()eing iterated is 
		 * the current one (the value of $pagination['page'])
		 *
		 * @return	boolean				Returns true if the page is the current, otherwise false
		 */
		public function isCurrent()
		{
		}

		/**
		 * Checks if this is a 'previous' page (to the left of the current)
		 *
		 * @return	boolean				Returns true if this is a 'previous' page, otherwise false
		 */
		public function isPrevious()
		{
		}

		/**
		 * Checks if this is a 'next' page (to the right of the current)
		 *
		 * @return	boolean				Returns true if this is a 'next' page, otherwise false
		 */
		public function isNext()
		{
		}
	}
?>