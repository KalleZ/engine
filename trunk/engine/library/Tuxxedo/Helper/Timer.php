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
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Timer helper
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Timer
	{
		/**
		 * Holds the registered timers
		 *
		 * @var		array
		 */
		protected $timers	= Array();


		/**
		 * Dummy constructor
		 *
		 * @param	\Tuxxedo\Registry		The Tuxxedo object reference
		 */
		public function __construct(Registry $registry)
		{
		}

		/**
		 * Starts a timer
		 *
		 * @param	string				The name of the timer
		 * @return	void				No value is returned
		 */
		public function start($timer)
		{
			$this->timers[$timer] = \microtime(true);
		}

		/**
		 * Stops a timer and deletes it
		 *
		 * @param	string				The name of the timer
		 * @return	float				Returns the time elapsed since the start or 0.0 on invalid timer
		 */
		public function stop($timer)
		{
			$time = $this->get($timer);

			unset($this->timers[$timer]);

			return($time);
		}

		/**
		 * Gets the time since a timer was started
		 *
		 * @param	string				The name of the timer
		 * @return	float				Returns the time elapsed since the start or 0.0 on invalid timer
		 */
		public function get($timer)
		{
			if(!isset($this->timers[$timer]))
			{
				return(0.0);
			}

			return((float) (\microtime(true) - $this->timers[$timer]));
		}
	}
?>