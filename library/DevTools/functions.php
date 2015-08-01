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
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;
	use Tuxxedo\User;
	use Tuxxedo\Utilities;


	/**
	 * A recursive glob function
	 *
	 * @param	string			The glob expression to execute
	 * @return	array			Returns an array containing the matched elements and false on error
	 */
	function recursive_glob($expression)
	{
		if(($pos = strpos($expression, '*')) !== false)
		{
			$glob = glob(substr($expression, 0, $pos) . ($expression{$pos - 1} != '/' && $expression{$pos - 1} != '\\' ? '/' : '') . '*');

			if(strlen($expression) > $pos)
			{
				$suffix 	= substr($expression, $pos + 1);
				$suffix_len	= strlen($suffix);
			}
		}
		else
		{
			$glob 		= glob($expression . '/*');
			$expression_len	= strlen($expression . '/');
		}

		if(!$glob)
		{
			return(false);
		}

		$return_value 	= [];

		foreach($glob as $entry)
		{
			if(is_dir($entry))
			{
				if(isset($suffix))
				{
					$entry .= '/*' . $suffix;
				}

				if(($entries = recursive_glob($entry)) !== false)
				{
					foreach($entries as $sub_entry)
					{
						$return_value[] = $sub_entry;
					}
				}

				continue;
			}

			$entry_len = strlen($entry);

			if(isset($suffix) && ($entry_len < $suffix_len || substr($entry, $entry_len - $suffix_len) != $suffix))
			{
				continue;
			}

			$return_value[] = $entry;
		}

		return($return_value);
	}

	/**
	 * Tests a log in without interfering with the current session
	 *
	 * @param	string			The user identifier
	 * @param	string			The user password
	 * @param	string			The user identifier field
	 * @return	boolean			Returns true if the login was successful and otherwise false
	 *
	 * @since	1.1.0
	 */
	function test_login($identifier, $password, $identifier_field = 'username')
	{
		$registry 	= Registry::init();
		$user		= ($registry->user ? $registry->user : $registry->invoke('\Tuxxedo\User'));
		$userinfo	= $user->getUserInfo($identifier, $identifier_field);

		return($userinfo && User::isValidPassword($password, $userinfo->salt, $userinfo->password));
	}

	/**
	 * Extended exception handler
	 *
	 * @param	\Exception		The exception to handle
	 * @return	void			No value is returned
	 *
	 * @since	1.1.0
	 */
	function devtools_exception_handler(\Exception $e)
	{
		if($e instanceof Exception\Multi)
		{
			$list 		= '';
			$style		= Registry::init()->style;
			$message	= $e->getMessage();

			foreach($e->getData() as $field)
			{
				eval('$list .= "' . $style->fetch('multierror_itembit') . '";');
			}

			eval(page('multierror'));
		}
		else
		{
			tuxxedo_exception_handler($e);
		}
	}

	/**
	 * Authentication handler
	 *
	 * This function handles all perspectives of the DevTools authentication (both 
	 * log in and log out) and all the things in between.
	 *
	 * @return	void			No value is returned
	 *
	 * @since	1.2.0
	 */
	function devtools_auth_handler()
	{
		global $configuration, $input, $session;

		if($session['devtools_authenticated'])
		{
			if(!$configuration['devtools']['protective'] || (isset($_GET['logout']) && $_GET['logout']))
			{
				global $style, $engine_version, $header, $footer;

				$session['devtools_authenticated'] = false;

				eval('$widget = "' . $style->getSidebarWidget() . '";');
				eval('$header = "' . $style->fetch('header') . '";');
				eval('$footer = "' . $style->fetch('footer') . '";');

				if($configuration['devtools']['protective'])
				{
					Utilities::redirect('Logged out with success', './', 3);
				}
			}
		}
		elseif(isset($_POST['password']) && $input->post('password') === $configuration['devtools']['password'])
		{
			$session['devtools_authenticated'] = true;

			Utilities::headerRedirect($_SERVER['SCRIPT_NAME'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
		}
		else
		{
			$self = htmlspecialchars($_SERVER['SCRIPT_NAME'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''), ENT_QUOTES);

			eval(page('password'));
			exit;
		}
	}

	/**
	 * Generates code to print a page
	 *
	 * @param	string				The template name to print
	 * @return	void				No value is returned
	 */
	function page($template)
	{
		$registry = Registry::init();

		if(!$registry->style)
		{
			return('');
		}

		return(
			'global $header, $footer;' . 
			'echo("' . $registry->style->fetch($template) . '");'
			);
	}
?>