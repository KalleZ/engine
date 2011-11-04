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
	defined('\TUXXEDO_LIBRARY') or exit;


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
		 * Default separator for classes, this is commonly '_'
		 * for non namespaced code.
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
		 * Custom paths for third party libraries
		 *
		 * @var		array
		 */
		public static $paths		= Array();

		/**
		 * Custom match points using regular expressions
		 *
		 * @var		array
		 */
		public static $routes		= Array();


		/**
		 * Defines one or more rewrite rules for autoloading 
		 * paths
		 *
		 * @param	string|array			The class or an array of classes to define custom rules for
		 * @param	string				The class separator, defaults to a backslash
		 * @param	string				The root path to load from
		 * @return	void				No value is returned
		 */
		public static function add($path, $separator = NULL, $root = NULL)
		{
			$separator 	= ($separator !== NULL ?: self::$separator);
			$root 		= ($root !== NULL ?: self::$root);

			if(\is_array($path))
			{
				if(!$path)
				{
					return;
				}

				foreach($path as $p)
				{
					self::$paths[$p] = Array(
									'separator'	=> $separator, 
									'root'		=> $root
									);
				}
			}
			else
			{
				self::$paths[$path] = Array(
								'separator'	=> $separator, 
								'root'		=> $root
								);
			}
		}

		/**
		 * Defines one or more rewrite rules for autoloading 
		 * paths using PCRE
		 *
		 * @param	string				The regular expression to match (without delimiters and modifiers)
		 * @param	string				The matching formatting, including separators if any
		 * @return	void				No value is returned
		 */
		public static function route($regex, $replacement)
		{
			if(\is_array($regex) && \is_array($replacement))
			{
				if(!$regex || !$replacements || ($length = \sizeof($regex)) != \sizeof($replacement))
				{
					return;
				}

				for($n = 0; $n < $length; ++$n)
				{
					self::$routes[$regex[$n]] = $replacement[$n];
				}
			}
			else
			{
				self::$routes[$regex] = $replacement;
			}
		}

		/**
		 * Normalizes a class name into a path
		 *
		 * @param	string				The class to convert
		 * @return	string				Returns the matching path
		 */
		public static function getNormalizedPath($name, &$regex_match = NULL)
		{
			$regex_match = false;

			if(self::$paths && isset(self::$paths[$name]))
			{
				if(\strpos($name, self::$paths[$name]['separator']) !== false)
				{
					$name = \str_replace(self::$paths[$name]['separator'], '/', $name);
				}

				return(self::$paths[$name]['root'] . '/' . $name . '.php');
			}
			elseif(self::$routes)
			{
				foreach(self::$routes as $regex => $replacement)
				{
					$match = \preg_replace('#' . $regex . '#Ui', $replacement, $name);

					if($match && $match !== $name)
					{
						$name 		= $match;
						$regex_match 	= $match;

						break;
					}
				}
			}

			if(\strpos($name, self::$separator) !== false)
			{
				$name = \str_replace(self::$separator, '/', $name);
			}

			if($name{0} == '/')
			{
				return(self::$root . $name . '.php');
			}

			return(self::$root . '/' . $name . '.php');
		}

		/**
		 * Autoloads a class, if a class fails to load, the error handler is called 
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
				class_alias($regex_match, $name);
			}

			if(self::exists($name))
			{
				return(true);
			}
			elseif(!is_file($path))
			{
				if($silent)
				{
					return(false);
				}

				if(self::exists('\Tuxxedo\Exception\Basic'))
				{
					throw new Exception\Basic('Unable to find object file for \'%s\' (assumed to be: \'%s\')', $name, $path);
				}

				\tuxxedo_doc_errorf('Unable to find object file for \'%s\' (assumed to be: \'%s\')', $name, \str_replace(Array('\\', '/'), \DIRECTORY_SEPARATOR, $path));
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

				\tuxxedo_doc_errorf('Object mismatch, class or interface (\'%s\') not found within the resolved file (\'%s\')', $name, \str_replace(Array('\\', '/'), \DIRECTORY_SEPARATOR, $path));
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