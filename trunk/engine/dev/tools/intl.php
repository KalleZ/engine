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
										'language_phrasegroup_add_edit_form', 
										'language_phrasegroup_delete', 
										'language_phrasegroup_delete_itembit', 
										'language_phrasegroup_list_itembit', 
										'language_phrasegroup_list'
										), 
					'phrase'		=> Array(
										'language_phrase_add_edit_form', 
										'language_phrase_delete', 
										'language_phrase_list', 
										'language_phrase_list_itembit', 
										'language_phrase_search', 
										'language_phrase_search_itembit'
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
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('phrasegroup', $input->get('id', Input::TYPE_NUMERIC));

					if($dm['languageid'] != $languageid)
					{
						Utilities::headerRedirect('./intl.php?language=' . $dm['languageid'] . '&do=phrasegroup&action=edit&id=' . $dm['id']);
					}
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm 			= Datamanager\Adapter::factory('phrasegroup');
							$dm['languageid']	= $languageid;
						}

						$dm['title'] = $input->post('name');

						$dm->save();

						Utilities::redirect(($action == 'edit' ? 'Edited phrasegroup' : 'Added phrasegroup'), './intl.php?language=' . $languageid . '&do=phrasegroup&action=list');
					}

					eval(page('language_phrasegroup_add_edit_form'));
				}
				break;
				case('delete'):
				{
					$dm = Datamanager\Adapter::factory('phrasegroup', $input->get('id', Input::TYPE_NUMERIC));

					if($dm['languageid'] != $languageid)
					{
						Utilities::headerRedirect('./intl.php?language=' . $dm['languageid'] . '&do=phrasegroup&action=delete&id=' . $dm['id']);
					}

					if(isset($_POST['confirmdelete']))
					{
						$dm->delete();

						Utilities::redirect('Deleted phrasegroup', './intl.php?language=' . $dm['languageid'] . '&do=phrasegroup&action=list');
					}

					$query = $db->equery('
								SELECT 
									`id`, 
									`title` 
								FROM 
									`' . TUXXEDO_PREFIX . 'phrases` 
								WHERE 
										`languageid` = %d
									AND 
										`phrasegroup` = \'%s\'', $languageid, $dm['title']);

					if($query && $query->getNumRows())
					{
						$list = '';

						foreach($query as $row)
						{
							eval('$list .= "' . $style->fetch('language_phrasegroup_delete_itembit') . '";');
						}
					}

					eval(page('language_phrasegroup_delete'));
				}
				break;
				case('list'):
				{
					if(!$datastore->phrasegroups[$languageid])
					{
						throw new Exception('No phrasegroups currently exists');
					}

					$rows = '';

					foreach($datastore->phrasegroups[$languageid] as $name => $pgroup)
					{
						eval('$rows .= "' . $style->fetch('language_phrasegroup_list_itembit') . '";');
					}

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
		case('phrase'):
		{
			switch($action)
			{
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('phrase', $input->get('id', Input::TYPE_NUMERIC));

					if($dm['languageid'] != $languageid)
					{
						Utilities::headerRedirect('./intl.php?language=' . $dm['languageid'] . '&do=phrase&action=edit&id=' . $dm['id']);
					}
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm 			= Datamanager\Adapter::factory('phrasegroup');
							$dm['languageid']	= $languageid;
						}

						$dm['title'] 		= $input->post('name');
						$dm['phrasegroup']	= $input->post('phrasegroup');
						$dm['translation']	= $input->post('translation');

						$dm->save();

						Utilities::redirect(($action == 'edit' ? 'Edited phrase' : 'Added phrase'), './intl.php?language=' . $languageid . '&do=phrase&action=list');
					}

					$phrasegroups_dropdown 	= '';

					$query			= $db->equery('
										SELECT 
											`id`, 
											`title`
										FROM 
											`' . TUXXEDO_PREFIX . 'phrasegroups` 
										WHERE 
											`languageid` = %d
										ORDER BY 
											`title` 
										ASC', $languageid);

					if(!$query || !$query->getNumRows())
					{
						throw new Exception('No phrasegroups currently exists, one must exist to add a phrase');
					}

					foreach($query as $row)
					{
						$value		= $row['id'];
						$name 		= $row['title'];
						$selected 	= (isset($dm) && $name == $dm['phrasegroup']);

						eval('$phrasegroups_dropdown .= "' . $style->fetch('option') . '";');
					}

					$query->free();

					eval(page('language_phrase_add_edit_form'));
				}
				break;
				case('delete'):
				{
					$dm = Datamanager\Adapter::factory('phrase', $input->get('id', Input::TYPE_NUMERIC));

					if($dm['languageid'] != $languageid)
					{
						Utilities::headerRedirect('./intl.php?language=' . $dm['languageid'] . '&do=phrase&action=delete&id=' . $dm['id']);
					}

					if(isset($_POST['confirmdelete']))
					{
						$dm->delete();

						Utilities::redirect('Deleted phrase', './intl.php?language=' . $dm['languageid'] . '&do=phrase&action=list');
					}

					eval(page('language_phrase_delete'));
				}
				break;
				case('reset'):
				{
					$dm = Datamanager\Adapter::factory('phrase', $input->get('id', Input::TYPE_NUMERIC));

					if($dm['changed'])
					{
						$dm->reset();
					}

					Utilities::redirect('Phrase reset to default with success', './intl.php?language=' . $languageid . '&do=phrase&action=list');
				}
				break;
				case('search'):
				{
					$fields = array_filter(Datamanager\Adapter::factory('phrase')->getFields(), function($field)
					{
						static $hidden_fields;

						if(!$hidden_fields)
						{
							$hidden_fields = Array(
										'id'
										);
						}

						return(!in_array($field, $hidden_fields));
					});

					$fields_dropdown = '';

					if(isset($_POST['submit']) && isset($_POST['query']) && $_POST['query'] && isset($_POST['query_field']) && in_array((string) $_POST['query_field'], $fields))
					{
						$safe_query	= htmlspecialchars($input->post('query'), ENT_QUOTES);
						$query 		= str_replace(Array('*', '%'), Array('%', '\%'), $input->post('query'));
						$stripped_query	= str_replace('*', '', $query);

						if($query{0} != '*')
						{
							$query = '%' . $query;
						}

						if(($length = strlen($query)) !== false && $query{$length - 1} != '*')
						{
							$query .= '%';
						}

						$query = $db->equery('
									SELECT 
										`id`, 
										`title`, 
										`changed`, 
										`phrasegroup`
									FROM 
										`' . TUXXEDO_PREFIX . 'phrases` 
									WHERE 
										`%s` LIKE \'%s\'', $input->post('query_field'), $query);


						if(!$query || !$query->getNumRows())
						{
							throw new Exception('Search returned zero results');
						}

						$table 		= '';
						$matches	= $query->getNumRows();

						while($phrase = $query->fetchArray())
						{
							eval('$table .= "' . $style->fetch('language_phrase_search_itembit') . '";');
						}
					}

					foreach($fields as $value)
					{
						$name 		= ucfirst($value);
						$selected	= (isset($safe_query) && $value == (string) $_POST['query_field']);

						eval('$fields_dropdown .= "' . $style->fetch('option') . '";');
					}

					eval(page('language_phrase_search'));
				}
				break;
				case('list'):
				{
					$query = $db->equery('
								SELECT 
									`id`, 
									`title`, 
									`changed`, 
									`phrasegroup`
								FROM 
									`' . TUXXEDO_PREFIX . 'phrases` 
								WHERE 
									`languageid` = %d
								ORDER BY 
									`id`
								ASC', $languageid);

					if($query && $query->getNumRows())
					{
						$rows = '';

						foreach($query as $row)
						{
							eval('$rows .= "' . $style->fetch('language_phrase_list_itembit') . '";');
						}

						eval(page('language_phrase_list'));
					}
					else
					{
						throw new Exception('No phrases currently exists');
					}
				}
				break;
				default:
				{
					throw new Exception('Invalid phrase action');
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