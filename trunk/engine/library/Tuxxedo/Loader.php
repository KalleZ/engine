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
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


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
		 * Default separator for classes, this is mainly is '_'
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
		 * Normalizes a class name into a path
		 *
		 * @var		string				The class to convert
		 * @return	string				Returns the matching path
		 */
		public static function getNormalizedPath($name)
		{
			if(isset(self::$paths[$name]))
			{
				if(\strpos($name, self::$paths[$name]['separator']) !== false)
				{
					$name = \str_replace(self::$paths[$name]['separator'], '/', $name);
				}

				return(self::$paths[$name]['root'] . '/' . $name . '.php');
			}

			if(\strpos($name, self::$separator) !== false)
			{
				$name = \str_replace(self::$separator, '/', $name);
			}

			return(self::$root . '/' . $name . '.php');
		}

		/**
		 * Autoloads a class, if a class fails to load, the error handler is called 
		 * directly and the error is shown. Technically this cannot throw any exceptions 
		 * due it may result in a recursive loop. This however can be bypassed by 
		 * calling this function manually with the silent error handler set to true.
		 *
		 * @param	string				The class to autoloader
		 * @param	boolean				Whether to return true or false in case of loading instead of calling the error handler
		 * @return	void				No value is returned
		 */
		public static function load($name, $silent = false)
		{
			$path = self::getNormalizedPath($name);

			if(!is_file($path))
			{
				if($silent)
				{
					return(false);
				}

				\tuxxedo_doc_errorf('Unable to find object file for \'%s\' (assumed to be: \'%s\')', $name, $path);
			}

			require($path);

			if(!\class_exists($name) && !\interface_exists($name))
			{
				if($silent)
				{
					return(false);
				}

				\tuxxedo_doc_errorf('Object mismatch, class or interface (\'%s\') not found within the resolved file (\'%s\')', $name, $path);
			}

			return(true);
		}
	}
?>