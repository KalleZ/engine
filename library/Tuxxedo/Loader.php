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
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Autoloader handler
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Loader
	{
		/**
		 * Loader mode - Default
		 *
		 * @var		integer
		 */
		const MODE_DEFAULT		= 1;

		/**
		 * Loader mode - PSR-0
		 *
		 * @var		integer
		 */
		const MODE_PSR0			= 2;


		/**
		 * Loader mode
		 *
		 * @var		integer
		 */
		public static $mode		= self::MODE_DEFAULT;

		/**
		 * Default separator for classes, this is commonly '_'
		 * for non namespaced code. Separators may have different 
		 * meaning depending on the loader modes.
		 *
		 * This value is ignored in the following loader modes:
		 *
		 * PSR-0
		 *
		 * @var		string
		 */
		public static $separator	= '\\';

		/**
		 * Default root to load from, defaults to the library 
		 * path
		 *
		 * @var		string
		 */
		public static $root		= \TUXXEDO_LIBRARY;

		/**
		 * Custom routing definitions
		 *
		 * @var		array
		 */
		protected static $routes	= Array(
							'path'		=> Array(), 
							'regex'		=> Array(), 
							'callback'	=> Array()
							);


		/**
		 * Defines which loader mode to use
		 *
		 * @param	integer				The loader mode; one of the MODE_* class constants
		 * @return	void				No value is returned
		 */
		public static function mode($new)
		{
			self::$mode = (integer) $new;
		}

		/**
		 * Defines one or more rewrite rules for autoloading 
		 * paths
		 *
		 * @param	string				The class to define custom rules for
		 * @param	string				The class separator, defaults to a backslash
		 * @param	string				The root path to load from
		 * @return	void				No value is returned
		 */
		public static function routeAsPath($path, $separator = NULL, $root = NULL)
		{
			self::$routes['path'][$path] = Array(
								'separator'	=> ($separator !== NULL ? $separator : self::$separator), 
								'root'		=> ($root !== NULL ? $root : self::$root)
								);
		}

		/**
		 * Defines one or more rewrite rules for autoloading 
		 * paths using PCRE
		 *
		 * @param	string				The regular expression to match (without delimiters and modifiers)
		 * @param	string				The matching formatting, including separators if any
		 * @return	void				No value is returned
		 */
		public static function routeAsRegex($regex, $replacement)
		{
			self::$routes['regex'][$regex] = $replacement;
		}

		/**
		 * Defines a callback for routing, this can be used to virtually alias 
		 * or similar in siturations where the other routing implementations 
		 * simply cannot match
		 *
		 * @param	string				The matching part, this can be a full name or a partial string
		 * @param	callback			The callback to route to
		 * @return	void				No value is returned
		 */
		public static function routeAsCallback($match, $callback)
		{
			if(\is_callable($callback))
			{
				self::$routes['callback'][$match] = $callback;
			}
		}
	
		/**
		 * Normalizes a class/interface name into a path
		 *
		 * @param	string				The class/interface to convert
		 * @return	string				Returns the matching path
		 */
		public static function getNormalizedPath($name, &$regex_match = NULL)
		{
			$regex_match = false;

			if(self::$routes['path'] && isset(self::$routes['path'][$name]))
			{
				if(\strpos($name, self::$routes['path'][$name]['separator']) !== false)
				{
					$name = \str_replace(self::$routes['path'][$name]['separator'], \DIRECTORY_SEPARATOR, $name);
				}

				return(self::$routes['path'][$name]['root'] . \DIRECTORY_SEPARATOR . $name . '.php');
			}

			if(self::$routes['regex'])
			{
				foreach(self::$routes['regex'] as $regex => $replacement)
				{
					$match = \preg_replace($regex, $replacement, $name);

					if($match && $match !== $name)
					{
						$name = $regex_match = $match;

						break;
					}
				}
			}

			if(self::$routes['callback'])
			{
				foreach(self::$routes['callback'] as $match => $callback)
				{
					$path = NULL;

					if(strpos($name, $match) !== false && \call_user_func($callback, $name, $path))
					{
						if($path !== NULL)
						{
							return($path);
						}

						break;
					}
				}
			}

			switch(self::$mode)
			{
				case(self::MODE_PSR0):
				{
					if(\strpos($name, self::$separator) !== false)
					{
						$ptr 	= \strrpos($name, '\\');
						$name	= \str_replace('\\', \DIRECTORY_SEPARATOR, \substr($name, 0, $ptr) . \str_replace('_', \DIRECTORY_SEPARATOR, \substr($name, $ptr)));
					}
				}
				break;
				default:
				{
					if(\strpos($name, self::$separator) !== false)
					{
						$name = \str_replace(self::$separator, \DIRECTORY_SEPARATOR, $name);
					}
				}
				break;
			}

			if($name{0} == \DIRECTORY_SEPARATOR)
			{
				return(self::$root . $name . '.php');
			}

			return(self::$root . \DIRECTORY_SEPARATOR . $name . '.php');
		}

		/**
		 * Autoloads a class or interface, if the class or interface fails to load, the error handler is called 
		 * directly and the error is shown.
		 *
		 * @param	string				The class or interface to autoload
		 * @param	boolean				Whether to return true or false in case of loading instead of calling the error handler
		 * @return	boolean				Returns true if loaded, false otherwise if loading failed (latter is only true, if the $silent parameter is set to true)
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if its loaded into runtime, else falls back to a standard error call
		 */
		public static function load($name, $silent = false)
		{
			$regex_match 	= false;
			$path 		= self::getNormalizedPath($name, $regex_match);

			if($regex_match)
			{
				\class_alias($regex_match, $name);
			}

			if(self::exists($name))
			{
				return(true);
			}
			elseif(!\is_file($path))
			{
				if($silent)
				{
					return(false);
				}

				if(self::exists('\Tuxxedo\Exception\Basic'))
				{
					throw new Exception\Basic('Unable to find object file for \'%s\' (assumed to be: \'%s\')', $name, \tuxxedo_trim_path($path));
				}

				\tuxxedo_errorf('Unable to find object file for \'%s\' (assumed to be: \'%s\')', $name, \str_replace(Array('\\', '/'), \DIRECTORY_SEPARATOR, \tuxxedo_trim_path($path)));
			}

			require($path);

			if(!self::exists($name))
			{
				if($silent)
				{
					return(false);
				}

				if(self::exists('\Tuxxedo\Exception\Basic'))
				{
					throw new Exception\Basic('Object mismatch, class or interface (\'%s\') not found within the resolved file (\'%s\')', $name, $path);
				}

				\tuxxedo_errorf('Object mismatch, class or interface (\'%s\') not found within the resolved file (\'%s\')', $name, \str_replace(Array('\\', '/'), \DIRECTORY_SEPARATOR, $path));
			}

			return(true);
		}

		/**
		 * Check whether a class or interface exists without attempting to autoload them
		 *
		 * @param	string				The class or interface to check
		 * @return	boolean				True if exists and false otherwise
		 */
		public static function exists($name)
		{
			return(\class_exists($name, false) || \interface_exists($name, false));
		}
	}
?>