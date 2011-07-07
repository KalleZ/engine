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
	use Tuxxedo\Exception;
	use Tuxxedo\Input;


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'option', 
					'styles_index'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'style'		=> Array(
									'styles_add_edit_form'
									)
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'options', 
					'styleinfo'
					);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'styles');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	if(($styleid = $input->get('style', Input::TYPE_NUMERIC)))
	{
		if(!isset($cache->styleinfo[$styleid]))
		{
			tuxxedo_error('Invalid style id');
		}

		$styledm 	= Datamanager\Adapter::factory('style', $styleid, 0);
		$styledata	= $styledm->get();
	}

	switch($do = strtolower($input->get('do')))
	{
		case('style'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('add'):
				case('edit'):
				{
					if($action == 'add' && isset($styledm))
					{
						tuxxedo_header_redirect('./styles.php?do=style&action=add');
					}
					elseif($action == 'edit' && !isset($styledm))
					{
						tuxxedo_error('Invalid style id');
					}
					elseif(isset($_POST['submit']))
					{
						if($action == 'edit' && !isset($_POST['defaultstyle']) && sizeof($cache->styledata) < 2)
						{
							tuxxedo_error('Cannot disable default style when there only is one');
						}
						elseif($action == 'add')
						{
							$styledm 		= Datamanager\Adapter::factory('style', NULL, 0);
							$styledm['inherit']	= $input->post('inherit');
						}

						$styledm['name'] 		= $input->post('name');
						$styledm['developer']		= $input->post('developer');
						$styledm['styledir']		= $input->post('styledir');
						$styledm['defaultstyle']	= $input->post('defaultstyle', Input::TYPE_BOOLEAN);

						$styledm->save();

						tuxxedo_redirect('Saved style with success', './styles.php?style=' . $styledm->get('id') . '&do=style&action=edit');
					}
					else
					{
						if($action == 'add')
						{
							$styles_dropdown = '';

							foreach($cache->styleinfo as $value => $data)
							{
								$name 		= $data['name'];
								$selected	= ($options->style_id == $value);

								eval('$styles_dropdown .= "' . $style->fetch('option') . '";');
							}
						}

						eval(page('styles_add_edit_form'));
					}
				}
				break;
				case('delete'):
				{
					if(!isset($styledm))
					{
						tuxxedo_error('Invalid style id');
					}
					elseif($styledata['id'] == $options->style_id)
					{
						tuxxedo_error('Cannot delete the default style');
					}

					$styledm->delete();

					tuxxedo_redirect('Deleted style with success', './styles.php');
				}
				default:
				{
					tuxxedo_error('Invalid style action');
				}
			}
		}
		break;
		case('templates'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('add'):
				case('edit'):
				case('delete'):
				case('list'):
				case('search'):
				{
					throw new Exception\Core('Template handlers are yet to be implemented');
				}
				break;
				default:
				{
					tuxxedo_error('Invalid template action');
				}
			}
		}
		break;
		default:
		{
			eval(page('styles_index'));
		}
		break;
	}
?>