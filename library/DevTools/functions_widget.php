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
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Style;
	use Tuxxedo\Registry;


	/**
	 * Widget hook function - styles
	 *
	 * @param	\Devtools\Style		The Devtools style object
	 * @param	\Tuxxedo\Registry	The registry reference
	 * @param	string			The template name of the widget
	 * @return	string			Returns the compiled sidebar widget
	 */
	function widget_hook_styles(Style $style, Registry $registry, $widget)
	{
		$registry->cache->cache(Array('styleinfo'));
		$style->cache(Array('option', $widget));

		$buffer = '';

		foreach($registry->cache->styleinfo as $value => $info)
		{
			$name = $info['name'];

			eval('$buffer .= "' . $style->fetch('option') . '";');
		}

		eval('$buffer = "' . $style->fetch($widget) . '";');
		return($buffer);
	}
?>