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
	use Tuxxedo\Datamanager;
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

	switch($do = strtolower($input->get('do')))
	{
		case('add'):
		{
			if($input->post('submit'))
			{
				$opt 		= Datamanager\Adapter::factory('option');
				$opt['option']	= $input->post('name');
				$opt['type']	= $input->post('characters');
				$opt['value']	= $input->post('value');

				$opt->save();

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
			$option 	= $input->get('option');
			$opt		= Datamanager\Adapter::factory('option', $option);
			$cached 	= isset($datastore->options[$option]);
			$defaultvalue	= var_dump_option($opt['type'], $opt['defaultvalue']);
			$cachevalue	= ($cached ? var_dump_option($opt['type'], $opt['value']) : 'N/A');

			if($input->post('submit'))
			{
				$opt['option']		= $input->post('name');
				$opt['type']		= $input->post('characters');
				$opt['value']		= $input->post('value');
				$opt['newdefault']	= $input->post('defaultoverride', Input::TYPE_BOOLEAN);

				$opt->save();

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
			$opt	= Datamanager\Adapter::factory('option', $option);

			$opt->delete();

			tuxxedo_redirect('Deleted option', './options.php');
		}
		break;
		case('reset'):
		{
			$option = $input->get('option');

			if($option !== NULL)
			{
				Datamanager\Adapter::factory('option', $option)->reset();

				tuxxedo_redirect('Option reset to default value', './options.php');
			}
			else
			{
				$query = $db->query('
							SELECT 
								`option`
							FROM
								`' . TUXXEDO_PREFIX . 'options` 
							ORDER BY 
								`option` ASC');

				if(!$query || !$query->getNumRows())
				{
					tuxxedo_error('No options found');
				}

				while($opt = $query->fetchRow())
				{
					Datamanager\Adapter::factory('option', $opt[0])->reset();
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
				$value		= ($opt['value'] !== $opt['defaultvalue'] ? var_dump_option($opt['type'], $opt['value']) : '');
				$defaultvalue	= var_dump_option($opt['type'], $opt['defaultvalue']);
				$cachevalue	= ($cached ? var_dump_option($opt['type'], $datastore->options[$opt['option']]) : 'N/A');

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