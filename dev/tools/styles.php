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
	use Tuxxedo\Filter;


	/**
	 * Global templates
	 */
	$templates 		= Array(
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


	if(($styleid = $filter->get('style', Filter::TYPE_NUMERIC)))
	{
		if(!isset($cache->styleinfo[$styleid]))
		{
			tuxxedo_error('Invalid style id');
		}

		$styledm 	= Datamanager\Adapter::factory('style', $styleid, false);
		$styledata	= $styledm->get();
	}

	switch($do = strtolower($filter->get('do')))
	{
		case('style'):
		{
			switch($action = strtolower($filter->get('action')))
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

					eval(page('styles_add_edit_form'));
				}
				break;
				case('delete'):
				{
					if(!isset($styledm))
					{
						tuxxedo_error('Invalid style id');
					}
					elseif($styledata['default'] || $styledata['id'] == $options->style_id)
					{
						tuxxedo_error('Cannot delete the default style');
					}

					$styledm->delete();

					tuxxedo_redirect('Deleted style with success', './styles.php');
				}
				break;
				default:
				{
					tuxxedo_error('Invalid style action');
				}
			}
		}
		break;
		case('templates'):
		{
			switch($action = strtolower($filter->get('action')))
			{
				case('add'):
				case('edit'):
				case('delete'):
				{
					throw new Exception\Core('Template handlers not implemented');
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