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
			$expression_len	= strlen($expression);
		}

		if(!$glob)
		{
			return(false);
		}

		$return_value 	= Array();

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