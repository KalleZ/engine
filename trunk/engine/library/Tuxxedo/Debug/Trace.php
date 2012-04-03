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
	 */
	namespace Tuxxedo\Debug;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Design;
	use Tuxxedo\Exception;
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
	 */
	class Trace implements Design\Invokable
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
		protected $frames	= Array();


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry	The Registry reference
		 * @param	array			The configuration array
		 * @return	void			No value is returned
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL)
		{
			if(!$configuration['application']['debug'] || !$configuration['debug']['trace'])
			{
				throw new Exception\Basic('Debug mode and tracing must be enabled before the tracing component can be loaded');
			}
		}

		/**
		 * Starts a trace
		 *
		 * @param	boolean				Whether or not to resume the old trace
		 * @return	void				No value is returned
		 */
		public function start()
		{
			$this->frames 	= \tuxxedo_debug_backtrace();
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
			$configuration = Registry::init()->getConfiguration();

			return(Array(
					'timer'		=> \round($this->timer->stop('Debug tracer'), $configuration['debug']['precision']), 
					'frames'	=> $this->frames
					));
		}
	}
?>