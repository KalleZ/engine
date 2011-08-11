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
	use Tuxxedo\Template\Compiler;


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
									), 
					'templates'	=> Array(
									'templates_add_edit_form', 
									'templates_list', 
									'templates_list_itembit', 
									'templates_search', 
									'templates_search_itembit'
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
	const SCRIPT_NAME 	= 'styles';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	$do 	= strtolower($input->get('do'));
	$action = strtolower($input->get('action'));

	if(($styleid = $input->get('style', Input::TYPE_NUMERIC)))
	{
		if(!isset($cache->styleinfo[$styleid]))
		{
			tuxxedo_error('Invalid style');
		}

		$styledm 	= Datamanager\Adapter::factory('style', $styleid, 0);
		$styledata	= $styledm->get();
	}
	elseif(!$styleid && !empty($do) && $do != 'style' && $action != 'add')
	{
		tuxxedo_error('Invalid style');
	}

	switch($do)
	{
		case('style'):
		{
			switch($action)
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
			switch($action)
			{
				case('list'):
				{
					$templateids = explode(',', $cache->styleinfo[$styleid]['templateids']);

					if(!$templateids || empty($templateids[0]))
					{
						tuxxedo_error('This style have no templates');
					}

					$table = '';

					foreach($templateids as $id)
					{
						$template = Datamanager\Adapter::factory('template', $id, Datamanager\Adapter::OPT_LOAD_ONLY);

						eval('$table .= "'. $style->fetch('templates_list_itembit') . '";');
					}

					eval(page('templates_list'));
				}
				break;
				case('search'):
				{
					if(isset($_POST['query']) && !empty($_POST['query']))
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

						if(isset($_POST['search_changed']) && $_POST['search_changed'])
						{
							$query = $db->equery('
										SELECT 
											`id`, 
											`title`, 
											`revision`, 
											`changed`, 
											`source`
										FROM 
											`' . TUXXEDO_PREFIX . 'templates` 
										WHERE 
											`source` 
										LIKE 
											\'%s\'
										AND 
											`changed` = \'1\'
										AND 
											`styleid` = %d', $query, $styleid);
						}
						else
						{
							$query = $db->equery('
										SELECT 
											`id`, 
											`title`, 
											`revision`, 
											`changed`, 
											`source` 
										FROM 
											`' . TUXXEDO_PREFIX . 'templates` 
										WHERE 
											`source` 
										LIKE 
											\'%s\'
										AND 
											`styleid` = %d', $query, $styleid);
						}

						if(!$query || !$query->getNumRows())
						{
							tuxxedo_error('Search return zero results');
						}

						$table = '';

						while($template = $query->fetchArray())
						{
							$matched_strings	= 0;
							$pos			= 0;

							while($pos = strpos($template['source'], $stripped_query, $pos))
							{
								++$matched_strings;
								++$pos;
							}

							eval('$table .= "' . $style->fetch('templates_search_itembit') . '";');
						}

						$matches = $query->getNumRows();
					}

					eval(page('templates_search'));
				}
				break;
				case('delete'):
				{
					try
					{
						$dm = Datamanager\Adapter::factory('template', $input->get('id'), Datamanager\Adapter::OPT_LOAD_ONLY);
					}
					catch(Exception $e)
					{
						tuxxedo_error('Invalid template id');
					}

					$dm->delete();

					tuxxedo_redirect('Template deleted with success', './styles.php?style=' . $styleid . '&do=templates&action=list');
				}
				break;
				case('edit'):
				{
					try
					{
						$dm 	= Datamanager\Adapter::factory('template', $input->get('id'), 0);
						$source	= htmlspecialchars($dm['source'], ENT_QUOTES, 'UTF-8');
					}
					catch(Exception $e)
					{
						tuxxedo_error('Invalid template id');
					}
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						$title 	= strtolower($input->post('title'));
						$source	= $input->post('source');

						if(empty($title))
						{
							tuxxedo_error('Template titles may not be blank');
						}

						if(!isset($dm))
						{
							$dm 		= Datamanager\Adapter::factory('template', NULL, 0);
							$dm['styleid']	= $styleid;
						}
						else
						{
							$dm['changed']	= true;
						}

						$dm['title'] 	= $title;
						$dm['revision']	+= 1;

						if($dm['source'] != $source)
						{
							try
							{
								$compiler = new Compiler;

								$compiler->set($source);
								$compiler->compile();

								if(!$compiler->test())
								{
									throw new Exception\TemplateCompiler('Source code does not pass compiler test, possily syntax error');
								}
							}
							catch(Exception\TemplateCompiler $e)
							{
								tuxxedo_error('Template compiler error: ' . $e->getMessage());
							}

							$dm['source'] 		= $source;
							$dm['compiledsource']	= $compiler->get();

							if(isset($_POST['sourceoverride']))
							{
								$dm['defaultsource'] = $dm['compiledsource'];
							}
						}

						if(!$dm->save())
						{
							tuxxedo_error('Unable to save template data');
						}

						tuxxedo_redirect(($action == 'edit' ? 'Template edited with success' : 'Template added with success'), './styles.php?style=' . $styleid . '&do=templates&action=list');
					}

					eval(page('templates_add_edit_form'));
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