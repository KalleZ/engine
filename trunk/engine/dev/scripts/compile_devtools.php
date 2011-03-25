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
	use Tuxxedo\Exception;
	use Tuxxedo\Template\Compiler;


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');


	/**
	 * Template directory path
	 *
	 * @var		string
	 */
	const TEMPLATE_DIR		= '../tools/style/templates';


	$cli = IO::isCLI();

	if($cli || isset($_POST['compile']))
	{
		$templates = glob(TEMPLATE_DIR . '*.raw');

		if(!$templates)
		{
			IO::text('There is no templates to compile');
			exit;
		}

		IO::headline('Compiling...');
		IO::ul(IO::TAG_START);

		$compiler = new Compiler;
		$compiler->allowFunction('strlen');

		foreach($templates as $template)
		{
			$result 	= 'Failed';
			$template	= pathinfo($template, PATHINFO_FILENAME);

			try
			{
				$compiler->set(file_get_contents(TEMPLATE_DIR . '/' . $template . '.raw'));
				$compiler->compile();

				file_put_contents(TEMPLATE_DIR . '/' . $template . '.tuxx', $compiler->get());

				$result = 'Success';
			}
			catch(Exception\TemplateCompiler $e)
			{
			}

			IO::li($template . '...' . (isset($e) ? 'Failed (' . $e->getMessage() . ')' : (!$cli ? 'Success' : '')), (isset($e) ? IO::STYLE_BOLD : 0));

			unset($e);
		}

		IO::ul(IO::TAG_END);

		if(!$cli)
		{
?>
<form action="./compile_devtools.php" method="post">
	<input type="submit" name="compile" value="Re-Compile" />
</form>
<?php
		}
	}
	else
	{
?>
<h2>DevTools compiler</h2>
<p>
	This tool re-compiles the .raw files into 
	.tuxx executable files for the development 
	tools templates.
</p>
<form action="./compile_devtools.php" method="post">
	<input type="submit" name="compile" value="Compile" />
</form>
<?php
	}
?>