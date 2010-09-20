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
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\Version;


	/**
	 * Exception handler, this terminates the script execution 
	 * if an exception is fatal and buffer non fatal exceptions 
	 * so they can be displayed on the template
	 *
	 * @param	   Exception		   The exception to handle
	 * @return	  void			No value is returned
	 */
	function tuxxedo_exception_handler(\Exception $e)
	{
		static $registry;

		if(!$registry)
		{
			$registry = class_exists('\Tuxxedo\Registry', false);
		}

		if($e instanceof Exception\Basic)
		{
			tuxxedo_doc_error($e);
		}
		elseif($e instanceof Exception)
		{
			tuxxedo_gui_error($e->getMessage());
		}

		if($registry && Registry::globals('error_reporting'))
		{
			$errors = (array) Registry::globals('errors');

			array_push($errors, $e->getMessage());

			Registry::globals('errors', $errors);
		}
		else
		{
			echo('<strong>Exception:</strong> ' . $e->getMessage() . '<br /> <br />');
		}
	}

	/**
	 * Error handler, this handles general errors from php. If 
	 * the script should error non fatal errors such as warnings 
	 * or notices, it will add them to the error buffer and show 
	 * then on the main template output. Note that this function is 
	 * not designed to be called directly and should be called by 
	 * php itself
	 *
	 * @param	   integer		 Error level
	 * @param	   string		  Error message
	 * @param	   string		  File
	 * @param	   integer		 Line number
	 * @return	void			No value is returned
	 *
	 * @throws	  Tuxxedo_Basic_Exception Throws a basic exception on fatal error types
	 */
	function tuxxedo_error_handler($level, $message, $file = NULL, $line = NULL)
	{
		static $registry;

		if(!$registry)
		{
			$registry = class_exists('\Tuxxedo\Registry', false);
		}

		if($registry && !Registry::globals('error_reporting') || !(error_reporting() & $level))
		{
			return;
		}

		if($level & E_RECOVERABLE_ERROR)
		{
			tuxxedo_doc_error('<strong>Recoverable error:</strong> ' . $message);
		}
		elseif($level & E_USER_ERROR)
		{
			tuxxedo_doc_error('<strong>Fatal error:</strong> ' . $message);
		}
		elseif($level & E_NOTICE || $level & E_USER_NOTICE)
		{
			$message = '<strong>Notice:</strong> ' . $message;
		}
		elseif($level & E_DEPRECATED || $level & E_USER_DEPRECATED)
		{
			$message = '<strong>Deprecated:</strong> ' . $message;
		}
		elseif($level & E_STRICT)
		{
			$message = '<strong>Strict standards:</strong> ' . $message;
		}
		else
		{
			$message = '<strong>Warning:</strong> ' . $message;
		}

		if($file !== NULL && $line !== NULL)
		{
			$message .= ' in ' . tuxxedo_trim_path($file) . ' on line ' . $line;
		}

		if($registry)
		{
			$errors = (array) Registry::globals('errors');

			array_push($errors, $message);

			Registry::globals('errors', $errors);
		}
		else
		{
			echo($message . '<br /> <br />');
		}
	}

	/**
	 * Print a document error (startup) and halts script execution
	 *
	 * @param	   string		  The message to show
	 * @return	  void			No value is returned
	 */
	function tuxxedo_doc_error($e)
	{
		static $called;
		global $registry, $configuration;

		if($called !== NULL)
		{
			return;
		}

		$called		= true;
		$buffer		= ob_get_clean();
		$exception	= ($e instanceof \Exception);
		$exception_sql	= $exception && $registry->db && $e instanceof Exception\SQL;
		$utf8		= function_exists('utf8_encode');
		$message	= ($exception ? $e->getMessage() : (string) $e);
		$errors		= ($registry ? Registry::globals('errors') : false);
		$application	= ($configuration['application']['name'] ? $configuration['application']['name'] . ($configuration['application']['version'] ? ' ' . $configuration['application']['version'] : '') : false);

		if(empty($message))
		{
			$message = 'Unknown error occured!';
		}
		elseif($exception_sql)
		{
			$message = (TUXXEDO_DEBUG ? str_replace(Array("\r", "\n"), '', $e->getMessage()) : 'An error occured while querying the database');
		}
		elseif($utf8)
		{
			$message = utf8_encode($message);
		}

		if(TUXXEDO_DEBUG && $errors && $registry)
		{
			$message .= '<ul>' . PHP_EOL;

			foreach($errors as $error)
			{
				$message .= '<li>' . htmlentities(!$utf8 ?: utf8_encode($error)) . '</li>';
			}

			$message .= '</ul>' . PHP_EOL;

			Registry::globals('errors', Array());
		}

		header('Content-Type: text/html');

		echo(
			'<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . 
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL . 
			'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . PHP_EOL . 
			'<head>' . PHP_EOL . 
			'<title>Tuxxedo Software Engine Error</title>' . PHP_EOL . 
			'<style type="text/css">' . PHP_EOL . 
			'body { background-color: #021420; color: #3B7286; font-family: "Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif; font-size: 82%; padding: 0px 30px; }' . PHP_EOL . 
			'h1 { color: #FFFFFF; }' . PHP_EOL . 
			'table tr.head td { background-color: #D2D2D2; padding: 5px; }' . PHP_EOL . 
			'table tr.row, table tr.row * { margin: 0px; padding: 5px; }' . PHP_EOL . 
			'table tr.strong * { font-weight: bold; }' . PHP_EOL . 
			'.box { background-color: #D2D2D2; border: 3px solid #D2D2D2; border-radius: 4px; }' . PHP_EOL . 
			'.box .inner { background-color: #FFFFFF; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'.box .outer { padding: 6px; }' . PHP_EOL . 
			'.infobox { background-color: #D2D2D2; border: 3px solid #D2D2D2; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'.infobox td { padding-right: 5px; }' . PHP_EOL . 
			'.infobox td.value { background-color: #FFFFFF; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'.spacer { margin-bottom: 10px; }' . PHP_EOL . 
			'</style>' . PHP_EOL .  
			'</head>' . PHP_EOL . 
			'<body>' . PHP_EOL . 
			(TUXXEDO_DEBUG && $buffer ? strip_tags($buffer) . PHP_EOL : '') . 
			'<h1>Tuxxedo Engine Error</h1>' . PHP_EOL
			);

		if(TUXXEDO_DEBUG)
		{
			echo(
				'<div class="box">' . PHP_EOL . 
				'<div class="inner">' . PHP_EOL
				);

			if($exception && $e instanceof Exception\Core)
			{
				echo(
					'<div class="infobox spacer">' . PHP_EOL . 
					'<strong>This is a critical error and should only occur in development releases!</strong>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);
			}

			echo(
				'<div class="infobox" style="float: left; width: 400px;">' . PHP_EOL . 
				'<table cellspacing="2" cellpadding="0">' . PHP_EOL
				);

			if($application)
			{
				echo(
					'<tr>' . PHP_EOL . 
					'<td>Application:</td>' . PHP_EOL . 
					'<td class="value" width="100%">' . $application . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);
			}

			echo(
				'<tr>' . PHP_EOL . 
				'<td nowrap="nowrap">Engine Version:</td>' . PHP_EOL . 
				'<td class="value" width="100%">' . Version::SIMPLE . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL .  
				'<tr>' . PHP_EOL . 
				'<td>Script:</td>' . PHP_EOL . 
				'<td class="value" nowrap="nowrap">' . realpath($_SERVER['SCRIPT_FILENAME']) . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL
				);

			if(($date = tuxxedo_date(NULL, 'H:i:s j/n - Y (e)')))
			{
				echo(
					'<tr>' . PHP_EOL . 
					'<td>Timestamp:</td>' . PHP_EOL . 
					'<td class="value">' . $date . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);
			}

			if($exception)
			{
				$class = get_class($e);

				if($class{0} != '\\')
				{
					$class = '\\' . $class;
				}

				echo(
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Exception Type:</td>' . PHP_EOL . 
					'<td class="value">' . $class . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);
			}

			if($exception_sql)
			{
				echo(
					'<tr>' . PHP_EOL . 
					'<td colspan="2">&nbsp;</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL . 
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Database Driver:</td>' . PHP_EOL . 
					'<td class="value" width="100%">' . constant(get_class($registry->db) . '::DRIVER_NAME') . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL . 
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Error code:</td>' . PHP_EOL . 
					'<td class="value" width="100%">' . $e->getCode() . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				if(($sqlstate = $e->getSQLState()) !== false)
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td nowrap="nowrap">SQL State:</td>' . PHP_EOL . 
						'<td class="value" width="100%">' . $sqlstate . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}
			}

			echo(
				'</table>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'<div style="margin: 10px 10px 10px 430px;">' . PHP_EOL . 
				nl2br($message) . PHP_EOL
				);

			if($exception_sql)
			{
				echo(
					'<br /> <br />' . PHP_EOL . 
					'<strong>SQL Query:</strong>' . PHP_EOL . 
					'<div class="box">' . PHP_EOL . 
					'<em>' . str_replace(Array("\r", "\n"), '', $e->getSQL()) . '</em>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);
			}

			echo(
				'</div>' . PHP_EOL . 
				'<div style="clear: left;"></div>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'</div>' . PHP_EOL
				);

			$bt = ($exception ? tuxxedo_debug_backtrace($e) : tuxxedo_debug_backtrace());

			if($bts = sizeof($bt))
			{
				echo(
					'<h1>Debug backtrace</h1>' . PHP_EOL . 
					'<div class="box">' . PHP_EOL . 
					'<div class="inner">' . PHP_EOL . 
					'<table width="100%" cellspacing="0" cellpadding="0">' . PHP_EOL . 
					'<tr class="head">' . PHP_EOL . 
					'<td>&nbsp;</td>' . PHP_EOL . 
					'<td class="strong">Call</td>' . PHP_EOL . 
					'<td class="strong">File</td>' . PHP_EOL . 
					'<td class="strong">Line</td>' . PHP_EOL . 
					'<td class="strong">Notes</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				foreach($bt as $n => $trace)
				{
					echo(
						'<tr class="' . ($trace->current ? 'strong ' : '') . 'row">' . PHP_EOL . 
						'<td><h3>' . ++$n . '</h3></td>' . PHP_EOL . 
						'<td nowrap="nowrap">' . $trace->call . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap" width="100%">' . $trace->file . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap">' . $trace->line . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap">' . $trace->notes . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				echo(
					'</table>' . PHP_EOL . 
					'</div>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);
			}

			if($registry && $registry->db && $registry->db->getNumQueries())
			{
				echo(
					'<h1>Executed SQL queries</h1>' . PHP_EOL . 
					'<div class="box">' . PHP_EOL . 
					'<div class="inner">' . PHP_EOL . 
					'<table width="100%" cellspacing="0" cellpadding="0">' . PHP_EOL . 
					'<tr class="head">' . PHP_EOL . 
					'<td width="100">&nbsp;</td>' . PHP_EOL . 
					'<td class="strong" width="100%">SQL</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				foreach($registry->db->getQueries() as $n => $sql)
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td><h3>' . ++$n . '</h3></td>' . PHP_EOL . 
						'<td><code>' . $sql . '</code></td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				echo(
					'</table>' . PHP_EOL . 
					'</div>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);
			}
		}
		else
		{
			echo(
				'<div class="box">' . PHP_EOL . 
				'<div class="inner">' . PHP_EOL . 
				nl2br($message) .  PHP_EOL . 
				'</div>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'<p>' . PHP_EOL . 
				'<em>' . 
				'This error was generated by ' . ($application ? $application . ' (' : '') . 'Tuxxedo Engine ' . Version::SIMPLE . ($application ? ')' : '') . 
				'</em>' . PHP_EOL . 
				'</p>'
				);
		}

		die(
			'</body>' . PHP_EOL . 
			'</html>'
			);
	}

	/**
	 * Formattable doc error
	 *
	 * @param	string			The error message, in a printf-alike formatted string or just a normal string
	 * @param	mixed			Optional argument #n for formatting
	 * @return	Void			No value is returned
	 */
	function tuxxedo_doc_errorf()
	{
		if(!func_num_args())
		{
			tuxxedo_doc_error('Unknown error');
		}

		tuxxedo_doc_error(call_user_func_array('sprintf', func_get_args()));
	}

	/**
	 * Trims a file path to hide its path prior to the root 
	 * of the application
	 *
	 * @param	   string		  The path to trim
	 * @param	   boolean		 Should the path also be trimmed if debug mode is on? Defaults to true
	 * @return	  string		  The trimmed path
	 */
	function tuxxedo_trim_path($path, $debug_trim = true)
	{
		if(!$debug_trim && TUXXEDO_DEBUG)
		{
			return($path);
		}

		if(empty($path))
		{
			return('');
		}

		if(strpos($path, '/') !== false || strpos($path, '\\') !== false || strpos($path, TUXXEDO_DIR) !== false)
		{
			$path = str_replace(Array('/', '\\', TUXXEDO_DIR), DIRECTORY_SEPARATOR, $path);
		}

		return(ltrim($path, DIRECTORY_SEPARATOR));
	}

	/**
	 * Shutdown handler
	 *
	 * @return	  void			No value is returned
	 */
	function tuxxedo_shutdown_handler()
	{
		static $registry;

		if(!$registry)
		{
			$registry = class_exists('\Tuxxedo\Registry', false);
		}

		$errors = ($registry ? Registry::globals('errors') : false);

		if(!$registry || !TUXXEDO_DEBUG || !$errors)
		{
			return;
		}

		global $registry;

		$buffer = '<br />' . implode($errors, '<br />');

		Registry::globals('errors', Array());

		if(!$registry->style)
		{
			tuxxedo_doc_error($buffer);
		}
		else
		{
			$output = ob_get_clean();

			if($pos = stripos($output, '</body>'))
			{
				$output = substr_replace($output, $buffer . '</body>', $pos, 7);
			}
			else
			{
				$output .= '<br />' . $buffer;
			}

			echo($output);
		}
	}

	/**
	 * Handles multiple errors repeatingly
	 *
	 * @param	   string		  A sprintf-like format
	 * @param	   array		   An array with elements to loop through
	 * @return	  void			No value is returned
	 *
	 * @throws	  Tuxxedo_Basic_Exception Throws a basic exception until the errors have been cleared
	 */
	function tuxxedo_multi_error($format, Array $elements)
	{
		if(!$elements)
		{
			return;
		}

		throw new Exception\Basic($format, reset($elements));
	}

	/**
	 * Issues a redirect and terminates the script
	 *
	 * @param	   string		  The message to show to the user while redirecting
	 * @param	   string		  The redirect location
	 * @param	   string		  Redirect timeout in seconds
	 * @return	  void			No value is returned
	 */
	function tuxxedo_redirect($message, $location, $timeout = 3)
	{
		eval(page('redirect'));
		exit;
	}

	/**
	 * Issues a redirect using headers and then terminates the script
	 *
	 * @param	   string		  The redirect location
	 * @return	  void			No value is returned
	 */
	function tuxxedo_header_redirect($location)
	{
		header('Location: ' . $location);
		exit;
	}

	/**
	 * Prints an error message using the current loaded 
	 * theme and then terminates the script
	 *
	 * @param	   string		  The error message
	 * @param	   boolean		 Whether to show the 'Go back' button or not
	 * @return	  void			No value is returned
	 */
	function tuxxedo_gui_error($message, $goback = true)
	{
		eval(page('error'));
		exit;
	}

	/**
	 * Date format function
	 *
	 * @param	   integer		 The timestamp to format
	 * @param	   string		  Optional format to use, defaults to the format defined within the options
	 * @return	  string		  Returns the formatted date
	 */
	function tuxxedo_date($timestamp = NULL, $format = NULL)
	{
		global $registry;

		if($timestamp === NULL)
		{
			$timestamp = (defined('TIMENOW') ? TIMENOW : TIMENOW_UTC);
		}

		if($format === NULL)
		{
			$format = $registry->cache->options['date_format'];
		}

		if(!$registry || !$registry->datetime)
		{
			return(date($format, $timestamp));
		}

		$old_timestamp = $registry->datetime->getTimestamp();

		$registry->datetime->setTimestamp($timestamp);
		$format = $registry->datetime->format($format);
		$registry->datetime->setTimestamp($old_timestamp);

		return($format);
	}

	/**
	 * Generates code to print a page
	 *
	 * @param	   string		  The template name to print
	 * @return	  void			No value is returned
	 */
	function page($template)
	{
		global $registry;

		return(
			'global $header, $footer;' . 
			'echo("' . $registry->style->fetch($template) . '");'
			);
	}

	/**
	 * Email validation, check if a supplied email 
	 * is written with a correct syntax.
	 *
	 * This function is based on code by:
	 * Alexander Meesters <admin@budgetwebhosting.nl>
	 *
	 * @param	   string		  The email address to validate
	 * @return	  boolean		 Returns true if the email is valid, otherwise false
	 */
	function is_valid_email($email)
	{
		static $have_filter_ext;

		if($have_filter_ext === NULL)
		{
			$have_filter_ext = extension_loaded('filter');
		}

		if($have_filter_ext)
		{
			return((boolean) filter_var($email, FILTER_VALIDATE_EMAIL));
		}

		if(!preg_match('/[^@]{1,64}@[^@]{1,255}/', $email))
		{
			return(false);
		}

		$email_array	= explode('@', $email);
		$local_array	= explode('.', $email_array[0]);
		$local_length   = sizeof($local_array);

		for($i = 0; $i < $local_length; ++$i)
		{
			if(!preg_match('£(([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-][A-Za-z0-9!#$%&\'*+/=?^_`{|}~\.-]{0,63})|("[^(\\|")]{0,62}"))£', $local_array[$i]))
			{
				return(false);
			}
		}

		if(!preg_match('@\[?[0-9\.]+\]?@', $email_array[1]))
		{
			$domain_array = explode('.', $email_array[1]);

			if(sizeof($domain_array) < 2)
			{
				return(false);
			}

			for($i = 0; $i < sizeof($domain_array); ++$i)
			{
				if(!preg_match('@(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))@', $domain_array[$i]))
				{
					return(false);
				}
			}
		}

		return(true);
	}
?>