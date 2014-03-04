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


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Debug tracker class
	 *
	 * This class can be used to log variables data for debugging 
	 * purposes, such as iterations which can be useful for writing 
	 * generators and other types of parsers to confirm states, but 
	 * it is not limited to such.
	 *
	 * <code>
	 * use Tuxxedo\Debug;
	 *
	 * class Test
	 * {
	 *		protected $iterations	 = 0;
	 *
	 *		protected $tracker;
	 *
	 *
	 *		public function __construct()
	 *		{
	 *			$this->tracker = new Debug\Tracker(function($log_data)
	 *			{
	 *				printf('The log was called %d time(s)%s%s', sizeof($log_data), PHP_EOL, PHP_EOL);
	 *
	 *				if($log_data)
	 *				{
	 *					foreach($log_data)
	 *					{
	 *						var_dump($log_data);
	 *					}
	 *				}
	 *			});
	 *		}
	 *
	 *		public function __destruct()
	 *		{
	 *			$this->tracker->end();
	 *		}
	 *
	 * 		public function iterator()
	 *		{
	 *			$this->tracker->log(['iterations' => ++$this->iterations]);
	 *		}
	 * }
	 *
	 * $test = new Test;
	 *
	 * $test->iterator(); // Test::$iterations=1
	 * $test->iterator(); // Test::$iterations=2
	 *
	 * for($x = 0; $x < 40; ++$x)
	 * {
	 *		$test->iterator(); // Test::$iterations=N
	 * }
	 *
	 * unset($test);
	 *
	 * // The log was called 42 time(s)
	 * //
	 * // ...
	 * </code>
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Tracker
	{
		/**
		 * Callback hook
		 *
		 * @var		callback
		 */
		protected $callback_hook;

		/**
		 * Log data
		 *
		 * @var		array
		 */
		protected $log_data		= [];


		/**
		 * Constructor
		 *
		 * Constructs a new tracker object
		 *
		 * @param	callback			A callback which will be used to process the log data
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception in case the callback is not valid
		 */
		public function __construct($callback)
		{
			if(!\is_callable($callback))
			{
				throw new Exception\Basic('Debug tracker callback is not valid');
			}

			$this->callback_hook = $callback;
		}

		/**
		 * Log data for a specific state/iteration
		 *	
		 * @param	array				The values to log in key-value pairs
		 * @return	void				No value is returned
		 */
		public function log(Array $log)
		{
			if(!$log)
			{
				return;
			}

			$this->log_data[] = $log;
		}

		/**
		 * Ends the tracker, ultimately calling the registered 
		 * callback which will have the stored log data passed 
		 * to it
		 *
		 * @return	void				No value is returned
		 */
		public function end()
		{
			call_user_func($this->callback_hook, $this->log_data);
		}
	}
?>