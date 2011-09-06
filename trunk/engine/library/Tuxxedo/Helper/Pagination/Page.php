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
	 * Include check
	 */
	defined('\TUXXEDO_LIBRARY') or exit;


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
		 * Options array from the associated pagination 
		 * object
		 *
		 * @var		array
		 */
		protected $options		= Array();


		/**
		 * Constructor, constructs a new page object
		 *
		 * @param	integer			The pointer from the pagination object
		 * @param	array			The options from the pagination object
		 */
		public function __construct($pointer, Array $options)
		{
			$this->pointer 	= $pointer;
			$this->options	= $options;
		}

		public function __toString()
		{
			return((string) 0);
		}

		/**
		 * Checks if the current page is the first page over all
		 *
		 * @return	boolean			Returns true if the page is the first page, otherwise false
		 */
		public function isFirst()
		{
		}

		public function isLast()
		{
		}

		public function getCurrent()
		{
		}

		/**
		 * Checks if the current page being iterated is 
		 * the current one (the value of $pagination['page'])
		 *
		 * @return	boolean			Returns true if the page is the current, otherwise false
		 */
		public function isCurrent()
		{
		}

		public function isPrevious()
		{
		}

		public function isNext()
		{
		}
	}
?>