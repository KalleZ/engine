<?php
	require('./includes/bootstrap.php');
	require(TUXXEDO_DIR . '/includes/functions_misc.php');

	if(isset($_POST['password']) && !empty($_POST['password']))
	{
		$salt = generate_password_salt($_POST['length']);
		$hash = password_hash($_POST['password'], $salt);

		echo('<textarea rows="10" style="width: 90%;">');
		echo('password: ' . htmlspecialchars($_POST['password']) . PHP_EOL);
		echo('hashed value: ' . $hash . PHP_EOL);
		echo('salt: ' . $salt . PHP_EOL);
		echo(PHP_EOL);
		echo('UPDATE `' . TUXXEDO_PREFIX . 'users` SET `password` = \'' . $hash . '\', `salt` = \'' . $db->escape($salt) . '\' WHERE `id` = ');
		echo('</textarea>');
	}
	else
	{
		echo('<h4>Password generator</h4>');

		echo('<p>Enter a password to generate a salt and its hashed value</p>');
		echo('<p>The dropdown can be used to select the length of the salt, defaults to 8</p>');
		echo('<form action="./password.php" method="post">');
		echo('<input type="password" name="password" />');
		echo('<select name="length">');

		foreach(Array(8, 24, 48) as $l)
		{
			echo('<option value="' . $l . '">' . $l . '</option>');
		}

		echo('</select>');
		echo('<input type="submit" value="Generate" />');
		echo('</form>');
	}
?>