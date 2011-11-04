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
	use Tuxxedo\Input;


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
	const SCRIPT_NAME	= 'options';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');
	require(TUXXEDO_LIBRARY . '/DevTools/functions_options.php');

	switch($do = strtolower($input->get('do')))
	{
		case('add'):
		{
			if($input->post('submit'))
			{
				if(!options_add($input->post('name'), $input->post('characters'), $input->post('value')))
				{
					tuxxedo_error('Failed to add new option, possible naming conflict');
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
			$option = $input->get('option');

			if(($opt = options_get_single($option)) == false)
			{
				tuxxedo_error('Invalid option');
			}

			$cached 	= isset($datastore->options[$option]);
			$defaultvalue	= options_value_dump($opt['type'], $opt['defaultvalue']);
			$cachevalue	= ($cached ? options_value_dump($opt['type'], $opt['value']) : 'N/A');

			if($input->post('submit'))
			{
				if(!options_edit($option, $input->post('name'), $input->post('characters'), $input->post('value'), $input->post('defaultoverride', Input::TYPE_BOOLEAN)))
				{
					tuxxedo_error('Failed to edit option, possible naming conflict');
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
			$option = $input->get('option');

			if(!options_is_valid($option))
			{
				tuxxedo_error('Invalid option');
			}

			options_delete($option) or tuxxedo_error('Unable to delete option');

			tuxxedo_redirect('Deleted option', './options.php');
		}
		break;
		case('reset'):
		{
			$option = $input->get('option');

			if($option !== NULL)
			{
				if(!options_is_valid($option))
				{
					tuxxedo_error('Invalid option');
				}

				options_reset($option);

				tuxxedo_redirect('Option reset to default value', './options.php');
			}
			else
			{
				$options = options_get_all();

				if(!$options)
				{
					tuxxedo_error('No options found');
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
				tuxxedo_error('No options to display. Add one from the sidebar');
			}

			$table 		= '';
			$reminder	= false;
			$found		= Array();

			while($opt = $query->fetchAssoc())
			{
				$found[]	= $opt['option'];
				$cached 	= isset($datastore->options[$opt['option']]);
				$value		= ($opt['value'] !== $opt['defaultvalue'] ? options_value_dump($opt['type'], $opt['value']) : '');
				$defaultvalue	= options_value_dump($opt['type'], $opt['defaultvalue']);
				$cachevalue	= ($cached ? options_value_dump($opt['type'], $datastore->options[$opt['option']]) : 'N/A');

				eval('$table .= "' . $style->fetch('options_index_itembit') . '";');

				if(!$cached || ($cached && $opt['value'] != $datastore->options[$opt['option']]))
				{
					$reminder = true;
				}
			}

			if(array_diff(array_keys($datastore->options), $found))
			{
				$reminder = true;
			}

			eval(page('options_index'));
		}
		break;
	}
?>