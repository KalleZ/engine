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
	 * Global templates
	 */
	$templates 		= Array(
					'options_index', 
					'options_index_itembit'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'add'	=> Array(
								'options_add_edit_form'
								), 
					'edit'	=> Array(
								'options_add_edit_form'
								)
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

	switch($do = strtolower($filter->get('do')))
	{
		case('add'):
		{
			if($filter->post('submit'))
			{
				if(!options_add($filter->post('name'), $filter->post('characters'), $filter->post('value')))
				{
					tuxxedo_gui_error('Failed to add new option, possible naming conflict');
				}

				tuxxedo_redirect('Added option', './options.php');
			}
			else
			{
				eval(page('options_add_edit_form'));
			}
		}
		break;
		case('edit'):
		{
			$option = $filter->get('option');

			if(($options = options_get_single($option)) == false)
			{
				tuxxedo_gui_error('Invalid option');
			}
			elseif($filter->post('submit'))
			{
				if(!options_edit($option, $filter->post('name'), $filter->post('characters'), $filter->post('value')))
				{
					tuxxedo_gui_error('Failed to edit option, possible naming conflict');
				}

				tuxxedo_redirect('Edited option', './options.php');
			}
			else
			{
				eval(page('options_add_edit_form'));
			}
		}
		break;
		case('delete'):
		{
			$option = $filter->get('option');

			if(!options_is_valid($option))
			{
				tuxxedo_gui_error('Invalid option');
			}

			options_delete($option) or tuxxedo_gui_error('Unable to delete option');

			tuxxedo_redirect('Deleted option', './options.php');
		}
		break;
		case('reset'):
		{
			$option = $filter->get('option');

			if($option !== NULL)
			{
				if(!options_is_valid($option))
				{
					tuxxedo_gui_error('Invalid option');
				}

				options_reset($option);

				tuxxedo_redirect('Option reset to default value', './options.php');
			}
			else
			{
				$options = options_get_all();

				if(!$options)
				{
					tuxxedo_gui_error('No options found');
				}

				foreach($options as $name => $data)
				{
					options_reset($name);
				}

				tuxxedo_redirect('All options reset to their default value', './options.php');
			}
		}
		break;
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
			$found		= Array();

			while($opt = $query->fetchAssoc())
			{
				$found[]	= $opt['option'];
				$cached 	= isset($cache->options[$opt['option']]);
				$value		= ($opt['value'] !== $opt['defaultvalue'] ? options_value_dump($opt['type'], $opt['value']) : '');
				$defaultvalue	= options_value_dump($opt['type'], $opt['defaultvalue']);
				$cachevalue	= ($cached ? options_value_dump($opt['type'], $cache->options[$opt['option']]) : 'N/A');

				eval('$table .= "' . $style->fetch('options_index_itembit') . '";');

				if(!$cached || ($cached && $opt['value'] != $cache->options[$opt['option']]))
				{
					$reminder = true;
				}
			}

			if(array_diff(array_keys($cache->options), $found))
			{
				$reminder = true;
			}

			eval(page('options_index'));
		}
		break;
	}
?>