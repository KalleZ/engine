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
	 * Trace frame, this class is used together with the backtracing 
	 * class, each frame returned is an instance of this class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class TraceFrame
	{
		/**
		 * Flag constant - Is Exception?
		 *
		 * @var			integer
		 */
		const FLAG_EXCEPTION		= 1;

		/**
		 * Flag constant - Is Handler?
		 *
		 * @var			integer
		 */
		const FLAG_HANDLER		= 2;

		/**
		 * Flag constant - Is Callback?
		 *
		 * @var			integer
		 */
		const FLAG_CALLBACK		= 4;

		/**
		 * Flag constant - Is Include?
		 *
		 * @var			integer
		 */
		const FLAG_INCLUDE		= 8;


		/**
		 * Frame id (the number of the frame in the trace)
		 *
		 * @var			integer
		 */
		public $frame			= -1;

		/**
		 * Call, including prototype (without arguments)
		 *
		 * @var			string
		 */
		public $call			= '';

		/**
		 * Call, including prototype and argument list
		 *
		 * @var			string
		 */
		public $callargs		= '';

		/**
		 * Reflection call, this is the 'call' version thats passed as the first parameter to each Reflection instance
		 *
		 * @var			string
		 */
		public $reflection_call;

		/**
		 * Whether or not this is the 'current' frame (the one that triggered the error)
		 *
		 * @var			boolean
		 */
		public $current			= false;

		/**
		 * Line number
		 *
		 * @var			integer
		 */
		public $line			= -1;

		/**
		 * File path (full path)
		 *
		 * @var			string
		 */
		public $file			= '';

		/**
		 * Notes
		 *
		 * @var			string
		 */
		public $notes			= '';

		/**
		 * Reflection class to call when using the getReflection() method
		 *
		 * @var			string
		 */
		protected $reflection_class;

		/**
		 * Boolean flags
		 *
		 * @var			boolean
		 */
		protected $flags		= 0;


		/**
		 * Constructor
		 *
		 * Constructs a new trace frame object
		 *
		 * @param	string				The name of the reflection class to call when the  getReflection() method is called
		 * @param	integer				Checking flags, bitmask of the FLAG_XXX class constants
		 */
		public function __construct($reflection_class, $flags = 0)
		{
			$this->reflection_class = $reflection_class;
			$this->flags		= (integer) $flags;
		}

		/**
		 * Checks whether or not this frame is an exception
		 *
		 * @return	boolean				Returns true if this is an exception, otherwise false
		 */
		public function isException()
		{
			return((boolean) ($this->flags & self::FLAG_EXCEPTION));
		}

		/**
		 * Checks whether or not this frame is a handler
		 *
		 * @return	boolean				Returns true if this is a handler, otherwise false
		 */
		public function isHandler()
		{
			return((boolean) ($this->flags & self::FLAG_HANDLER));
		}

		/**
		 * Checks whether or not this frame is a callback
		 *
		 * @return	boolean				Returns true if this is a callback, otherwise false
		 */
		public function isCallback()
		{
			return((boolean) ($this->flags & self::FLAG_CALLBACK));
		}

		/**
		 * Checks whether or not this frame is an include
		 *
		 * @return	boolean				Returns true if this is an include, otherwise false
		 */
		public function isInclude()
		{
			return((boolean) ($this->flags & self::FLAG_INCLUDE));
		}

		/**
		 * Gets the boolean flags bitfield
		 *
		 * @return	integer				Returns the boolean flags for things like 'isException()', 'isInclude()', ...
		 */
		public function getFlags()
		{
			return($this->flags);
		}

		/**
		 * Gets a new reflection instance of this call
		 *
		 * @return	\Reflection			Returns a Reflection object of the call, or false for things like includes that does not have reflection
		 */
		public function getReflection()
		{
			$class = $this->reflection_class;

			if(!$class)
			{
				return(false);
			}

			return(new $class($this->reflection_call));
		}
	}
?>