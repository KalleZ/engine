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
	 * Debug namespace, this namespace contains debugging related routines that 
	 * is better suited to be encapsulated in an object.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	namespace Tuxxedo\Debug;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Helper;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Trace class, this records traces about calls priority to 
	 * executing the upcoming code block and calculates the time 
	 * it took to execute
	 *
	 * Example:
	 * <code>
	 * $trace->start();
	 *
	 * // Code block
	 *
	 * $traceinfo = $trace->end();
	 * </code>
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Trace
	{
		/**
		 * Timer
		 *
		 * @var		\Tuxxedo\Helper\Timer
		 */
		protected $timer;

		/**
		 * Stack frames
		 *
		 * @var		array
		 */
		protected $frames	= [];


		/**
		 * Starts a trace
		 *
		 * @param	boolean				Whether or not to resume the old trace
		 * @return	void				No value is returned
		 */
		public function start()
		{
			$this->frames 	= new Backtrace;
			$this->timer	= Helper::factory('timer');

			$this->timer->start('Debug tracer');
		}

		/**
		 * Ends a trace
		 *
		 * @return	array				Returns the timer and trace frames for the last trace
		 */
		public function end()
		{
			return([
					'timer'		=> \round($this->timer->stop('Debug tracer'), Registry::init()->getConfiguration()['debug']['precision']), 
					'frames'	=> $this->frames
					]);
		}
	}
?>