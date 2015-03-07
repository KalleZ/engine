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


	$locations = [
			'Tuxxedo Engine DevTools' => '/tools/style/templates/'
			];

	IO::signature();

	$cli = IO::isCLI();

	if($cli || isset($_POST['compile']))
	{
		$basepath = realpath(__DIR__ . '/..');

		$compiler = new Compiler;
	
		$compiler->allowFunction('strlen');
		$compiler->allowFunction('strtolower');
		$compiler->allowFunction('in_array');

		foreach($locations as $app => $path)
		{
			$pathlen	= strlen($path);
			$wildcard	= ($path{$pathlen - 1} == '*');
			$path		= ($wildcard ? substr($path, 0, -1) : $path);
			$glob		= ($wildcard ? 'recursive_glob' : 'glob');
			$templates 	= $glob($basepath . $path . '*.raw');

			IO::headline('Compiling \'' . $app . '\'');

			if(!$templates)
			{
				IO::text('There is no templates to compile');

				continue;
			}

			IO::ul(IO::TAG_START);

			foreach($templates as $template)
			{
				$temp = pathinfo($template, PATHINFO_FILENAME);

				try
				{
					$compiler->setSource(file_get_contents($template));
					$compiler->compile();

					file_put_contents(substr_replace($template, '.tuxx', -4, 4), $compiler->getCompiledSource());

					IO::li((!$cli ? $temp . '... Success' : str_pad($temp . '... ', 40, ' ') . 'Success'));
				}
				catch(Exception\TemplateCompiler $e)
				{
					$failed = true;

					IO::li((!$cli ? $temp . '... Failed' : str_pad($temp . '... ', 40, ' ') . 'Failed'), IO::STYLE_BOLD);
					IO::li($e->getMessage(), IO::STYLE_BOLD | IO::STYLE_HIDDEN_DOT);
					IO::li('', IO::STYLE_HIDDEN_DOT);
				}
			}

			IO::ul(IO::TAG_END);
		}

		if(isset($failed))
		{
			IO::text(IO::eol());
			IO::text('One or more templates failed to compile! Go back fix the compilation errors');
		}

		if(!$cli)
		{
?>
<form action="./compile_dev.php" method="post">
	<input type="submit" name="compile" value="Re-compile" />
</form>
<?php
		}
	}
	else
	{
?>
<h2>Dev compiler</h2>
<p>
	This tool re-compiles development applications used to 
	develop Tuxxedo Engine.
</p>
<form action="./compile_dev.php" method="post">
	<input type="submit" name="compile" value="Compile" />
</form>
<?php
	}
?>