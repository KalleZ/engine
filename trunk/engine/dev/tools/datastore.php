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
	 * Global templates
	 */
	$templates = Array(
				'datastore_index', 
				'datastore_index_itembit'
				);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'datastore');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	$indices = Array(
				'languages'	=> 'id', 
				'options'	=> 'option', 
				'phrasegroups'	=> 'id', 
				'styleinfo'	=> 'id', 
				'usergroups'	=> 'id', 
				'timezones'	=> NULL
				);

	$filter = new Tuxxedo_Filter;

	switch(strtolower($filter->get('do')))
	{
		case('truncate'):
		{
			$db->query('TRUNCATE TABLE `' . TUXXEDO_PREFIX . 'datastore`');

			tuxxedo_redirect('Datastore truncated', './datastore.php');
		}
		break;
		case('dump'):
		{
		}
		break;
		default:
		{
			if($filter->post('progress'))
			{
				var_dump(1337);
			}
			else
			{
				$cache_items = '';

				foreach(array_keys($indices) as $index)
				{
					eval('$cache_items .= "' . $style->fetch('datastore_index_itembit') . '";');
				}

				eval(page('datastore_index'));
			}
		}
		break;
	}	
?>