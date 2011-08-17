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
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Test;
	use DevTools\User;
	use Tuxxedo\Input;
	use Tuxxedo\Template\Compiler;

	/**
	 * Global templates
	 */
	$templates 		= Array(
					'tools_index'
					);

	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'statistics'		=> Array(
										'tools_statistics', 
										'tools_statistics_itembit'
										), 
					'password'		=> Array(
										'tools_password', 
										'tools_password_result'
										), 
					'requirements'		=> Array(
										'tools_requirements', 
										'tools_requirements_itembit'
										), 
					'compiler'		=> Array(
										'tools_compiler'
										), 
					'authentication'	=> Array(
										'tools_authentication'
										), 
					'permissions'		=> Array(
										'option', 
										'tools_permissions', 
										'tools_permissions_itembit'
										)
					);

	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'options', 
					'permissions'
					);

	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'tools';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	switch(strtolower($input->get('do')))
	{
		case('statistics'):
		{
			$files = recursive_glob('../..');

			if(!$files)
			{
				tuxxedo_error('No source files found in the root directory');
			}

			$ignored	= Array(
						'png', 
						'sqlite3'
						);

			$statistics 	= Array(
						'lines'		=> Array(), 
						'size'		=> Array(), 
						'files'		=> Array(), 
						'total'		=> Array(
										'files'		=> 0, 
										'lines'		=> 0, 
										'size'		=> 0
										), 
						'extensions'	=> Array(
										'php'		=> Array(
														'tokens'	=> 0
													)
										)
						);

			foreach($files as $path)
			{
				$path		= '../../' . $path;
				$extension 	= pathinfo($path, PATHINFO_EXTENSION);

				if(in_array(strtolower($extension), $ignored))
				{
					continue;
				}

				if(!isset($statistics['lines'][$extension]))
				{
					$statistics['lines'][$extension] = 0;
				}

				if(!isset($statistics['size'][$extension]))
				{
					$statistics['size'][$extension] = 0;
				}

				if(!isset($statistics['files'][$extension]))
				{
					$statistics['files'][$extension] = 0;
				}

				if(!isset($statistics['extensions'][$extension]))
				{
					$statistics['extensions'][$extension] 	= Array(
											'blanks'	=> 0
											);
				}
				elseif(!isset($statistics['extensions'][$extension]['blanks']))
				{
					$statistics['extensions'][$extension]['blanks'] = 0;
				}

				if(stripos($extension, 'php') !== false)
				{
					$statistics['extensions'][$extension]['tokens'] += sizeof(token_get_all(file_get_contents($path)));
				}

				foreach($l = file($path) as $line)
				{
					$line = trim($line);

					if(empty($line))
					{
						++$statistics['extensions'][$extension]['blanks'];
					}
				}

				$statistics['lines'][$extension] 	+= sizeof($l);
				$statistics['size'][$extension]		+= ($s = filesize($path));

				$statistics['total']['lines']		+= sizeof($l);
				$statistics['total']['size']		+= $s;

				++$statistics['total']['files'];
				++$statistics['files'][$extension];
			}

			ksort($statistics['lines']);

			$extensions = '';

			foreach($statistics['lines'] as $ext => $lines)
			{
				if(!$statistics['files'][$ext])
				{
					continue;
				}

				$name = strtoupper($ext);

				eval('$extensions .= "' . $style->fetch('tools_statistics_itembit') . '";');
			}

			$statistics['lines'] 		= sizeof($statistics['lines']);
			$statistics['extensions']	= sizeof(array_keys($statistics['files']));

			eval(page('tools_statistics'));
		}
		break;
		case('password'):
		{
			if(isset($_POST['submit']) && ($password = $input->post('keyword')) !== false && !empty($password) && ($chars = $input->post('characters')) % 8 === 0)
			{
				$salt 		= htmlspecialchars(\Tuxxedo\User::getPasswordSalt($chars));
				$hash 		= \Tuxxedo\User::getPasswordHash($password, $salt);
				$password	= ($input->post('hide_password') ? '********' : htmlspecialchars($password));

				eval('$results = "' . $style->fetch('tools_password_result') . '";');
			}

			eval(page('tools_password'));
		}
		break;
		case('requirements'):
		{
			$results 	= '';
			$tests 		= Array(
						'PHP 5.3.0'	=> new Test(Test::OPT_VERSION | Test::OPT_REQUIRED, Array('5.3.0', PHP_VERSION)), 
						'SPL'		=> new Test(Test::OPT_EXTENSION | Test::OPT_REQUIRED, Array('spl')), 
						'json'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('json')), 
						'tokenizer'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('tokenizer')), 
						'mysql'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('mysql')), 
						'mysqli'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('mysqli')), 
						'sqlite3'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('sqlite3')), 
						'pdo'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('pdo')), 
						'pdo_mysql'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('pdo_mysql')), 
						'pdo_sqlite'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, Array('pdo_sqlite')), 
						'realpath()'	=> new Test(Test::OPT_FUNCTION | Test::OPT_REQUIRED, Array('realpath'))
						);

			$failed = false;

			foreach($tests as $component => $test)
			{
				$required = $test->isRequired();

				if(($passed = $test->test()) === false)
				{
					$failed = true;
				}

				eval('$results .= "' . $style->fetch('tools_requirements_itembit') . '";');
			}

			eval(page('tools_requirements'));
		}
		break;
		case('compiler'):
		{

			$source = '';

			if(isset($_POST['submit']) && ($src = $input->post('sourcecode')) !== false && !empty($src))
			{
				$opts		= 0;
				$source 	= htmlspecialchars($src);
				$compiler	= new Compiler;
				$compiler_opts	= Array(
							'opt_function_limit'		=> Compiler::OPT_NO_FUNCTION_CALL_LIMIT, 
							'opt_class_limit'		=> Compiler::OPT_NO_CLASS_CALL_LIMIT, 
							'opt_closure_limit'		=> Compiler::OPT_NO_CLOSURE_CALL_LIMIT, 
							'opt_interpolated_limit'	=> Compiler::OPT_NO_INTERPOLATED_CALLS
							);

				foreach($compiler_opts as $field => $bitfield)
				{
					if(isset($_POST[$field]))
					{
						$opts |= $bitfield;
					}
				}

				foreach(Array('function', 'class', 'closure') as $data)
				{
					if(!isset($_POST['opt_data_' . $data]) || empty($_POST['opt_data_' . $data]))
					{
						continue;
					}

					$raw 				= array_map('trim', explode(',', $_POST['opt_data_' . $data]));
					${'predefined_' . $data} 	= htmlspecialchars(implode(',', $raw), ENT_QUOTES);

					array_map(Array($compiler, 'allow' . $data), $raw);
				}

				if(isset($_POST['verbose_test']))
				{
					$opts |= Compiler::OPT_VERBOSE_TEST;
				}

				$compiler->setOptions($opts);

				try
				{
					$compiler->set($src);
					$compiler->compile();

					$test 	= $compiler->test();
					$result = $compiler->get();
				}
				catch(Exception\TemplateCompiler $e)
				{
					$error = $e->getMessage();
				}
			}

			eval(page('tools_compiler'));
		}
		break;
		case('authentication'):
		{
			if(isset($_POST['progress']) && in_array($input->post('identifier_field'), Array('username', 'email')))
			{
				$registry->register('user', '\Tuxxedo\User');

				$logged_in = $user->login($input->post('identifier'), $input->post('password'), $input->post('identifier_field'));

				if($logged_in)
				{
					$user->logout();
				}
			}

			eval(page('tools_authentication'));
		}
		break;
		case('permissions'):
		{
			$cache_buffer = Array();

			$registry->register('db', '\Tuxxedo\Database');
			$cache->cache(Array('usergroups', 'permissions'), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);
			unset($cache_buffer);

			$permissions = $users = $usergroups = '';

			foreach(Array('permissions', 'users', 'usergroups') as $element)
			{
				switch($element)
				{
					case('users'):
					{
						$allusers = $db->query('
									SELECT 
										`id`, 
										`username`
									FROM
										`' . TUXXEDO_PREFIX . 'users` 
									ORDER BY 
										`id` 
									ASC');

						if(!$allusers->getNumRows())
						{
							tuxxedo_error('Unable to fetch users');
						}

						while($user = $allusers->fetchRow())
						{
							$value 		= $user[0];
							$name		= $user[1];
							$selected	= (isset($_POST['user']) && $input->post('user', Input::TYPE_NUMERIC) == $user[0]);

							eval('$users .= "' . $style->fetch('option') . '";');
						}
					}
					break;
					case('permissions'):
					{
						foreach($cache->permissions as $name => $bits)
						{
							$selected = (isset($_POST['permission_' . $name]) && $_POST['permission_' . $name]);

							eval('$permissions .= "' . $style->fetch('tools_permissions_itembit') . '";');
						}
					}
					break;
					case('usergroups'):
					{
						foreach($cache->usergroups as $value => $group)
						{
							$name 		= $group['title'];
							$selected	= (isset($_POST['usergroup']) && $input->post('usergroup', Input::TYPE_NUMERIC) == $value);

							eval('$usergroups .= "' . $style->fetch('option') . '";');
						}
					}
					break;
				}
			}

			if(isset($_POST['progress']))
			{
				$user = new User;

				if(isset($_POST['user']) && !empty($_POST['user']) && !$user->impersonateAsUser($input->post('user')))
				{
					tuxxedo_error('Unable to impersonate user');
				}
				elseif(!$user->isImpersonatingUser() && isset($_POST['usergroup']) && !$user->impersonateAsUsergroup($input->post('usergroup'), $input->post('user')))
				{
					tuxxedo_error('Unable to impersonate usergroup');
				}

				$test_permissions = '';

				foreach($cache->permissions as $name => $bits)
				{
					if(!isset($_POST['permission_' . $name]))
					{
						continue;
					}

					eval('$test_permissions .= "' . $style->fetch('tools_permissions_itembit') . '";');
				}

				if(empty($test_permissions))
				{
					unset($test_permissions);
				}
			}

			eval(page('tools_permissions'));
		}
		break;
		default:
		{
			eval(page('tools_index'));
		}
		break;
	}
?>