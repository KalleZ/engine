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
	 * Aliasing rules
	 */
	use Tuxxedo\Debug;
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\Template;
	use Tuxxedo\Utilities;
	use Tuxxedo\Version;


	/**
	 * Exception handler, this terminates the script execution 
	 * if an exception is fatal and buffer non fatal exceptions 
	 * so they can be displayed on the template
	 *
	 * @param	\Exception			The exception to handle
	 * @return	void				No value is returned
	 *
	 * @changelog	1.2.0				This function is now CLI compatible
	 */
	function tuxxedo_exception_handler(\Exception $e)
	{
		static $error_handler;

		if(!$error_handler)
		{
			$error_handler = (PHP_SAPI == 'cli' ? 'tuxxedo_cli_error' : 'tuxxedo_doc_error');
		}

		if($e instanceof Exception\Basic)
		{
			tuxxedo_basic_error($e);
		}
		elseif($e instanceof Exception\Multi)
		{
			$template 		= new Template\Layout('error');
			$template['message']	= htmlspecialchars($e->getMessage(), ENT_QUOTES);

			if(($errors = $e->getData()))
			{
				$list = '';

				foreach($errors as $error)
				{
					$bit 		= new Template('error_listbit');
					$bit['error']	= $error;

					$list		.= $bit;
				}

				$template['error_list'] = $list;
			}

			echo($template);
			exit;
		}
		elseif($e instanceof Exception)
		{
			$template 		= new Template\Layout('error');
			$template['message']	= htmlspecialchars($e->getMessage(), ENT_QUOTES);

			echo($template);
			exit;
		}

		if(Registry::globals('error_reporting'))
		{
			$errors = (array) Registry::globals('errors');

			array_push($errors, $e->getMessage());

			Registry::globals('errors', $errors);
		}
		else
		{
			echo((PHP_SAPI == 'cli' ? '<strong>Exception:</strong> ' : 'Exception: ') . htmlentities($e->getMessage()) . '<br /> <br />');
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
	 * @param	integer				Error level
	 * @param	string				Error message
	 * @param	string				File path
	 * @param	integer				Line number
	 * @return	void				No value is returned
	 *
	 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception on fatal error types
	 *
	 * @changelog	1.2.0				This function is now CLI compatible
	 */
	function tuxxedo_error_handler($level, $message, $file = NULL, $line = NULL)
	{
		static $error_handler, $cli_mode;

		if(!Registry::globals('error_reporting') || !(error_reporting() & $level))
		{
			return;
		}

		if(!$error_handler)
		{
			$error_handler = (($cli_mode = (PHP_SAPI == 'cli')) !== false ? 'tuxxedo_cli_error' : 'tuxxedo_doc_error');
		}

		$message 	= htmlentities($message);
		$prefix 	= '<strong>Warning:</strong> ';

		if($level & E_RECOVERABLE_ERROR)
		{
			if(($spos = strpos($message, TUXXEDO_DIR)) !== false)
			{
				$message = substr_replace($message, tuxxedo_trim_path(substr($message, $spos, $epos = strrpos($message, ' on line') - $spos)), $spos, $epos);
			}

			$error_handler(($cli_mode ? 'Recoverable error: ' : '<strong>Recoverable error:</strong> ') . $message);
		}
		elseif($level & E_USER_ERROR)
		{
			$error_handler(($cli_mode ? 'Fatal error: ' : '<strong>Fatal error:</strong> ') . $message);
		}
		elseif(($level & E_NOTICE) || ($level & E_USER_NOTICE))
		{
			$prefix = '<strong>Notice:</strong> ';
		}
		elseif(($level & E_DEPRECATED) || ($level & E_USER_DEPRECATED))
		{
			$prefix = '<strong>Deprecated:</strong> ';
		}
		elseif($level & E_STRICT)
		{
			$prefix = '<strong>Strict standards:</strong> ';
		}

		if($cli_mode)
		{
			$prefix = strip_tags($prefix);
		}

		if($file !== NULL && $line !== NULL)
		{
			$message .= ' in ' . tuxxedo_trim_path($file) . ' on line ' . $line;
		}

		$errors = (array) Registry::globals('errors');

		array_push($errors, $prefix . $message);

		Registry::globals('errors', $errors);
	}

	/**
	 * Handler register
	 *
	 * This function is a wrapper for registering handlers to various 
	 * functions, calling this function for registering handlers should 
	 * be registered using this function or some features may stop working 
	 * unexpectedly
	 *
	 * @param	string				The handler to register, can be one of 'error', 'exception', 'shutdown' or 'autoload'
	 * @param	callback			The callback to register to the handler
	 * @return	callback			Returns a callback, if only the first parameter is set, may also return false on error in any case
	 *
	 * @since	1.1.0
	 */
	function tuxxedo_handler($handler, $callback = NULL)
	{
		static $handlers, $references;

		if($references === NULL)
		{
			$references 	= [
						'error'		=> 'set_error_handler', 
						'exception'	=> 'set_exception_handler', 
						'shutdown'	=> 'register_shutdown_function', 
						'autoload'	=> 'spl_autoload_register'
						];
		}

		$handler = strtolower($handler);

		if(!isset($references[$handler]))
		{
			return(false);
		}

		if($callback === NULL)
		{
			if(!isset($handlers[$handler]))
			{
				return(false);
			}

			return($handlers[$handler]);
		}

		$handlers[$handler] = $callback;

		$references[$handler]($callback);
	}

	/**
	 * Print a document error (startup) and halts script execution
	 *
	 * @param	mixed				The message to show, this can also be an exception
	 * @return	void				No value is returned
	 *
	 * @changelog	1.2.0				This function now prints SQL query backtraces
	 */
	function tuxxedo_doc_error($e)
	{
		static $called;

		if($called !== NULL)
		{
			return;
		}

		$registry 	= Registry::init();
		$configuration	= Registry::getConfiguration();

		$called		= true;
		$buffer		= ob_get_clean();
		$exception	= ($e instanceof \Exception);
		$exception_sql	= $exception && $registry->db && $e instanceof Exception\SQL;
		$exception_xml	= $exception && $e instanceof Exception\Xml;
		$utf8		= function_exists('utf8_encode');
		$message	= ($exception ? $e->getMessage() : (string) $e);
		$errors		= ($registry ? Registry::globals('errors') : false);
		$application	= ($configuration['application']['name'] ? $configuration['application']['name'] . ($configuration['application']['version'] ? ' ' . $configuration['application']['version'] : '') : false);

		if(!$exception)
		{
			$e = NULL;
		}

		if(empty($message))
		{
			$message = 'No error message given';
		}
		elseif($exception_sql)
		{
			$message = ($configuration['application']['debug'] && ($err = $e->getMessage()) !== '' ? str_replace(["\r", "\n"], '', $err) : 'An error occured while querying the database');
		}
		elseif($utf8)
		{
			$message = utf8_encode($message);
		}

		header('Content-Type: text/html');

		echo(
			'<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . 
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL . 
			'<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . PHP_EOL . 
			'<head>' . PHP_EOL . 
			'<title>' . ($configuration['application']['debug'] ? 'Tuxxedo Engine' : 'Application') . ' Error</title>' . PHP_EOL . 
			'<style type="text/css">' . PHP_EOL . 
			'body { background-color: #E4F4FC; color: #3B7286; font-family: "Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif; font-size: 82%; margin: 0px 30px; }' . PHP_EOL . 
			'code { font-family: Consolas, Monaco, \'Courier New\', Monospace; }' . PHP_EOL . 
			'fieldset { background-color: #C2EDFD; border: 0px; border-radius: 4px; }' . PHP_EOL . 
			'fieldset legend { background-color: #C2EDFD; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'h1 { margin: 30px 0px -6px 0px; }' . PHP_EOL . 
			'h1 sup { background-color: #3B7286; border-radius: 4px; color: #FFFFFF; font-size: 35%; padding: 1px 3px; }' . PHP_EOL . 
			'h2 { margin: 20px 0px 0px 0px; }' . PHP_EOL . 
			'h2 span { background-color: #C2EDFD; border-top-left-radius: 4px; border-top-right-radius: 4px; padding: 5px; padding-bottom: 0px; }' . PHP_EOL . 
			'li, ul { margin: 0px; }' . PHP_EOL . 
			'table tr td { border: 1px solid transparent; }' . PHP_EOL . 
			'table tr.head td { background-color: #C2EDFD; padding: 5px; border-radius: 4px; }' . PHP_EOL . 
			'table tr td.value { background-color: #FFFFFF; border-radius: 4px; padding: 3px; }' . PHP_EOL . 
			'table tr.row, table tr.row * { margin: 0px; padding: 2px 5px; }' . PHP_EOL . 
			'table tr.strong td { background-color: #C2EDFD; border-radius: 4px; }' . PHP_EOL . 
			'table tr.strong td.empty { background-color: #FFFFFF; }' . PHP_EOL . 
			'.box { background-color: #C2EDFD; border: 3px solid #C2EDFD; border-radius: 4px; }' . PHP_EOL . 
			'.box.edge-title { border-top-left-radius: 0px; }' . PHP_EOL . 
			'.box .inner { background-color: #FFFFFF; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'.box .inner ul { padding: 3px 15px; }' . PHP_EOL . 
			'.box .outer { padding: 6px; }' . PHP_EOL . 
			'.content { margin: 15px 0px 10px 430px; }' . PHP_EOL . 
			'.infobox { background-color: #C2EDFD; border-radius: 4px; padding: 6px; }' . PHP_EOL . 
			'.infobox td { padding-right: 5px; }' . PHP_EOL . 
			'.left-content { float: left; }' . PHP_EOL . 
			'.left-content fieldset { width: 400px; }' . PHP_EOL . 
			'.spacer { margin-bottom: 10px; }' . PHP_EOL . 
			'.wrapper { padding: 0px 50px; }' . PHP_EOL . 
			'</style>' . PHP_EOL .  
			'</head>' . PHP_EOL . 
			'<body>' . PHP_EOL . 
			'<div class="wrapper">' . PHP_EOL . 
			($configuration['application']['debug'] && $buffer ? strip_tags($buffer) . PHP_EOL : '') . 
			'<h1>' . ($configuration['application']['debug'] ? 'Tuxxedo Engine Error <sup>v' . Version::SIMPLE . '</sup>' : 'Application Error') . '</h1>' . PHP_EOL
			);

		if($configuration['application']['debug'])
		{
			echo(
				'<div class="box">' . PHP_EOL . 
				'<div class="inner">' . PHP_EOL . 
				'<div class="left-content">' . PHP_EOL . 
				'<fieldset>' . PHP_EOL . 
				'<legend><strong>Application information</strong></legend>' . PHP_EOL . 
				'<table cellspacing="4" cellpadding="0">' . PHP_EOL
				);

			if($application)
			{
				echo(
					'<tr>' . PHP_EOL . 
					'<td>Application:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $application . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);
			}

			echo(
				'<tr>' . PHP_EOL . 
				'<td nowrap="nowrap">Engine version:</td>' . PHP_EOL . 
				'<td class="value" style="width: 100%">' . Version::FULL . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL .  
				'<tr>' . PHP_EOL . 
				'<td nowrap="nowrap">Library path:</td>' . PHP_EOL . 
				'<td class="value" style="width: 100%">' . str_replace(TUXXEDO_DIR, '', TUXXEDO_LIBRARY) . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL .  
				'<tr>' . PHP_EOL . 
				'<td nowrap="nowrap">Working directory:</td>' . PHP_EOL . 
				'<td class="value" style="width: 100%">' . TUXXEDO_DIR . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL .  
				'<tr>' . PHP_EOL . 
				'<td>Script:</td>' . PHP_EOL . 
				'<td class="value" nowrap="nowrap">' . tuxxedo_trim_path(realpath($_SERVER['SCRIPT_FILENAME'])) . '</td>' . PHP_EOL . 
				'</tr>' . PHP_EOL
				);

			if(($date = Utilities::date(NULL, 'H:i:s j/n - Y (e)')))
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
					'<td nowrap="nowrap">Exception type:</td>' . PHP_EOL . 
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
					'<td nowrap="nowrap">Database driver:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $e->getDriver() . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				if(($code = $e->getCode()) !== -1)
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td nowrap="nowrap">Error code:</td>' . PHP_EOL . 
						'<td class="value" style="width: 100%">' . $code . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				if(($sqlstate = $e->getSQLState()) !== false)
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td nowrap="nowrap">SQL State:</td>' . PHP_EOL . 
						'<td class="value" style="width: 100%">' . $sqlstate . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}
			}

			if($exception_xml)
			{
				echo(
					'<tr>' . PHP_EOL . 
					'<td colspan="2">&nbsp;</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL . 
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Library:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $e->getType(true) . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				if(($parser = $e->getParser()) !== '')
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td nowrap="nowrap">XML parser:</td>' . PHP_EOL . 
						'<td class="value" style="width: 100%">' . $parser . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				echo(
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Error code:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $e->getCode() . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				if(($depth = $e->getLevel()) !== false)
				{
					echo(
						'<tr>' . PHP_EOL . 
						'<td nowrap="nowrap">Depth:</td>' . PHP_EOL . 
						'<td class="value" style="width: 100%">' . $depth . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				echo(
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Column:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $e->getColumn() . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL .
					'<tr>' . PHP_EOL . 
					'<td nowrap="nowrap">Line:</td>' . PHP_EOL . 
					'<td class="value" style="width: 100%">' . $e->getXmlLine() . '</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);
			}

			echo(
				'</table>' . PHP_EOL . 
				'</fieldset>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'<div class="content">' . PHP_EOL . 
				'<div class="infobox">' . PHP_EOL . 
				nl2br($message) . PHP_EOL
				);

			if($exception && $e instanceof Exception\BasicMulti && ($multi_errors = $e->getErrors()) !== false)
			{
				echo(
					'<div class="inner">' . PHP_EOL . 
					'<ul>' . PHP_EOL
					);

				foreach($multi_errors as $error)
				{
					echo(
						'<li>' . $error . '</li>' . PHP_EOL
						);
				}

				echo(
					'</ul>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);

				
			}

			echo(
				'</div>' . PHP_EOL . 
				'<br />' . PHP_EOL
				);

			if($configuration['application']['debug'] && $errors)
			{
				foreach($errors as $error)
				{
					if(!$error)
					{
						continue;
					}

					echo(
						'<div class="infobox">' . PHP_EOL . 
						(!$utf8 ? $error : utf8_encode($error)) . PHP_EOL . 
						'</div>' . PHP_EOL . 
						'<br />'
						);
				}

				Registry::globals('errors', []);
			}

			if($exception_sql)
			{
				echo(
					'<fieldset>' . PHP_EOL . 
					'<legend><strong>SQL</strong></legend>' . PHP_EOL .
					'<table cellspacing="4" cellpadding="0" style="width: 100%;">' . PHP_EOL . 
					'<tr>' . PHP_EOL . 
					'<td colspan="2" class="value" style="width: 100%"><code>' . str_replace(["\r", "\n"], '', $e->getSQL()) . '</code></td>' . PHP_EOL . 
					'</tr>' . PHP_EOL . 
					'</table>' . PHP_EOL . 
					'</fieldset>' . PHP_EOL
					);
			}

			echo(
				'</div>' . PHP_EOL . 
				'<div style="clear: left;"></div>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'</div>' . PHP_EOL
				);

			if(($bt = new Debug\Backtrace($e)))
			{
				echo(
					'<h2><span>Backtrace</span></h2>' . PHP_EOL . 
					'<div class="box edge-title">' . PHP_EOL . 
					'<div class="inner">' . PHP_EOL . 
					'<table style="width: 100%" cellspacing="2" cellpadding="0">' . PHP_EOL . 
					'<tr class="head">' . PHP_EOL . 
					'<td>&nbsp;</td>' . PHP_EOL . 
					'<td class="strong">Call</td>' . PHP_EOL . 
					'<td class="strong">File</td>' . PHP_EOL . 
					'<td class="strong">Line</td>' . PHP_EOL . 
					'<td class="strong">Notes</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				foreach($bt as $trace)
				{
					echo(
						'<tr class="' . ($trace->current ? 'strong ' : '') . 'row">' . PHP_EOL . 
						'<td align="center"><h3>' . ($trace->frame + 1) . '</h3></td>' . PHP_EOL . 
						'<td nowrap="nowrap">' . $trace->call . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap" style="width: 100%">' . tuxxedo_trim_path($trace->file) . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap" align="right">' . $trace->line . '</td>' . PHP_EOL . 
						'<td nowrap="nowrap">' . $trace->notes . '</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);

					if($configuration['debug']['fullbacktrace'] || $trace->current)
					{
						echo(
							'<tr class="' . ($trace->current ? 'strong ' : '') . 'row">' . PHP_EOL . 
							'<td class="empty"><h3>&nbsp;</h3></td>' . PHP_EOL . 
							'<td colspan="4"><code>' . htmlentities($trace->callargs) . '</code></td>' . PHP_EOL . 
							'</tr>' . PHP_EOL
							);
					}
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
					'<h2><span>Queries</span></h2>' . PHP_EOL . 
					'<div class="box edge-title">' . PHP_EOL . 
					'<div class="inner">' . PHP_EOL . 
					'<table style="width: 100%" cellspacing="2" cellpadding="0">' . PHP_EOL . 
					'<tr class="head">' . PHP_EOL . 
					'<td style="width: 10">&nbsp;</td>' . PHP_EOL . 
					'<td class="strong">SQL</td>' . PHP_EOL . 
					'</tr>' . PHP_EOL
					);

				foreach($registry->db->getQueries() as $n => $query)
				{
					echo(
						'<tr class="row">' . PHP_EOL . 
						'<td align="center"><h3>' . ++$n . '</h3></td>' . PHP_EOL . 
						'<td>' . PHP_EOL
						);

					if($query['trace'])
					{
						echo(
							'<fieldset>' . PHP_EOL . 
							'<legend><strong>SQL</strong></legend>' . PHP_EOL . 
							'<table cellspacing="4" cellpadding="0" style="width: 100%;">' . PHP_EOL . 
							'<tr>' . PHP_EOL . 
							'<td class="value" style="width: 100%"><code>' . $query['sql'] . '</code></td>' . PHP_EOL . 
							'</tr>' . PHP_EOL . 
							'</table>' . PHP_EOL . 
							'</fieldset>' . PHP_EOL . 
							'<div style="margin-top: 10px; padding: 0px;">' . PHP_EOL . 
							'<div style="float: left; margin-right: 10px; padding: 0px; width: 40%;">' . PHP_EOL . 
							'<fieldset>' . PHP_EOL . 
							'<legend><strong>Trace information</strong></legend>' . PHP_EOL . 
							'<table cellspacing="4" cellpadding="0">' . PHP_EOL . 
							'<tr>' . PHP_EOL . 
							'<td nowrap="nowrap">Execution time:</td>' . PHP_EOL . 
							'<td class="value" style="width: 100%">' . $query['trace']['timer'] . ' seconds</td>' . PHP_EOL . 
							'</tr>' . PHP_EOL . 
							'</table>' . PHP_EOL . 
							'</fieldset>' . PHP_EOL
							);

						if($query['trace']['frames'])
						{
							$frames = sizeof($query['trace']['frames']);

							echo(
								'</div>' . PHP_EOL . 
								'<div style="padding: 0px;">' . PHP_EOL . 
								'<fieldset>' . PHP_EOL . 
								'<legend><strong>Backtrace</strong></legend>' . PHP_EOL . 
								'<table cellspacing="4" cellpadding="0" style="width: 100%;">' . PHP_EOL
								);

							foreach($query['trace']['frames'] as $trace)
							{
								if($trace->frame < 2)
								{
									continue;
								}

								echo(
									'<tr>' . PHP_EOL . 
									'<td>' . ($frames - $trace->frame) . '</td>' . PHP_EOL . 
									'<td class="value" style="width: 100%">' . $trace->call . '</td>' . PHP_EOL . 
									'</tr>' . PHP_EOL
									);
							}

							echo(
								'</table>' . PHP_EOL . 
								'</fieldset>' . PHP_EOL . 
								'</div>' . PHP_EOL . 
								'</div>' . PHP_EOL . 
								'<div class="clear"></div>' . PHP_EOL
								);
						}
					}
					else
					{
						echo(
							'<code>' . $query['sql'] . '</code>' . PHP_EOL
							);
					}

					echo(
						'</td>' . PHP_EOL . 
						'</tr>' . PHP_EOL
						);
				}

				echo(	
					'</table>' . PHP_EOL . 
					'</div>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);
			}

			echo(
				'<p>' . PHP_EOL . 
				'<em>' . 
				'Tuxxedo Engine &copy; 2006+ - Tuxxedo Software Development' . 
				'</em>' . PHP_EOL . 
				'</p>' . PHP_EOL
				);
		}
		else
		{
			echo(
				'<div class="box">' . PHP_EOL . 
				'<div class="inner">' . PHP_EOL . 
				nl2br($message) .  PHP_EOL
				);

			if($exception && $e instanceof Exception\BasicMulti && ($multi_errors = $e->getErrors()) !== false)
			{
				echo(
					'<div class="inner">' . PHP_EOL . 
					'<ul>' . PHP_EOL
					);

				foreach($multi_errors as $error)
				{
					echo(
						'<li>' . $error . '</li>' . PHP_EOL
						);
				}

				echo(
					'</ul>' . PHP_EOL . 
					'</div>' . PHP_EOL
					);

				
			}

			echo(
				'</div>' . PHP_EOL . 
				'</div>' . PHP_EOL . 
				'<p>' . PHP_EOL . 
				'<em>' . 
				'This error was generated by ' . ($application ? $application . ' (Powered by ' : '') . 'Tuxxedo Engine ' . Version::SIMPLE . ($application ? ')' : '') . 
				'</em>' . PHP_EOL . 
				'</p>'
				);
		}

		die(
			'</div>' . PHP_EOL . 
			'</body>' . PHP_EOL . 
			'</html>'
			);
	}

	/**
	 * Print a document error (startup) and halts script execution for CLI.
	 *
	 * @param	mixed				The message to show, this can also be an exception
	 * @return	void				No value is returned
	 *
	 * @since	1.2.0
	 */
	function tuxxedo_cli_error($e)
	{
		static $called;

		if($called !== NULL)
		{
			return;
		}

		$registry 	= Registry::init();
		$configuration	= Registry::getConfiguration();

		$called		= true;
		$buffer		= ob_get_clean();
		$exception	= ($e instanceof \Exception);
		$exception_sql	= $exception && $registry->db && $e instanceof Exception\SQL;
		$exception_xml	= $exception && $e instanceof Exception\Xml;
		$utf8		= function_exists('utf8_encode');
		$message	= ($exception ? htmlentities($e->getMessage()) : (string) $e);
		$errors		= ($registry ? Registry::globals('errors') : false);
		$application	= ($configuration['application']['name'] ? $configuration['application']['name'] . ($configuration['application']['version'] ? ' ' . $configuration['application']['version'] : '') : false);

		if(!$exception)
		{
			$e = NULL;
		}

		if(empty($message))
		{
			$message = 'No error message given';
		}
		elseif($exception_sql)
		{
			$message = ($configuration['application']['debug'] ? str_replace(["\r", "\n"], '', $e->getMessage()) : 'An error occured while querying the database');
		}
		elseif($utf8)
		{
			$message = utf8_encode($message);
		}

		echo(
			(!empty($buffer) ? $buffer . PHP_EOL : '') . 
			($configuration['application']['debug'] ? 'Tuxxedo Engine Error' : 'Application Error') . PHP_EOL . 
			PHP_EOL . 
			PHP_EOL . 
			$message . 
			PHP_EOL . 
			PHP_EOL . 
			PHP_EOL
			);

		if($exception && $e instanceof Exception\BasicMulti && ($multi_errors = $e->getErrors()) !== false)
		{
			foreach($multi_errors as $error)
			{
				echo(
					' - ' . $error . PHP_EOL
					);
			}

			echo(
				PHP_EOL . 
				PHP_EOL
				);
		}

		if($configuration['application']['debug'])
		{
			echo(
				'Application information' . PHP_EOL . 
				'-----------------------' . PHP_EOL
				);

			if($application)
			{
				echo(
					str_pad('Application: ', 20, ' ') .  $application . PHP_EOL
					);
			}

			echo(
				str_pad('Engine version: ', 20, ' ') . Version::FULL . PHP_EOL . 
				str_pad('Library path: ', 20, ' ') . str_replace(TUXXEDO_DIR, '', TUXXEDO_LIBRARY) . PHP_EOL . 
				str_pad('Working directory: ', 20, ' ') . TUXXEDO_DIR . PHP_EOL . 
				str_pad('Script: ', 20, ' ') . tuxxedo_trim_path(realpath($_SERVER['SCRIPT_FILENAME'])) . PHP_EOL
				);

			if(($date = Utilities::date(NULL, 'H:i:s j/n - Y (e)')))
			{
				echo(
					str_pad('Timestamp: ', 20, ' ') . $date . PHP_EOL
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
					PHP_EOL . 
					str_pad('Exception type: ', 20, ' ') . $class . PHP_EOL
					);
			}

			if($exception_sql)
			{
				echo(
					PHP_EOL . 
					str_pad('Database driver: ', 20, ' ') . $e->getDriver() . PHP_EOL . 
					str_pad('Error code: ', 20, ' ') . $e->getCode() . PHP_EOL
					);

				if(($sqlstate = $e->getSQLState()) !== false)
				{
					echo(
						str_pad('SQL State: ', 20, ' ') . $sqlstate . PHP_EOL
						);
				}
			}

			if($exception_xml)
			{
				echo(
					PHP_EOL . 
					str_pad('Library: ', 20, ' ') . $e->getType(true) . PHP_EOL
					);

				if(($parser = $e->getParser()) !== false)
				{
					echo(
						str_pad('XML parser: ', 20, ' ') . $parser . PHP_EOL
						);
				}

				echo(
					str_pad('Error code: ', 20, ' ') . $e->getCode() . PHP_EOL
					);

				if(($depth = $e->getLevel()) !== false)
				{
					echo(
						str_pad('Depth: ', 20, ' ') . $depth . PHP_EOL
						);
				}

				echo(
					str_pad('Column: ', 20, ' ') . $e->getColumn() . PHP_EOL . 
					str_pad('Line: ', 20, ' ') . $e->getXmlLine() . PHP_EOL
					);
			}

			if($configuration['application']['debug'] && $errors)
			{
				echo(
					PHP_EOL . 
					PHP_EOL . 
					'Errors' . PHP_EOL . 
					'------' . PHP_EOL
					);

				foreach($errors as $error)
				{
					if(!$error)
					{
						continue;
					}

					echo(
						(!$utf8 ? $error : utf8_encode($error)) . PHP_EOL . 
						PHP_EOL
						);
				}

				Registry::globals('errors', []);
			}

			if($exception_sql)
			{
				echo(
					PHP_EOL . 
					PHP_EOL . 
					'SQL' . PHP_EOL . 
					'---' . PHP_EOL . 
					str_replace(["\r", "\n"], '', $e->getSQL()) . PHP_EOL
					);
			}

			if(($bt = new Debug\Backtrace($e)))
			{
				echo(
					PHP_EOL . 
					PHP_EOL . 
					'Backtrace' . PHP_EOL . 
					'---------' . PHP_EOL
					);

				foreach($bt as $trace)
				{
					echo(
						'#' . ($trace->frame + 1) . ': ' . ($trace->file && $trace->line ? tuxxedo_trim_path($trace->file) . '(' . $trace->line . '):' : '') . PHP_EOL . 
						($trace->current ? '>>> ' : '') . (($configuration['debug']['fullbacktrace'] || $trace->current) ? $trace->callargs : $trace->call) . PHP_EOL .
						PHP_EOL
						);

					if($trace->notes)
					{
						echo(
							'(' . $trace->notes . ')' . PHP_EOL . 
							PHP_EOL
							);
					}
				}
			}

			if($registry && $registry->db && $registry->db->getNumQueries())
			{
				echo(
					PHP_EOL . 
					PHP_EOL . 
					'Queries' . PHP_EOL . 
					'-------' . PHP_EOL
					);

				foreach($registry->db->getQueries() as $n => $query)
				{
					echo(
						'#' . ++$n . ':' . PHP_EOL . 
						Utilities::trimSql($query['sql']) . PHP_EOL
						);

					if($query['trace'])
					{

						echo(
							str_pad('Execution time: ', 20, ' ') . $query['trace']['timer'] . ' seconds' . PHP_EOL
							);

						if($query['trace']['frames'])
						{
							$frames = sizeof($query['trace']['frames']);

							echo(
								str_pad('Debug frames: ', 20, ' ') . $frames . PHP_EOL . 
								PHP_EOL
								);

							foreach($query['trace']['frames'] as $trace)
							{
								if($trace->frame < 2)
								{
									continue;
								}

								echo(
									"\t#" . ($frames - $trace->frame) . ': ' . $trace->call . PHP_EOL
									);
							}

							echo(
								PHP_EOL
								);
						}
					}
				}
			}
		}
		else
		{
			echo(
				$message
				);
		}

		exit;
	}

	/**
	 * Basic error, this function picks the CLI handler for CLI mode 
	 * and web otherwise for formatting to prevent having a lot of if 
	 * conditionals to call the correct function.
	 *
	 * This function terminates the script.
	 *
	 * @param	mixed				The message to show, this can also be an exception
	 * @return	void				No value is returned
	 *
	 * @since	1.2.0
	 */
	function tuxxedo_basic_error($e)
	{
		static $error_handler;

		if(!$error_handler)
		{
			$error_handler = (PHP_SAPI == 'cli' ? 'tuxxedo_cli_error' : 'tuxxedo_doc_error');
		}

		$error_handler($e);
	}

	/**
	 * Formattable error, this function is SAPI aware and will 
	 * call the CLI error handler on terminals
	 *
	 * @param	string				The error message, in a printf-alike formatted string or just a normal string
	 * @param	mixed				Optional argument #n for formatting
	 * @return	Void				No value is returned
	 */
	function tuxxedo_errorf()
	{
		if(!func_num_args())
		{
			tuxxedo_doc_error('Unknown error');
		}

		tuxxedo_basic_error(call_user_func_array('sprintf', func_get_args()));

	}

	/**
	 * Trims a file path to hide its path prior to the root 
	 * of the application
	 *
	 * @param	string				The path to trim
	 * @return	string				The trimmed path
	 *
	 * @changelog	1.2.0				Removed the $debug_trim parameter
	 */
	function tuxxedo_trim_path($path)
	{
		static $dir, $lib;

		if(empty($path))
		{
			return('');
		}

		if(!$dir)
		{
			$dir = realpath(TUXXEDO_DIR);
			$lib = realpath(TUXXEDO_LIBRARY);
		}

		$trimmed = (strpos($path, '/') !== false || strpos($path, '\\') !== false || strpos($path, $dir) !== false ? str_replace(['/', '\\', $dir], DIRECTORY_SEPARATOR, $path) : ($path{1} != ':' && $path{2} != '\\' ? DIRECTORY_SEPARATOR : '') . ltrim($path, DIRECTORY_SEPARATOR));

		if($trimmed == $path)
		{
			$trimmed = str_replace((($ptr = strrpos($lib, DIRECTORY_SEPARATOR)) !== false ? substr($lib, 0, $ptr) : $lib), '', $trimmed);
		}

		return(str_replace(['\\\\', '//'], ['\\', '/'], $trimmed));
	}

	/**
	 * Shutdown handler
	 *
	 * @return	void				No value is returned
	 *
	 * @changelog	1.2.0				This function is now CLI compatible
	 */
	function tuxxedo_shutdown_handler()
	{
		$configuration	= Registry::getConfiguration();
		$output 	= (ob_get_length() ? ob_get_clean() : '');

		if($configuration['application']['debug'] && $output && substr(ltrim($output), 0, 11) == 'Fatal error')
		{
			$error = trim(substr_replace($output, (PHP_SAPI == 'cli' ? 'Fatal error' : '<strong>Fatal error</strong>'), 0, 12));

			if(($spos = strpos($error, TUXXEDO_DIR)) !== false)
			{
				$error = substr_replace($error, tuxxedo_trim_path(substr($error, $spos, $epos = strrpos($error, ' on line') - $spos)), $spos, $epos);
			}

			tuxxedo_basic_error($error);
		}

		$errors = Registry::globals('errors');

		if(!$configuration['application']['debug'] || !$errors)
		{
			echo($output);

			return;
		}

		if(PHP_SAPI == 'cli')
		{
			$buffer = PHP_EOL . strip_tags(implode(PHP_EOL, $errors));
		}
		else
		{
			$buffer = '<br />' . implode('<br />', $errors);
		}

		if($pos = stripos($output, '</body>'))
		{
			$output = substr_replace($output, $buffer . '</body>', $pos, 7);
		}
		else
		{
			$output .= $buffer;
		}

		echo($output);
	}

	/**
	 * Handles multiple errors repeatingly
	 *
	 * @param	string				A sprintf-like format, only applies for singular errors (if $show_all is set to false)
	 * @param	array				An array with elements to loop through
	 * @param	string				A fully quantified exception name to throw, this should be able to handle multiple errors
	 * @param	boolean				Whether to display all errors or not
	 * @return	void				No value is returned
	 *
	 * @throws	mixed				Throws an exception until the errors have been cleared
	 *
	 * @changelog	1.2.0				The $exception parameter now defaults to NULL
	 * @changelog	1.2.0				Added the $show_all parameter
	 */
	function tuxxedo_multi_error($format, Array $elements, $exception = NULL, $show_all = true)
	{
		if(!$elements)
		{
			return;
		}
		elseif($exception === NULL)
		{
			$exception = ($show_all ? '\Tuxxedo\Exception\BasicMulti' : '\Tuxxedo\Exception\Basic');
		}

		if($show_all)
		{
			throw new $exception($elements, $format);
		}

		throw new $exception($format, (string) reset($elements));
	}
?>