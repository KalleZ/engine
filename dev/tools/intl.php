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
					'intl_index', 
					'option'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'language'		=> Array(
										'language_add_edit_form'
										)
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'languages'
					);

	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'intl';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	$do 	= strtolower($input->get('do'));
	$action = strtolower($input->get('action'));

	if(($languageid = $input->get('language', Input::TYPE_NUMERIC)))
	{
		if(!isset($datastore->languages[$languageid]))
		{
			tuxxedo_error('Invalid language');
		}

		$languagedm 	= Datamanager\Adapter::factory('language', $languageid);
		$languagedata	= $languagedm->get();
	}
	elseif(!$languageid && !empty($do) && $do != 'language' && $action != 'add')
	{
		tuxxedo_error('Invalid language');
	}

	switch($do)
	{
		case('language'):
		{
			switch($action)
			{
				case('add'):
				case('edit'):
				{
					if($action == 'add' && isset($languagedm))
					{
						tuxxedo_header_redirect('./intl.php?do=language&action=add');
					}
					elseif($action == 'edit' && !isset($languagedm))
					{
						tuxxedo_error('Invalid language id');
					}
					elseif(isset($_POST['submit']))
					{
						if($action == 'edit' && !isset($_POST['defaultlanguage']) && sizeof($datastore->languages) < 2)
						{
							unset($languagedm);

							tuxxedo_error('Cannot disable default language when there only is one');
						}
						elseif($action == 'add')
						{
							$languagedm 		= Datamanager\Adapter::factory('language');
							$languagedm['inherit']	= $input->post('inherit');
						}

						$languagedm['title'] 		= $input->post('title');
						$languagedm['developer']	= $input->post('developer');
						$languagedm['isotitle']		= $input->post('isotitle');
						$languagedm['charset']		= $input->post('charset');
						$languagedm['isdefault']	= $input->post('defaultlanguage', Input::TYPE_BOOLEAN);

						$languagedm->save();

						tuxxedo_redirect('Saved language with success', './intl.php?language=' . $languagedm['id'] . '&do=language&action=edit');
					}
					else
					{
						if($action == 'add')
						{
							$languages_dropdown = '';

							foreach($datastore->languages as $value => $data)
							{
								$name 		= $data['title'];
								$selected	= ($options->language_id == $value);

								eval('$languages_dropdown .= "' . $style->fetch('option') . '";');
							}
						}

						eval(page('language_add_edit_form'));
					}
				}
				break;
				case('delete'):
				{
					if(!isset($languagedm))
					{
						tuxxedo_error('Invalid language id');
					}
					elseif($languagedata['id'] == $options->language_id)
					{
						tuxxedo_error('Cannot delete the default language');
					}

					$languagedm->delete();

					tuxxedo_redirect('Deleted language with success', './intl.php');
				}
				default:
				{
					tuxxedo_error('Invalid language action');
				}
			}
		}
		break;
		default:
		{
			eval(page('intl_index'));
		}
		break;
	}
?>