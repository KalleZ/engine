<?php
	define('TUXXEDO', 1337);
	require('../../includes/core.php');
	require('../../includes/filter.php');
	require('../../includes/template_compiler.php');
	require('../../includes/functions.php');

	$filter	= new Tuxxedo_Filter;

	if(isset($_POST['progress']))
	{
		$compiler = new Tuxxedo_Template_Compiler;
		$compiler->set($filter->post('sourcecode'));
?>
<h2>Compile result</h2>
<?php

		if($compiler->test() !== true)
		{
			try
			{
				$compiler->compile();
			}
			catch(Tuxxedo_Template_Compiler_Exception $e)
			{
?>
<p><?php echo($e->getMessage()); ?></p>
<?php
			}
		}
		else
		{
			$compiler->compile();
?>
<textarea name="sourcecode" cols="100" rows="15"><?php echo(htmlspecialchars($compiler->get())); ?></textarea>
<?php
		}
	}
?>
<form action="./template_compiler.php" method="post">
	<h2>Template compiler</h2>

	<textarea name="sourcecode" cols="100" rows="15"><?php echo(htmlspecialchars($filter->post('sourcecode'))); ?></textarea>

	<p />
	<input type="submit" name="progress" value="Compile" />
</form>
