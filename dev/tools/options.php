<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'options_index', 
					'options_index_itembit'
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'options'
					);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'options');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');
	require('./includes/functions_options.php');

	switch(strtolower($filter->get('do')))
	{
/*
		case(''):
		{
		}
		break;
*/
		default:
		{
			$query = $db->query('
						SELECT 
							*
						FROM
							`' . TUXXEDO_PREFIX . 'options` 
						ORDER BY 
							`option` ASC');

			if(!$query || !$query->getNumRows())
			{
				tuxxedo_gui_error('No options to display. Add one from the sidebar');
			}

			$table 		= '';
			$reminder	= false;

			while($opt = $query->fetchAssoc())
			{
				$cached 	= isset($cache->options[$opt['option']]);
				$type		= options_long_type($opt['type']);
				$value		= ($opt['value'] !== $opt['defaultvalue'] ? options_value_dump($opt['type'], $opt['value']) : '');
				$defaultvalue	= options_value_dump($opt['type'], $opt['defaultvalue']);
				$cachevalue	= ($cached ? options_value_dump($opt['type'], $cache->options[$opt['option']]) : 'N/A');

				eval('$table .= "' . $style->fetch('options_index_itembit') . '";');

				if(!$cached || ($cached && $opt['value'] != $cache->options[$opt['option']]))
				{
					$reminder = true;
				}
			}

			eval(page('options_index'));
		}
		break;
	}
?>