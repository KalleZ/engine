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
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


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
		const TAG_START = 1;
		const TAG_END = 2;

		const STYLE_BOLD = 1;
		const STYLE_ITALIC = 2;
		const STYLE_UNDERLINE = 4;


		public static function isCli()
		{
			static $cli;

			if(!$cli)
			{
				$cli = (PHP_SAPI == 'cli');
			}

			return($cli);
		}

		public static function headline($text)
		{
			if(self::isCli())
			{
				fprintf(STDOUT, '%s%s', $text, self::eol(2));

				return;
			}

			printf('<h2>%s</h2>', $text);
		}

		public static function ul($mode)
		{
			if(self::isCli())
			{
				return;
			}

			if($mode == self::TAG_END)
			{
				echo('</ul>');
			}
			else
			{
				echo('<ul>');
			}
		}

		public static function li($text, $style = 0)
		{
			if(self::isCli())
			{
				fprintf(STDOUT, ' * %s%s', $text, self::eol(1));

				return;
			}

			printf('<li>%s</li>', self::style($text, $style));
		}

		public static function text($text, $style = 0)
		{
			if(self::isCli())
			{
				fprintf(STDOUT, '%s', $text, self::eol(1));

				return;
			}

			printf('%s', self::style($text, $style));
		}

		public static function eol($times)
		{
			if(self::isCli())
			{
				return(str_repeat(PHP_EOL, $times));
			}

			return(str_repeat('<br />', $times));
		}

		protected static function style($buffer, $style)
		{
			if($style & self::STYLE_BOLD)
			{
				$buffer = sprintf('<strong>%s</strong>', $buffer);
			}

			if($style & self::STYLE_ITALIC)
			{
				$buffer = sprintf('<em>%s</em>', $buffer);
			}

			if($style & self::STYLE_UNDERLINE)
			{
				$buffer = sprintf('<span style="text-decoration: underline;">%s</span>', $buffer);
			}

			return($buffer);
		}
	}
?>