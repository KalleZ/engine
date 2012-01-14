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
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * A recursive glob function
	 *
	 * @param	string			The glob expression to execute
	 * @return	array			Returns an array containing the matched elements and false on error
	 */
	function recursive_glob($expression)
	{
		static $expression_prefix;

		if(!$expression_prefix)
		{
			$expression_prefix = strlen($expression) + 1;
		}

		$glob = glob($expression . '/*');

		if(!$glob)
		{
			return(false);
		}

		$return_value = Array();

		foreach($glob as $entry)
		{
			if(is_dir($entry))
			{
				if(($entries = recursive_glob($entry)) !== false)
				{
					foreach($entries as $sub_entry)
					{
						$return_value[] = $sub_entry;
					}
				}

				continue;
			}

			$entry = substr_replace($entry, '', 0, $expression_prefix);

			if(strpos($entry, '\\') !== false)
			{
				$entry = str_replace('\\', '/', $entry);
			}

			$return_value[] = $entry;
		}

		return($return_value);
	}

	/**
	 * Extended exception handler
	 *
	 * @param	\Exception		The exception to handle
	 * @return	void			No value is returned
	 */
	function devtools_exception_handler(\Exception $e)
	{
		if($e instanceof Exception\FormData)
		{
			$list 		= '';
			$style		= Registry::init()->style;
			$message	= $e->getMessage();

			foreach($e->getFields() as $field)
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
?>