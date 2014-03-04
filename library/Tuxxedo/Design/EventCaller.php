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
	 * Implements event calling hooks for a class
	 *
	 * Note, if the class that reuses this trait defines its own magic 
	 * '__get' and '__set' methods, then they must be called 
	 * manually to continuesly allow the event callbacks to be assigned.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	trait EventCaller
	{
		/**
		 * The event handler object
		 *
		 * @var		\Tuxxedo\Design\EventHandler
		 */
		private $eh_ptr;


		/**
		 * Defines the event handler instance for usage within 
		 * the event caller trait
		 *
		 * @param	\Tuxxedo\Design\EventHandler	The event handler instance
		 * @return	void				No value is returned
		 */
		final protected function setEventHandler(EventHandler $event_handler)
		{
			$this->eh_ptr = $event_handler;
		}

		/**
		 * Gets the event handler instance currently assigned to this event caller
		 * trait
		 *
		 * @return	\Tuxxedo\Design\EventHandler	Returns the event handler instance if any, otherwise false
		 */
		final protected function getEventHandler()
		{
			return(($this->eh_ptr ? $this->eh_ptr : false));
		}

		/**
		 * Magic setter to implement 'onEvent' alike syntax
		 *
		 * @param	string				The property name
		 * @param	mixed				The property value
		 * @return	void				No value is returned
		 */
		public function __set($property, $value)
		{
			$prop = \substr($lprop = \strtolower($property), 2);

			if(\substr($lprop, 0, 2) == 'on')
			{
				if(\is_callable($value))
				{
					$this->eh_ptr->register($prop, $value);
				}
				else
				{
					$this->eh_ptr->unregister($prop);
				}
			}
		}

		/**
		 * Magic getter to implement 'onEvent' alike syntax
		 *
		 * @param	string				The property name
		 * @return	mixed				Returns all the callbacks (if any) registered to this event
		 */
		public function __get($property)
		{
			$prop = \substr($lprop = \strtolower($property), 2);

			if(\substr($lprop, 0, 2) == 'on')
			{
				return($this->eh_ptr->getCallbacks($prop));
			}
		}
	}
?>