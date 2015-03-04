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
	use DevTools\Style;
	use Tuxxedo\Input;
	use Tuxxedo\Registry;



	/**
	 * Widget hook function - index
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.3.0
	 */
	function widget_hook_index(Style $style, Registry $registry, $widget)
	{
		$have_apidocs = is_file('../scripts/apidump/output/index.html');

		eval('$sidebar = "' . $style->fetch($widget) . '";');

		return($sidebar);
	}

	/**
	 * Widget hook function - datastore
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.2.0
	 */
	function widget_hook_datastore(Style $style, Registry $registry, $widget)
	{
		static $total_size;

		if($total_size === NULL && !isset($_POST['progress']))
		{
			$total_size	= 0;
			$ds 		= $registry->db->query('
								SELECT 
									"data" 
								FROM 
									"' . TUXXEDO_PREFIX . 'datastore"');

			if($ds && $ds->getNumRows())
			{
				foreach($ds as $row)
				{
					$total_size += strlen($row['data']);
				}
			}
		}

		eval('$sidebar = "' . $style->fetch($widget) . '";');

		return($sidebar);
	}

	/**
	 * Widget hook function - styles
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.1.0
	 */
	function widget_hook_styles(Style $style, Registry $registry, $widget)
	{
		$buffer 	= '';
		$styleid	= $registry->input->get('style', Input::TYPE_NUMERIC);

		foreach($registry->datastore->styleinfo as $value => $info)
		{
			$name 		= $info['name'];
			$selected	= ($styleid == $value);

			eval('$buffer .= "' . $style->fetch('option') . '";');
		}

		$default 	= ($styleid == $registry->options->style_id);
		$valid		= isset($registry->datastore->styleinfo[$styleid]);

		eval('$buffer = "' . $style->fetch($widget) . '";');

		return($buffer);
	}

	/**
	 * Widget hook function - intl
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.2.0
	 */
	function widget_hook_intl(Style $style, Registry $registry, $widget)
	{
		$buffer 	= '';
		$languageid	= $registry->input->get('language', Input::TYPE_NUMERIC);

		foreach($registry->datastore->languages as $value => $info)
		{
			$name 		= $info['title'];
			$selected	= ($languageid == $value);

			eval('$buffer .= "' . $style->fetch('option') . '";');
		}

		$default 	= ($languageid == $registry->options->language_id);
		$valid		= isset($registry->datastore->languages[$languageid]);

		eval('$buffer = "' . $style->fetch($widget) . '";');

		return($buffer);
	}

	/**
	 * Widget hook function - sessions
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.1.0
	 */
	function widget_hook_sessions(Style $style, Registry $registry, $widget)
	{
		$buffer		= '';
		$refresh_values = [
					0	=> 'Disabled', 
					5	=> '5 Seconds', 
					10	=> '10 Seconds', 
					15	=> '15 Seconds', 
					30	=> '30 Seconds', 
					60	=> '1 Minute'
					];

		if(isset($_POST['autorefresh']) && isset($refresh_values[$registry->input->post('autorefresh', Input::TYPE_NUMERIC)]))
		{
			$registry->cookie->set('devtools_session_autorefresh', $registry->input->post('autorefresh', Input::TYPE_NUMERIC));
		}
		elseif(!isset($registry->cookie['devtools_session_autorefresh']))
		{
			$registry->cookie->set('devtools_session_autorefresh', 0);
		}

		foreach($refresh_values as $value => $name)
		{
			$selected = ($registry->cookie['devtools_session_autorefresh'] == $value);

			eval('$buffer .= "' . $style->fetch('option') . '";');
		}

		$refresh_timer = $registry->cookie['devtools_session_autorefresh'];

		eval('$sidebar = "' . $style->fetch($widget) . '";');

		return($sidebar);
	}

	/**
	 * Widget hook function - users
	 *
	 * @param	\DevTools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 *
	 * @since	1.1.0
	 */
	function widget_hook_users(Style $style, Registry $registry, $widget)
	{
		$any_usergroups		= sizeof($registry->datastore->usergroups);
		$any_permissions 	= sizeof($registry->datastore->permissions);

		eval('$sidebar = "' . $style->fetch($widget) . '";');

		return($sidebar);
	}
?>