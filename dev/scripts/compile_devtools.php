<?php
	define('TUXXEDO', 1337);
	define('TEMPLATE_DIR', '../tools/style/templates/');

	require('../../library/Tuxxedo/Exception.php');
	require('../../library/Tuxxedo/Exception/TemplateCompiler.php');
	require('../../library/Tuxxedo/Template/Compiler.php');
	require('../../library/Tuxxedo/Template/Compiler/Dummy.php');
	require('../../library/Tuxxedo/functions.php');

	if(isset($_POST['compile']))
	{
		$templates = glob(TEMPLATE_DIR . '*.raw');

		if(!sizeof($templates))
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
		$compiler = new Tuxxedo\Template\Compiler;

		$compiler->allowFunction('strlen');

		foreach($templates as $template)
		{
			$result 	= 'Failed';
			$template	= explode('/', str_replace('.raw', '', $template));
			$template	= $template[sizeof($template) - 1];

			try
			{
				$compiler->set(file_get_contents(TEMPLATE_DIR . '/' . $template . '.raw'));
				$compiler->compile();

				file_put_contents(TEMPLATE_DIR . '/' . $template . '.tuxx', $compiler->get());

				$result = 'Success';
			}
			catch(Template_Compiler_Exception $e)
			{
			}
?>
	<li><?php echo($template . '... ' . $result); ?></li>
<?php
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
