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
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Event Handler class, this is the internal firing mechanism 
	 * that works together with the EventCaller trait.
	 *
	 * <code>
	 * use Tuxxedo\Design;
	 *
	 * class Component
	 * {
	 *	// Trait which implements the hooks for 
	 *	// event registrations
	 *	use Design\EventCaller;
	 *
	 *
	 *	// Holds the event handler instance
	 * 	protected $event_handler;
	 *
	 *
	 * 	// Constructor, this loads in the event handling sub
	 * 	// system and registers the events
	 * 	public function __construct()
	 *	{
	 *		$this->setEventHandler($this->event_handler = new Design\EventHandler($this, ['test1', 'test2']));
	 *	}
	 *
	 *	// Method test #1
	 *	public function test1()
	 *	{
	 *		$this->event_handler->fire('test1');
	 *	}
	 *
	 *	// Method test #2
	 *	public function test2()
	 *	{
	 *		$this->event_handler->fire('test2', func_get_args());
	 *	}
	 * }
	 *
	 * // Create the new component instance
	 * $component = new Component;
	 *
	 * // Register callbacks for the events
	 * $component->onTest1 = function(Design\EventContext $ctx)
	 * {
	 *	echo 'Event ' . $ctx->event . ' called', PHP_EOL;
	 * };
	 *
	 * $component->onTest2 = function(Design\EventContext $ctx)
	 * {
	 *	echo 'Event ' . $ctx->event . ' called', PHP_EOL;
	 *	echo 'Additional arguments passed to this callback:', PHP_EOL;
	 *
	 * 	var_dump($ctx->args);
	 * };
	 *
	 * // Execute
	 * $component->test1();
	 * $component->test2('Hello', 'World');
	 * </code>
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class EventHandler
	{
		/**
		 * The object instance associated with this event 
		 * handler. Note that this can be NULL as its not 
		 * required.
		 *
		 * @var		object|null
		 */
		protected $obj_ptr;

		/**
		 * The registered events to this event handler
		 *
		 * @var		array
		 */
		protected $events		= [];

		/**
		 * The registered callbacks for this event handler
		 *
		 * @var		array
		 */
		protected $event_callbacks	= [];


		/**
		 * Constructs a new event handler object
		 *
		 * @param	object|null			The object for which this event handler is registered to (or NULL if none)
		 * @param	array				A list of events, this must contain at least one event, all event names are case insensitive
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the object supplied is not an object or NULL and if no events are passed
		 */
		public function __construct($obj_ptr, Array $events)
		{
			if($obj_ptr !== NULL && !\is_object($obj_ptr))
			{
				throw new Exception\Basic('Invalid object supplied to the event handler');
			}
			elseif(!$events)
			{
				throw new Exception\Basic('The event handler must contain at least one event');
			}

			if($obj_ptr)
			{
				$this->obj_ptr = $obj_ptr;
			}

			$this->events = \array_unique(\array_map('\strtolower', $events));
		}

		/**
		 * Registers a new callback for an event
		 *
		 * @param	string				The event name
		 * @param	callback			The callback to register
		 * @return	void				No value is returned
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if a callback is attempted to be registered for an invalid event
		 */
		public function register($event, $callback)
		{
			if(!\in_array($event, $this->events))
			{
				throw new Exception\Basic('Trying to register an invalid event callback');
			}

			if(!isset($this->event_callbacks[$event]))
			{
				$this->event_callbacks[$event] = [];
			}

			$this->event_callbacks[$event][] = $callback;
		}

		/**
		 * Unregisters all callbacks for a specific event
		 *
		 * @param	string				The event name
		 */
		public function unregister($event)
		{
			if(!\in_array($event, $this->events) || !isset($this->event_callbacks[$event]))
			{
				return;
			}

			unset($this->event_callbacks[$event]);
		}

		/**
		 * Gets all the callbacks for a specific event
		 *
		 * @return	array			Returns an array with all the callbacks for a specific event, this array can be empty, false is returned for invalid events
		 */
		public function getCallbacks($event)
		{
			if(!\in_array($event, $this->events))
			{
				return(false);
			}

			return((isset($this->event_callbacks[$event]) ? $this->event_callbacks[$event] : []));
		}

		/**
		 * Gets all the events valid for this event handler
		 *
		 * @return	array			Returns an array with all the events for this event handler
		 */
		public function getEvents()
		{
			return($this->events);
		}

		/**
		 * Fires an event
		 *
		 * Be aware that event callbacks may throw exceptions
		 *
		 * @param	string			The event name
		 * @param	array			Additional arguments that should be passed to the event callbacks using the context object
		 * @return	void			No value is returned
		 */
		public function fire($event, Array $args = NULL)
		{
			if(!\in_array($event, $this->events) || !isset($this->event_callbacks[$event]))
			{
				return;
			}

			$ctx 		= new EventContext;
			$ctx->event	= $event;

			if($this->obj_ptr)
			{
				$ctx->extern = $this->obj_ptr;
			}

			if($args)
			{
				$ctx->args = $args;
			}

			foreach($this->event_callbacks[$event] as $callback)
			{
				\call_user_func($callback, $ctx);
			}
		}
	}
?>