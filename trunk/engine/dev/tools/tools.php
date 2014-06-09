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
	use DevTools\Utilities;
	use Tuxxedo\Exception;
	use Tuxxedo\Helper;
	use Tuxxedo\Input;
	use Tuxxedo\Template\Compiler;

	/**
	 * Global templates
	 */
	$templates 		= [
					'tools_index'
					];

	/**
	 * Action templates
	 */
	$action_templates	= [
					'statistics'		=> [
									'tools_statistics', 
									'tools_statistics_itembit'
									], 
					'password'		=> [
									'tools_password'
									], 
					'requirements'		=> [
									'tools_requirements', 
									'tools_requirements_itembit'
									], 
					'compiler'		=> [
									'tools_compiler'
									], 
					'authentication'	=> [
									'tools_authentication'
									], 
					'status'		=> [
									'tools_status', 
									'tools_status_itembit', 
									'tools_status_all', 
									'tools_status_all_itembit'
									], 
					'configuration'		=> [
									'tools_configuration', 
									'tools_configuration_section', 
									'tools_configuration_section_itembit'
									]
					];

	/**
	 * Precache datastore elements
	 */
	$precache 		= [
					'permissions', 
					'usergroups'
					];

	/**
	 * Set script name
	 *
	 * @var		string
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
			$files = recursive_glob('../..', false);

			if(!$files)
			{
				throw new Exception('No source files found in the root directory');
			}

			$ignored	= [
						'json', 
						'png', 
						'sqlite3', 
						'txt', 
						'tuxx'
						];

			$statistics 	= [
						'lines'		=> [], 
						'size'		=> [], 
						'files'		=> [], 
						'total'		=> [
									'files'		=> 0, 
									'lines'		=> 0, 
									'size'		=> 0, 
									'blanks'	=> 0
									], 
						'extensions'	=> [
									'php'		=> [
												'tokens'	=> 0
												]
									]
						];

			foreach($files as $path)
			{
				$path		= $path;
				$extension 	= pathinfo($path, PATHINFO_EXTENSION);
				$tokenizer	= extension_loaded('tokenizer');

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
					$statistics['extensions'][$extension] 	= [
											'blanks'	=> 0
											];
				}
				elseif(!isset($statistics['extensions'][$extension]['blanks']))
				{
					$statistics['extensions'][$extension]['blanks'] = 0;
				}

				if($tokenizer && stripos($extension, 'php') !== false)
				{
					$statistics['extensions'][$extension]['tokens'] += sizeof(token_get_all(file_get_contents($path)));
				}

				foreach($l = file($path) as $line)
				{
					$line = trim($line);

					if(empty($line))
					{
						++$statistics['extensions'][$extension]['blanks'];
						++$statistics['total']['blanks'];
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

			$statistics['total']['extensions']	= sizeof(array_keys($statistics['files']));
			$ignored				= '.' . implode(', .', $ignored);

			$avg					= [
									'lines_per_file'	=> round($statistics['total']['lines'] / $statistics['total']['files']), 
									'bytes_per_line'	=> round($statistics['total']['size'] / $statistics['total']['lines']), 
									'blanks_per_file'	=> round($statistics['total']['blanks'] / $statistics['total']['files']), 
									'tokens_per_line'	=> round($statistics['extensions']['php']['tokens'] / $statistics['lines']['php']), 
									'tokens_per_file'	=> round($statistics['extensions']['php']['tokens'] / $statistics['files']['php'])
									];

			eval(page('tools_statistics'));
		}
		break;
		case('password'):
		{
			$valid = false;

			if(isset($_POST['submit']) && ($password = $input->post('keyword')) !== false && !empty($password) && ($chars = $input->post('characters')) % 8 === 0)
			{
				$valid		= true;
				$salt 		= htmlspecialchars(\Tuxxedo\User::getPasswordSalt($chars));
				$hash 		= \Tuxxedo\User::getPasswordHash($password, $salt);
				$password	= htmlspecialchars($password);
			}

			eval(page('tools_password'));
		}
		break;
		case('requirements'):
		{
			$results 	= '';
			$tests 		= [
						'PHP 5.4.0'	=> new Test(Test::OPT_VERSION | Test::OPT_REQUIRED, ['5.4.0', PHP_VERSION]), 
						'SPL'		=> new Test(Test::OPT_EXTENSION | Test::OPT_REQUIRED, ['spl']), 
						'filter'	=> new Test(Test::OPT_EXTENSION | Test::OPT_REQUIRED, ['filter']), 
						'json'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['json']), 
						'OpenSSL'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['openssl']), 
						'fileinfo'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['fileinfo']), 
						'tokenizer'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['tokenizer']), 
						'mysql'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['mysql']), 
						'mysqli'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['mysqli']), 
						'sqlite3'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['sqlite3']), 
						'pdo'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['pdo']), 
						'pdo_mysql'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['pdo_mysql']), 
						'pdo_sqlite'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['pdo_sqlite']), 
						'xml'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['xml']), 
						'dom'		=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['dom']), 
						'xmlreader'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['xmlreader']), 
						'simplexml'	=> new Test(Test::OPT_EXTENSION | Test::OPT_OPTIONAL, ['simplexml']), 
						'glob()'	=> new Test(Test::OPT_FUNCTION | Test::OPT_REQUIRED, ['glob']), 
						'realpath()'	=> new Test(Test::OPT_FUNCTION | Test::OPT_REQUIRED, ['realpath'])
						];

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
				$compiler_opts	= [
							'opt_function_limit'		=> Compiler::OPT_NO_FUNCTION_CALL_LIMIT, 
							'opt_class_limit'		=> Compiler::OPT_NO_CLASS_CALL_LIMIT, 
							'opt_closure_limit'		=> Compiler::OPT_NO_CLOSURE_CALL_LIMIT, 
							'opt_interpolated_limit'	=> Compiler::OPT_NO_INTERPOLATED_CALLS
							];

				foreach($compiler_opts as $field => $bitfield)
				{
					if(isset($_POST[$field]))
					{
						$opts |= $bitfield;
					}
				}

				foreach(['function', 'class', 'closure'] as $data)
				{
					if(!isset($_POST['opt_data_' . $data]) || empty($_POST['opt_data_' . $data]))
					{
						continue;
					}

					$raw 				= array_map('trim', explode(',', $_POST['opt_data_' . $data]));
					${'predefined_' . $data} 	= htmlspecialchars(implode(',', $raw), ENT_QUOTES);

					array_map([$compiler, 'allow' . $data], $raw);
				}

				if(!isset($_POST['verbose_test']))
				{
					$opts &= ~Compiler::OPT_VERBOSE_TEST;
				}

				if(isset($_POST['parse_tags_if']))
				{
					$opts |= Compiler::OPT_PARSE_IF_TAGS;
				}

				$compiler->setOptions($opts);

				try
				{
					$compiler->setSource($src);
					$compiler->compile();

					$test 	= $compiler->test();
					$result = $compiler->getCompiledSource();
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
			if(isset($_POST['progress']) && in_array($input->post('identifier_field'), ['id', 'username', 'email']))
			{
				$logged_in = test_login($input->post('identifier'), $input->post('password'), $input->post('identifier_field'));
			}

			eval(page('tools_authentication'));
		}
		break;
		case('status'):
		{
			$dbhelper = Helper::factory('database');
			$dbdriver = $dbhelper->getDriver();

			if($dbdriver == 'sqlite' || $dbdriver == 'pdo_sqlite')
			{
				throw new Exception('This tool is not available for SQLite');
			}

			$query = $dbhelper->getTables();

			if(isset($_GET['table']) && isset($_GET['operation']) && in_array(strtolower($input->get('operation')), ['optimize', 'repair']))
			{
				$table = strtolower($input->get('table'));

				foreach($query as $row)
				{
					if($table == strtolower($row['Name']))
					{
						$match = true;

						break;
					}
				}

				if(!isset($match))
				{
					throw new Exception('Invalid table');
				}

				switch(strtolower($input->get('operation')))
				{
					case('optimize'):
					{
						$result = $dbhelper->tableOptimize($row['Name']);
					}
					break;
					case('repair'):
					{
						$result = $dbhelper->tableRepair($row['Name']);
					}
					break;
					default:
					{
						throw new Exception('Invalid operation');
					}
				}

				Utilities::redirect('Running operation \'' . strtolower($input->get('operation')) . '\' on table \'' . $row['Name'] . '\': ' . $result, './tools.php?do=status');
			}
			elseif(isset($_POST['submit']))
			{
				$optimize 	= isset($_POST['optimize']);
				$repair		= isset($_POST['repair']);

				if(!$optimize && !$repair)
				{
					throw new Exception('No operation was selected');
				}

				$tablelist = '';

				foreach($query as $row)
				{
					if($optimize)
					{
						$operation	= 'optimize';
						$result 	= $dbhelper->tableOptimize($row['Name']);

						eval('$tablelist .= "' . $style->fetch('tools_status_all_itembit') . '";');
					}

					if($repair)
					{
						$operation	= 'repair';
						$result		= $dbhelper->tableRepair($row['Name']);

						eval('$tablelist .= "' . $style->fetch('tools_status_all_itembit') . '";');
					}
				}

				eval(page('tools_status_all'));
			}
			else
			{
				$tablelist = '';

				foreach($query as $row)
				{
					eval('$tablelist .= "' . $style->fetch('tools_status_itembit') . '";');
				}

				eval(page('tools_status'));
			}
		}
		break;
		case('configuration'):
		{
			if(!$configuration)
			{
				throw new Exception('No sections found in the configuration file');
			}

			$sections = '';
			$dbdriver = Helper::factory('database')->getDriver();

			foreach($configuration as $section => $directives)
			{
				$rows = '';

				if($directives)
				{
					foreach($directives as $name => $value)
					{
						if(($name == 'password' || $name == 'database' && ($dbdriver == 'sqlite' || $dbdriver == 'pdo_sqlite' || $section == 'devtools')) && !$configuration['devtools']['protective'])
						{
							$value = '"********"';
						}
						elseif(is_bool($value))
						{
							$value = ($value ? 'true' : 'false');
						}
						elseif($value == '0' || !empty($value))
						{
							$value = '"' . $value . '"';
						}

						eval('$rows .= "' . $style->fetch('tools_configuration_section_itembit') . '";');
					}
				}

				eval('$sections .= "' . $style->fetch('tools_configuration_section') . '";');
			}

			eval(page('tools_configuration'));
		}
		break;
		default:
		{
			eval(page('tools_index'));
		}
		break;
	}
?>