<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Executes a redirect
	 *
	 * If headers are not sent, then a header('location: xxx') is sent and 
	 * the script execution stops. Otherwise a link is printed out
	 *
	 * @param	string			The location to redirect to
	 * @return	void			No value is returned
	 */
	function redirect($location)
	{
		if(!headers_sent())
		{
			header('Location: ' . $location);
			exit;
		}

		echo('<a href="' . $location . '">Click here to get redirected...</a>');
	}

	/**
	 * Constructs a new storage engine
	 *
	 * @param	string			The one character option type: b, i etc.
	 * @param	string			The value to convert
	 * @return	string			Converts the value into the desired type, and string for unknown types
	 */
	function convert_to_option_type($type, $value)
	{
		switch($type)
		{
			case('b'):
			{
				return((boolean) $value);
			}
			break;
			case('i'):
			{
				return((integer) $value);
			}
			break;
		}

		return((string) $value);
	}
?>