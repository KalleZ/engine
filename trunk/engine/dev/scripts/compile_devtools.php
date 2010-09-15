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


	use Tuxxedo\Exception;
	use Tuxxedo\Template;

	define('TEMPLATE_DIR', '../tools/style/templates/');

	require('../../library/Tuxxedo/Exception.php');
	require('../../library/Tuxxedo/Exception/TemplateCompiler.php');
	require('../../library/Tuxxedo/Template/Compiler.php');
	require('../../library/Tuxxedo/Template/Compiler/Dummy.php');
	require('../../library/Tuxxedo/functions.php');

	if(isset($_POST['compile']))
	{
		$templates = glob(TEMPLATE_DIR . '*.raw');

		if(!$templates)
		{
?>
<p>
	There is no templates to compile.
</p>
<?php
			exit;
		}
?>
<h2>Compiling ...</h2>
<ul>
<?php
		$compiler = new Template\Compiler;

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
?>
	<li><?php if(isset($e)) { echo('<strong>'); } echo($template . '... ' . $result); if(isset($e)) { echo('</strong>'); }  ?></li>
<?php
			unset($e);
		}
?>
</ul>
<form action="./compile_devtools.php" method="post">
	<input type="submit" name="compile" value="Re-Compile" />
</form>
<?php
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