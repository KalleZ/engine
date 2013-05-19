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
	 * @subpackage		Dev
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Utilities\IO;


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');


	/**
	 * Template directory path
	 *
	 * @var		string
	 */
	define('TEMPLATE_DIR', realpath(__DIR__ . '../../..') . '/styles/default/templates');

	IO::signature();

	$cli = IO::isCLI();

	if($cli || isset($_POST['sync']))
	{
		$registry->register('db', '\Tuxxedo\Database');
		$registry->register('datastore', '\Tuxxedo\Datastore');

		$datastore->cache(Array('options'));

		$templates = $db->query('
						SELECT 
							* 
						FROM 
							`' . TUXXEDO_PREFIX . 'templates` 
						WHERE 
							`styleid` = %d
						ORDER BY 
							`title` 
						ASC', $datastore->options['style_id']);

		if(!$templates)
		{
			IO::text('There is no templates to syncronize for the default style');
			exit;
		}

		IO::headline('Template Synchronizer');

		IO::ul();

		while($template = $templates->fetchAssoc())
		{
			file_put_contents(TEMPLATE_DIR . '/' . $template['title'] . '.tuxx', $template['compiledsource']);

			IO::li((!$cli ? $template['title'] . '... Success' : str_pad($template['title'] . '... ', 40, ' ') . 'success'));
		}

		IO::ul(IO::TAG_END);

		if(!$cli)
		{
?>
<form action="./sync_templates.php" method="post">
	<input type="submit" name="sync" value="Re-synchronize" />
</form>
<?php
		}
	}
	else
	{
?>
<h2>Template Synchronizer</h2>
<p>
	This tool synchronize the database based templates 
	within their filesystem counterparts. It's intended 
	for use until the development tools have built-in 
	support for such a feature.
</p>
<form action="./sync_templates.php" method="post">
	<input type="submit" name="sync" value="Synchronize" />
</form>
<?php
	}
?>