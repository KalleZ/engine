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
	 * Event class, this allows objects to register events and let 
	 * other objects call events like a simple plugin interface.
	 *
	 *  *) Events can have multiple callbacks
	 *  *) Event hooks does not have to be declared before usage
	 *  *) Event callbacks are passed a special context object
	 *
	 * <code>
	 * use Tuxxedo\Design\Event;
	 *
	 * // A simple class that triggers an event, causing 
	 * // all registered callbacks (if any) to be executed
	 * class Test
	 * {
	 * 	public function run()
	 * 	{
	 *		// Trigger the event, we can choose to 
	 *		// expose the current object by passing 
	 *		// $this, which will populate EventContext::$extern
	 *		new Event('TestRun', $this);
	 *
	 * 		// or by calling: Event::fire(...)
	 *
	 * 		// ...
	 *	}
	 * }
	 *
	 * // Inside an object that wants to integrate with 
	 * // the 'TestRun' hook
	 *
	 * use Tuxxedo\Design\EventContext;
	 *
	 * // ...
	 * {
	 *	// ...
	 *
	 *	Event::register('TestRun', function(EventContext $ctx)
	 *	{
	 *		echo 'Event hook called';
	 * 	});
	 *
	 *	// ...
	 * }
	 *
	 * // And finally when we hit the 'run' statement, this will trigger 
	 * // the '$closure' we registered in our other object
	 *
	 * $test = new Test;
	 * $test->run(); // prints 'Event hook called'
	 * </code>
	 *
	 * Since events are all named, this means that multiple components may 
	 * share the same name.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Event
	{
		/**
		 * Holds the current registered events and their 
		 * registered callbacks
		 *
		 * @var		array
		 */
		protected static $events	= Array();


		/**
		 * Wrapper for the trigger method, this basically allows the 
		 * syntax sugar to be possible:
		 *
		 * <code>
		 * // ...
		 *
		 * new Event('name');
		 *
		 * // ...
		 * </code>
		 *
		 * @param	string			The event to trigger
		 * @param	object			The $this instance if called inside a method
		 * @param	array			Additional arguments that maybe useful for the callbacks
		 */
		public function __construct($event, $extern = NULL, Array $args = NULL)
		{
			self::fire($event, $extern, $args);
		}

		/**
		 * Registers a new callback for an event
		 *
		 * @param	string			The event name
		 * @param	callback		The callback
		 * @return	void			No value is returned
		 */
		public static function register($event, $callback)
		{
			if(!\is_callable($callback))
			{
				return;
			}

			if(!isset(self::$events[$event]))
			{
				self::$events[$event] = Array();
			}

			self::$events[$event][] = $callback;
		}

		/**
		 * Unregisters all callbacks for an event
		 *
		 * @param	string			The event name
		 * @return	void			No value is returned
		 */
		public static function unregister($event)
		{
			if(isset(self::$events[$event]))
			{
				unset(self::$events[$event]);
			}
		}

		/**
		 * Event trigger
		 *
		 * @param	string			The event to trigger
		 * @param	object			The $this instance if called inside a method
		 * @param	array			Additional arguments that maybe useful for the callbacks
		 * @return	void			No value is returned
		 */
		public static function fire($event, $extern = NULL, Array $args = NULL)
		{
			if(!isset(self::$events[$event]) || !self::$events[$event])
			{
				return;
			}

			$ctx 		= new EventContext;
			$ctx->event	= $event;
			$ctx->extern	= (\is_object($extern) ? $extern : NULL);

			if($args)
			{
				$ctx->import($args);
			}

			foreach(self::$events[$event] as $cb)
			{
				\call_user_func($cb, $ctx);
			}
		}
	}
?>