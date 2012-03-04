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
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Development utilities namespace. This namespace contains various 
	 * random classes for the code in /dev/.
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools\Utilities;


	/**
	 * Alias rules
	 */
	use Tuxxedo\Version;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Input/Output writer. This class is designed to make basic scripts 
	 * work in CLI and in a browser without having to make alot of if/else 
	 * constructs.
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	class IO
	{
		/**
		 * Tag mode constant - indicates start of a tag
		 *
		 * @var		integer
		 */
		const TAG_START 	= 0;

		/**
		 * Tag mode constant - indicates end of a tag
		 *
		 * @var		integer
		 */
		const TAG_END 		= 1;

		/**
		 * Style mode constant - indicates bold text
		 *
		 * @var		integer
		 */
		const STYLE_BOLD 	= 1;

		/**
		 * Style mode constant - indicates italic text
		 *
		 * @var		integer
		 */
		const STYLE_ITALIC 	= 2;

		/**
		 * Style mode constant - indicates underlined text
		 *
		 * @var		integer
		 */
		const STYLE_UNDERLINE 	= 4;


		/**
		 * Nesting level of block elements
		 *
		 * @var		integer
		 */
		public static $depth	= 0;


		/**
		 * Checks if the script is running using a console
		 *
		 * @return	boolean				Returns true if the client is a console, otherwise false for webservers
		 */
		public static function isCli()
		{
			static $cli;

			if($cli === NULL)
			{
				$cli = (\PHP_SAPI == 'cli');
			}

			return($cli);
		}

		/**
		 * Engine signature line, this only applies in CLI mode
		 *
		 * @return	void				No value is returned
		 */
		public static function signature()
		{
			if(!self::isCli())
			{
				return;
			}

			IO::headline('Tuxxedo Engine ' . Version::FULL, 1);
		}

		/**
		 * Gets an input variable, this uses argv in CLI and $_GET in web
		 *
		 * @param	string				The argument to get
		 * @param	mixed				The default value used on error
		 * @return	string				Returns the value as a string, and false if the argument was not found
		 */
		public static function input($argument, $default = false)
		{
			if(self::isCli())
			{
				global $argv;

				$key = \array_search('-' . $argument, $argv);

				if($key === false || !isset($argv[$key + 1]))
				{
					return($default);
				}

				return((string) $argv[$key + 1]);
			}

			return((isset($_GET[$argument]) ? (string) $_GET[$argument] : $default));
		}

		/**
		 * Prints a serve error message
		 *
		 * @param	string				The error message
		 * @param	boolean				Whether or not to abort execution of the script (defaults to true)
		 * @return	void				No value is returned
		 */
		public static function error($error, $exit = true)
		{
			IO::headline('Error: ' . $error);

			if($exit)
			{
				exit;
			}
		}

		/**
		 * Writes a headline
		 *
		 * @param	string				The headline text
		 * @param	integer				The headline size (web only), for CLI this works as the number of EOL's to apply
		 * @return	void				No value is returned
		 */
		public static function headline($text, $size = 2)
		{
			if(self::isCli())
			{
				\fprintf(\STDOUT, '%s%s%s', self::eol(), $text, self::eol($size));

				return;
			}

			\printf('<h%1$d>%2$s</h%1$d>', $size, $text);
		}

		/**
		 * Starts or end writing an unordered list
		 *
		 * @param	integer				Either TAG_START or TAG_END class constants as a boolean
		 * @return	void				No value is returned
		 */
		public static function ul($mode = self::TAG_START)
		{
			if($mode == self::TAG_END)
			{
				--self::$depth;

				if(!self::isCli())
				{
					echo('</ul>');
				}
			}
			else
			{
				++self::$depth;

				if(!self::isCli())
				{
					echo('<ul>');
				}
			}
		}

		/**
		 * Writes a list item, optionally using a style
		 *
		 * @param	string				The list item text
		 * @param	integer				The style bitfield, consisting of the STYLE_XXX constants
		 * @return	void				No value is returned
		 */
		public static function li($text, $style = 0)
		{
			if(self::isCli())
			{
				\fprintf(\STDOUT, '%s* %s%s', \str_repeat(' ', self::$depth), $text, self::eol());

				return;
			}

			\printf('<li>%s</li>', self::style($text, $style));
		}

		/**
		 * Writes a text string, optionally using a style
		 *
		 * @param	string				The text
		 * @param	integer				The style bitfield, consisting of the STYLE_XXX constants
		 * @return	void				No value is returned
		 */

		public static function text($text, $style = 0)
		{
			if(self::isCli())
			{
				\fprintf(\STDOUT, '%s%s', $text, self::eol());

				return;
			}

			\printf('%s%s', self::style($text, $style), self::eol());
		}

		/**
		 * Repeats an end of line character
		 *
		 * @param	integer				The times to repeat an EOL
		 * @return	string				Returns the EOLs as a string
		 */
		public static function eol($times = 1)
		{
			static $eol;

			if(!$eol)
			{
				$eol = (self::isCli() ? \PHP_EOL : '<br />');
			}

			return(\str_repeat($eol, $times));
		}

		/**
		 * Styles a text string
		 *
		 * @param	string				The text buffer
		 * @param	integer				The style bitfield, consisting of the STYLE_XXX constants
		 * @return	string				Returns the style string
		 */
		public static function style($buffer, $style = 0)
		{
			if(self::isCli())
			{
				return($buffer);
			}

			if($style & self::STYLE_BOLD)
			{
				$buffer = \sprintf('<strong>%s</strong>', $buffer);
			}

			if($style & self::STYLE_ITALIC)
			{
				$buffer = \sprintf('<em>%s</em>', $buffer);
			}

			if($style & self::STYLE_UNDERLINE)
			{
				$buffer = \sprintf('<span style="text-decoration: underline;">%s</span>', $buffer);
			}

			return($buffer);
		}
	}
?>