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
	use Tuxxedo\Filter;
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
		$style->cache(Array('option', $widget));

		$buffer 	= '';
		$styleid	= $registry->filter->get('style', Filter::TYPE_NUMERIC);

		foreach($registry->cache->styleinfo as $value => $info)
		{
			$name 		= $info['name'];
			$selected	= ($styleid == $value);

			eval('$buffer .= "' . $style->fetch('option') . '";');
		}

		$default 	= ($styleid == $registry->options->style_id);
		$valid		= isset($registry->cache->styleinfo[$styleid]);

		eval('$buffer = "' . $style->fetch($widget) . '";');
		return($buffer);
	}
?>