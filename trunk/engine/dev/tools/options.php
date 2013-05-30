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
					'option', 
					'options_add_edit_form', 
					'options_category', 
					'options_category_itembit', 
					'options_index'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'categories'	=> Array(
									'options_category_add_edit_form', 
									'options_category_delete', 
									'options_category_delete_itembit'
									)
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'optioncategories', 
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
		case('categories'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('optioncategory', $input->get('category'));
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm = Datamanager\Adapter::factory('optioncategory');
						}

						$dm['name'] = $input->post('name');

						$dm->save();

						tuxxedo_redirect(($action == 'edit' ? 'Edited option category' : 'Added option category'), './options.php');
					}

					eval(page('options_category_add_edit_form'));
				}
				break;
				case('delete'):
				{
					$dm = Datamanager\Adapter::factory('optioncategory', $input->get('category'));

					if(isset($_POST['confirmdelete']))
					{
						$dm->delete();

						tuxxedo_redirect('Deleted category', './options.php');
					}

					$query = $db->equery('
								SELECT 
									`option` 
								FROM 
									`' . TUXXEDO_PREFIX . 'options` 
								WHERE 
									`category` = \'%s\'', $dm['name']);

					if($query && $query->getNumRows())
					{
						$list = '';

						foreach($query as $row)
						{
							eval('$list .= "' . $style->fetch('options_category_delete_itembit') . '";');
						}
					}

					eval(page('options_category_delete'));
				}
				break;
				default:
				{
					tuxxedo_error('Invalid action');
				}
				break;
			}
		}
		break;
		case('options'):
		default:
		{
			switch($action = strtolower($input->get('action')))
			{
				case('add'):
				{
					if($input->post('submit'))
					{
						$opt 			= Datamanager\Adapter::factory('option');
						$opt['option']		= $input->post('name');
						$opt['type']		= $input->post('characters');
						$opt['value']		= $input->post('value');
						$opt['category']	= $input->post('category');

						$opt->save();

						tuxxedo_redirect('Added option', './options.php');
					}
					else
					{
						$categories_dropdown = options_categories_dropdown();

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
						$opt['category']	= $input->post('category');

						$opt->save();

						tuxxedo_redirect('Edited option', './options.php');
					}
					else
					{
						$opt['value']		= htmlspecialchars($opt['value']);
						$opt['defaultvalue']	= htmlspecialchars($opt['value']);

						$categories_dropdown = options_categories_dropdown($opt['category']);

						eval(page('options_add_edit_form'));
					}
				}
				break;
				case('delete'):
				{
					Datamanager\Adapter::factory('option', $input->get('option'))->delete();

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

					$reminder	= false;
					$found		= Array();
					$options	= Array();
					$orphan		= Array();

					while($opt = $query->fetchAssoc())
					{
						$option					= $opt['option'];
						$found[]				= $option;
						$options[$option] 			= $opt;
						$options[$option]['cached']		= isset($datastore->options[$option]);
						$options[$option]['dumpvalue']		= ($opt['value'] !== $opt['defaultvalue'] ? var_dump_option($opt['type'], $opt['value']) : '');
						$options[$option]['defaultvalue']	= var_dump_option($opt['type'], $opt['defaultvalue']);
						$options[$option]['cachedvalue']	= ($options[$option]['cached'] ? var_dump_option($opt['type'], $datastore->options[$opt['option']]['value']) : 'N/A');

						if(!in_array($opt['category'], $datastore->optioncategories))
						{
							$reminder 		= true;
							$orphan[$option]	= $options[$option];
						}

						if(!$options[$option]['cached'] || ($options[$option]['cached'] && $options[$option]['value'] != $datastore->options[$option]['value']))
						{
							$reminder = true;
						}
					}

					if(array_diff(array_keys($datastore->options), $found))
					{
						$reminder = true;
					}

					$categorized = options_categorize($options);

					if($categorized)
					{
						$categories = '';

						foreach($categorized as $category => $options)
						{
							if($options)
							{
								$rows = '';

								foreach($options as $option => $data)
								{
									eval('$rows .= "' . $style->fetch('options_category_itembit') . '";');
								}
							}

							eval('$categories .= "' . $style->fetch('options_category') . '";');

							unset($rows);
						}
					}

					if($orphan)
					{
						$rows = '';

						foreach($orphan as $option => $data)
						{
							eval('$rows .= "' . $style->fetch('options_category_itembit') . '";');
						}
					}

					eval(page('options_index'));
				}
				break;
			}
		}
		break;
	}
?>