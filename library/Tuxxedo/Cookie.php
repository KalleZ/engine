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
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Cookie Jar, this is a basic OO wrapper for cookies, it shares 
	 * the same globals as the session class, although they can be 
	 * overridden using this interface per 'set' call.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.1.0
	 */
	class Cookie extends Design\InfoAccess implements Design\Invokable
	{
		/**
		 * The cookie options, such as prefix, path etc.
		 *
		 * @var		array
		 */
		protected static $options	= Array(
							'expires'	=> 1800, 
							'prefix'	=> '', 
							'domain'	=> '', 
							'path'		=> '', 
							'secure'	=> false
							);


		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	\Tuxxedo\Registry	The Registry reference
		 * @param	array			The configuration array
		 * @return	object			Object instance
		 */
		public static function invoke(Registry $registry, Array $configuration = NULL)
		{
			if(!($options = $registry->options))
			{
				return;
			}

			self::$options = Array(
						'expires'	=> $options->cookie_expires, 
						'prefix'	=> $options->cookie_prefix, 
						'domain'	=> $options->cookie_domain, 
						'path'		=> $options->cookie_path, 
						'secure'	=> $options->cookie_secure
						);
		}

		/**
		 * Gets a cookie
		 *
		 * @param	string			Cookie name
		 * @param	boolean			Whether to include the cookie prefix or not, defaults to true
		 * @return	mixed			Returns the cookie value on success, or null on failure
		 */
		public static function get($name, $prefix = true)
		{
			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			if(!isset($_COOKIE[$name]))
			{
				return;
			}

			return($_COOKIE[$name]);
		}

		/**
		 * Sets a cookie
		 *
		 * @param	string			Cookie name
		 * @param	mixed			Cookie value
		 * @param	boolean			Whether to include the cookie prefix or not, defaults to true
		 * @param	array			Options, if overridding any default ones (Must contain: 'expires' (integer), 'path' (string), 'domain' (string) & 'secure' (boolean))
		 * @return	void			No value is returned
		 */
		public static function set($name, $value, $prefix = true, Array $options = NULL)
		{
			$options = ($options ? array_merge(self::$options, $options) : self::$options);

			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			$_COOKIE[$name] = $value;

			setcookie($name, $value, \TIMENOW_UTC + $options['expires'], $options['path'], $options['domain'], $options['secure'], true);
		}

		/**
		 * Checks whether a cookie is available 
		 *
		 * @param	scalar			The information row name to check
		 * @return	boolean			Returns true if the information is stored, otherwise false
		 */
		public function offsetExists($offset)
		{
			return(isset($_COOKIE[self::$options['prefix'] . $offset]));
		}

		/**
		 * Gets a cookie value
		 * 
		 * @param	scalar			The information row name to get
		 * @return	mixed			Returns the information value, and NULL if the value wasn't found
		 */
		public function offsetGet($offset)
		{
			if(isset($_COOKIE[self::$options['prefix'] . $offset]))
			{
				return($_COOKIE[self::$options['prefix'] . $offset]);
			}
		}

		/**
		 * Sets a new cookie
		 *
		 * @param	scalar			The information row name to set
		 * @param	mixed			The new/update value for this row
		 * @return	void			No value is returned
		 */
		public function offsetSet($offset, $value)
		{
			$_COOKIE[self::$options['prefix'] . $offset] = $value;
		}

		/**
		 * Deletes a cookie
		 *
		 * @param	scalar			The information row name to delete
		 * @return	void			No value is returned
		 */
		public function offsetUnset($offset)
		{
			if(isset($_COOKIE[self::$options['prefix'] . $offset]))
			{
				unset($_COOKIE[self::$options['prefix'] . $offset]);

				setcookie(self::$options['prefix'] . $offset, '', \TIMENOW_UTC - 604800, self::$options['path'], self::$options['domain'], self::$options['secure'], true);
			}
		}
	}
?>