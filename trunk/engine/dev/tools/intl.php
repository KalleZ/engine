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
	use DevTools\Utilities;
	use Tuxxedo\Datamanager;
	use Tuxxedo\Exception;
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
										), 
					'phrasegroup'		=> Array(
										'language_phrasegroup_list'
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
	 *
	 * @var		string
	 * @since	1.2.0
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
			throw new Exception('Invalid language');
		}

		$languagedm 	= Datamanager\Adapter::factory('language', $languageid);
		$languagedata	= $languagedm->get();
	}
	elseif(!$languageid && !empty($do) && $do != 'language' && $action != 'add')
	{
		throw new Exception('Invalid language');
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
						Utilities::headerRedirect('./intl.php?do=language&action=add');
					}
					elseif($action == 'edit' && !isset($languagedm))
					{
						throw new Exception('Invalid language id');
					}
					elseif(isset($_POST['submit']))
					{
						if($action == 'edit' && !isset($_POST['defaultlanguage']) && sizeof($datastore->languages) < 2)
						{
							unset($languagedm);

							throw new Exception('Cannot disable default language when there only is one');
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

						Utilities::redirect('Saved language with success', './intl.php?language=' . $languagedm['id'] . '&do=language&action=edit');
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
						throw new Exception('Invalid language id');
					}
					elseif($languagedata['id'] == $options->language_id)
					{
						throw new Exception('Cannot delete the default language');
					}

					$languagedm->delete();

					Utilities::redirect('Deleted language with success', './intl.php');
				}
				default:
				{
					throw new Exception('Invalid language action');
				}
			}
		}
		break;
		case('phrasegroup'):
		{
			switch($action)
			{
				case('list'):
				{
					eval(page('language_phrasegroup_list'));
				}
				break;
				default:
				{
					throw new Exception('Invalid phrasegroup action');
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