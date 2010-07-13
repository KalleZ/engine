<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Extended hook callback for exceptions
	 *
	 * @param	Exception		The caught exception
	 * @return	void			No value is returned
	 */
	function tuxxedo_hook_exceptions(Exception $e)
	{
		if($e instanceof Tuxxedo_Formdata_Exception)
		{
			global $tuxxedo;

			$cache_buffer = Array();

			$tuxxedo->style->cache(Array('error_validation', 'error_validationbit'))  or tuxxedo_multi_error('Unable to load template \'%s\'', $cache_buffer);

			$list 		= '';
			$template	= $tuxxedo->style->fetch('error_validationbit');

			foreach($e->getFields() as $name)
			{
				eval('$list .= "' . $template . '";');
			}

			eval(page('error_validation'));
			return(true);
		}
	}
?>