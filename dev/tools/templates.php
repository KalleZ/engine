<?php
	require('./includes/bootstrap.php');

	$cache->cache(Array('options', 'styleinfo'));

	if(!is_array($cache->styleinfo))
	{
		throw new Tuxxedo_Basic_Exception('The datastore is corrupt, rebuild to continue');
	}

	function validate_template()
	{
		if(!isset($_GET['id']) || !is_numeric($_GET['id']))
		{
			throw new Tuxxedo_Basic_Exception('Invalid template id');
		}

		$res = Tuxxedo::get('db')->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'templates` WHERE `id` = %d', $_GET['id']);

		if(!$res || !$res->getNumRows())
		{
			throw new Tuxxedo_Basic_Exception('Invalid template id');
		}

		return($res->fetchObject());
	}

	function compile_template(stdClass $t = NULL, $testonly = false)
	{
		if(!isset($_POST['source']))
		{
			throw new Tuxxedo_Basic_Exception('No source passed');
		}
		elseif(!isset($_POST['title']) || empty($_POST['title']))
		{
			throw new Tuxxedo_Basic_Exception('No title passed');
		}
		elseif(!is_null($t) && isset($_POST['bumprevision']))
		{
			++$t->revision;
		}

		$compiler = new Tuxxedo_Template_Compiler;
		$compiler->set($_POST['source']);

		try
		{
			$compiler->compile();
		}
		catch(Tuxxedo_Template_Compiler_Exception $e)
		{
		}

		if($testonly)
		{
			return($compiler->test());
		}

		if(!$compiler->test())
		{
			throw new Tuxxedo_Template_Compiler_Exception('Template did not parse');
		}

		$db 	= Tuxxedo::get('db');
		$source = $db->escape($_POST['source']);

		if(is_null($t))
		{
			$db->query('INSERT INTO `' . TUXXEDO_PREFIX . 'templates` (`title`, `styleid`, `source`, `compiledsource`, `defaultsource`) VALUES (\'' . $db->escape($_POST['title']) . '\', 1, \'' . $source . '\', \'' . $db->escape($compiler->get()) . '\', \'' . $source . '\')');
		}
		else
		{
			$db->query('UPDATE `' . TUXXEDO_PREFIX . 'templates` SET `title` = \'' . $db->escape($t->title) . '\', `source` = \'' . $source . '\', `defaultsource` = \'' . $source . '\', `compiledsource` = \'' . $db->escape($compiler->get()) . '\', `revision` = ' . $t->revision . ' WHERE `id` = ' . $t->id);
		}

		sync_template((!is_null($t) ? $t->title : $_POST['title']), $compiler->get());
	}

	function e($message)
	{
		throw new Tuxxedo_Basic_Exception('Validation failed: ' . $message);
	}

	function sync_template($title, $compiledsource)
	{
		global $cache;

		$fp = fopen(TUXXEDO_DIR . '/styles/' . $cache->styleinfo[1]['styledir'] . '/templates/' . normalize_title($title) . '.tuxx', 'w+');

		fwrite($fp, $compiledsource);
		fclose($fp);
	}

	function normalize_title($title)
	{
		return(strtolower(str_replace(Array('-', ' '), '_', $title)));
	}

	if(isset($_GET['action']))
	{
		require(TUXXEDO_DIR . '/includes/class_template_compiler.php');

		switch(strtolower($_GET['action']))
		{
			case('edit'):
			{
				$t = validate_template();

				if((isset($_POST['update']) || isset($_POST['updatereturn'])) && !isset($_POST['test']))
				{
					compile_template($t);

					redirect('./templates.php' . (isset($_POST['updatereturn']) ? '?action=edit&id=' . $t->id : ''));
				}
				else
				{
					echo('<h4>Edit template</h4>');
					echo('<p>(styleid=1, template_id=' . $t->id . ', revision=' . $t->revision . ', data_length=' . strlen($t->source) . ', compiled_length=' . strlen($t->compiledsource) . ')</p>');
					echo('<form action="./templates.php?action=edit&id=' . $t->id . '" method="post">');
					echo('<strong>Title:</strong><br />');
					echo('<input type="text" name="title" value="' . htmlize($t->title) . '" /><br />');
					echo('<strong>Revision:</strong><br />');
					echo('<label><input type="checkbox" name="bumprevision" value="true" /> bump revision</label><br />');
					echo('<strong>Source:</strong><br />');
					echo('<textarea name="source" rows="15" style="width: 100%;">' . htmlize((isset($_POST['source']) ? $_POST['source'] : $t->source)) . '</textarea>');
					echo('<input type="submit" name="updatereturn" value="Update" />');
					echo('<input type="submit" name="test" value="Test" />');
					echo('<input type="submit" name="update" value="Done" />');
					echo('</form>');

					if(isset($_POST['test']))
					{
						echo('<p>' . (compile_template($t, true) ? 'Template compiled with success' : 'Template had errors') . '</p>');
					}
				}
			}
			break;
			case('add'):
			{
				if(isset($_POST['save']) || isset($_POST['saveedit']))
				{
					compile_template();

					$_GET['id'] = Tuxxedo::get('db')->getInsertId();
					$t = validate_template();

					redirect('./templates.php' . (isset($_POST['saveedit']) ? '?action=edit&id=' . $t->id : ''));
				}
				else
				{
					echo('<h4>Add template</h4>');
					echo('<p>(styleid=1)</p>');
					echo('<form action="./templates.php?action=add" method="post">');
					echo('<strong>Title:</strong><br />');
					echo('<input type="text" name="title" /><br />');
					echo('<strong>Source:</strong><br />');
					echo('<textarea name="source" rows="15" style="width: 100%;"></textarea>');
					echo('<input type="submit" name="saveedit" value="Add and edit" />');
					echo('<input type="submit" name="save" value="Add" />');
					echo('</form>');
				}
			}
			break;
			case('export'):
			{
				$templates = $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'templates` WHERE `styleid` = 1 ORDER BY `title` ASC');

				if(!$templates || !$templates->getNumRows())
				{
					throw new Tuxxedo_Basic_Exception('No templates to export');
				}

				ob_start();
				header('Content-Type: text/xml');

				echo('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
				echo('<tuxxedo type="style" format="1.0">' . PHP_EOL);
				echo("\t" . '<product>' . PHP_EOL);
				echo("\t\t" . '<parameter name="product-name" value="Tuxxedo" />' . PHP_EOL);
				echo("\t\t" . '<parameter name="product-version" value="' . Tuxxedo::VERSION . '" />' . PHP_EOL);
				echo("\t\t" . '<parameter name="style-title" value="Tuxxedo Default" />' . PHP_EOL);
				echo("\t\t" . '<parameter name="style-developer" value="Tuxxedo Software Development" />' . PHP_EOL);
				echo("\t\t" . '<parameter name="style-directory" value="' . $cache->styleinfo[1]['styledir'] . '" />' . PHP_EOL);
				echo("\t" . '</product>' . PHP_EOL);
				echo("\t" . '<document>' . PHP_EOL);

				while($t = $templates->fetchObject())
				{
					echo("\t\t" . '<object type="template" name="' . $t->title . '">' . PHP_EOL);
					echo("\t\t\t" . '<parameter name="revision" value="' . (integer) $t->revision . '" />' . PHP_EOL);
					echo("\t\t\t" . '<document type="source">' . PHP_EOL);
					echo("\t\t\t\t" . '<![CDATA[' . $t->source . ']]>' . PHP_EOL);
					echo("\t\t\t" . '</document>' . PHP_EOL);
					echo("\t\t\t" . '<document type="compiled">' . PHP_EOL);
					echo("\t\t\t\t" . '<![CDATA[' . $t->compiledsource . ']]>' . PHP_EOL);
					echo("\t\t\t" . '</document>' . PHP_EOL);
					echo("\t\t" . '</object>' . PHP_EOL);
				}

				echo("\t" . '</document>' . PHP_EOL);
				echo('</tuxxedo>');
			}
			break;
			case('delete'):
			{
				$t = validate_template();
				$db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'templates` WHERE `id` = ' . $t->id);

				unlink(TUXXEDO_DIR . '/styles/' . $cache->styleinfo[1]['styledir'] . '/templates/' . normalize_title($t->title) . '.tuxx');

				redirect('./templates.php');
			}
			break;
			case('index'):
			{
				if(!isset($_POST['index']) || !is_numeric($_POST['index']) || $_POST['index'] < 1)
				{
					throw new Tuxxedo_Basic_Exception('Invalid index');
				}

				$db->query('ALTER TABLE `' . TUXXEDO_PREFIX . 'templates` AUTO_INCREMENT = %d', $_POST['index']);

				redirect('./templates.php');
			}
			break;
			case('import'):
			{
				if(!function_exists('simplexml_load_file'))
				{
					throw new Tuxxedo_Basic_Exception('SimpleXML is required to import a style');
				}

				if(isset($_POST['import']) && isset($_POST['location']) && !empty($_POST['location']))
				{
					$xml = @simplexml_load_file($_POST['location']);

					if(!$xml)
					{
						throw new Tuxxedo_Basic_Exception('Unable to load style xml');
					}

					if($xml->getName() != 'tuxxedo' || !isset($xml['type']) || !isset($xml['format']))
					{
						e('Root element is not called \'tuxxedo\', or does not include the required attributes');
					}
					elseif($xml['type'] != 'style')
					{
						e('This is not a style document');
					}
					elseif($xml['format'] <> '1.0')
					{
						e('Only style documents version 1.0 is supported at this time');
					}
					elseif(!isset($xml->product) || !isset($xml->product->parameter) || !sizeof($xml->product->parameter))
					{
						e('No product information found');
					}

					$product_data			= Array();
					$required_product_params 	= array_fill_keys(Array('product-name', 'style-title', 'style-developer', 'style-directory'), 0);

					foreach($xml->product->parameter as $info)
					{
						if(!isset($info['name']) || !isset($info['value']) || empty($info['name']) || empty($info['value']))
						{
							e('A product parameter did not contain the name or value attributes with a value');
						}

						switch(strtolower((string) $info['name']))
						{
							case('product-name'):
							{
								if(strtolower((string) $info['value']) != 'tuxxedo')
								{
									e('This style document is not for Tuxxedo');
								}
							}
							break;
						}

						if(array_key_exists((string) $info['name'], $required_product_params))
						{
							$product_data[(string) $info['name']] = (string) $info['value'];
							unset($required_product_params[(string) $info['name']]);
						}
					}

					if(sizeof($required_product_params))
					{
						e('One or more of the required parameters in the product section was not found');
					}
					elseif(!isset($xml->document) || !isset($xml->document->object) || !sizeof($xml->document->object))
					{
						e('No document section, or no objects to import');
					}

					$requires_recompile = $sources = Array();

					foreach($xml->document->object as $template)
					{
						if(!isset($template['type']) || !isset($template['name']) || empty($template['type']) || empty($template['name']))
						{
							e('An object does not contain the type or name attributes with a value');
						}

						if(!isset($template->document))
						{
							e('No template sources found');
						}

						$found 		= false;
						$recompile 	= true;

						foreach($template->document as $src)
						{
							if(!isset($src['type']))
							{
								break;
							}

							if($src['type'] == 'source')
							{
								$found 					= true;
								$revision				= (integer) (string) $template->parameter->attributes()->value;
								$sources[(string) $template['name']] 	= Array(
														'source'	=> (string) $src, 
														'revision'	=> !$revision ? 1 : $revision
														);
							}
							elseif($src['type'] == 'compiled')
							{
								if($found)
								{
									$sources[(string) $template['name']]['compiled'] = (string) $src;
								}

								$recompile = false;
							}
						}

						if(!$found)
						{
							e('No template source found');
						}

						if($recompile)
						{
							$requires_recompile[] = (string) $template['name'];
						}
					}

					if(sizeof($requires_recompile))
					{
						require_once(TUXXEDO_DIR . '/includes/class_template_compiler.php');

						echo('<p>' . sizeof($requires_recompile) . ' template(s) needs to be re-compiled to function, compiling...</p>');
						echo('<ul>');

						$is_error	= false;
						$compiler 	= new Tuxxedo_Template_Compiler;

						foreach($requires_recompile as $template)
						{
							echo('<li>');
							echo($template . '... ');

							$compiler->set($sources[$template]);

							try
							{
								$compiler->compile();
								$test = $compiler->test();

								if(!$test)
								{
									throw new Tuxxedo_Template_Compiler_Exception('haxxx');
								}

								$sources[$template]['compiled'] = $compiler->get();
							}
							catch(Tuxxedo_Template_Compiler_Exception $e)
							{
								$is_error = true;
							}

							echo(($test ? 'success' : 'failed'));
							echo('</li>');
						}

						echo('</ul>');

						if($is_error)
						{
							echo('<p>One or more templates did not compile, check their syntax and try to re-import</p>');
							echo('<p>For templates that requires custom functions allowed via plugins or source changes, these must be set once imported as it will not work here</p>');
							exit;
						}
					}

					$db->query('INSERT INTO `' . TUXXEDO_PREFIX . 'styles` (`name`, `developer`, `styledir`) VALUES (\'' . $db->escape($product_data['style-title']) . '\', \'' . $db->escape($product_data['style-developer']) . '\', \'' . $db->escape($product_data['style-directory']) . '\')');

					$styleid = $db->getInsertId();

					foreach($sources as $title => $source)
					{
						$db->query('INSERT INTO `' . TUXXEDO_PREFIX . 'templates` (`title`, `source`, `compiledsource`, `defaultsource`, `styleid`, `revision`) VALUES (\'' . $db->escape($title) . '\', \'' . $db->escape($source['source']) . '\', \'' . $db->escape($source['compiled']) . '\', \'' . $db->escape($source['source']) . '\', ' . $styleid . ', ' . $source['revision'] . ')');
						sync_template($title, $source['compiled']);
					}

					echo('<h4>Style imported</h4>');
					echo('<p>Style imported with success, style id=' . $styleid . '</p>');
					echo('<p>The datastore must be rebuilt for this style to appear as selectable</p>');
				}
				else
				{
					echo('<h4>Import style</h4>');
					echo('<form action="./templates.php?action=import" method="post">');
					echo('<strong>Location (URL or local path):</strong><br />');
					echo('<input type="text" name="location" /> ');
					echo('<input type="submit" name="import" value="Import" />');
					echo('</form>');
				}
			}
			break;
			case('storage'):
			{
				echo('<h4>Synchronizing templates</h2>');

				$templates = $db->query('SELECT `title`, `compiledsource` FROM `' . TUXXEDO_PREFIX . 'templates` WHERE `styleid` = 1 ORDER BY `id` ASC');

				if(!$templates || !$templates->getNumRows())
				{
					echo('<p>No templates to synchronize. <a href="./templates.php?action=add">Add a new template?</a></p>');
					die;
				}

				while($template = $templates->fetchObject())
				{
					echo('- ' . $template->title . '<br />');
					sync_template($template->title, $template->compiledsource);
				}

				echo('<p>Done!</p>');
			}
			break;
			default:
			{
				throw new Tuxxedo_Basic_Exception('Invalid action');
			}
		}
	}
	else
	{
		echo('<h4>Styles</h4>');
		echo('<p>');
		echo('<a href="./templates.php?action=export">export style</a>');
		echo(' | ');
		echo('<a href="./templates.php?action=import">import style</a>');
		echo('</p>');

		$templates = $db->query('SELECT `title`, `id`, `revision` FROM `' . TUXXEDO_PREFIX . 'templates` WHERE `styleid` = 1 ORDER BY `id` ASC');

		if(!$templates || !$templates->getNumRows())
		{
			echo('<p>No templates to show. <a href="./templates.php?action=add">Add one?</a></p>');
			die;
		}

		echo('<h4>Templates</h4>');
		echo('<p>');
		echo('<a href="./templates.php?action=add">add new template</a>');
		echo('</p>');
		echo('<table border="1">');
		echo('<tr>');
		echo('<td><strong>#</strong></td>');
		echo('<td><strong>Title</strong></td>');
		echo('<td><strong>Revision</strong></td>');
		echo('<td>&nbsp;</td>');
		echo('<td>&nbsp;</td>');
		echo('</tr>');

		while($template = $templates->fetchObject())
		{
			echo('<tr>');
			echo('<td>' . $template->id . '</td>');
			echo('<td>' . $template->title . '</td>');
			echo('<td>' . $template->revision . '</td>');
			echo('<td><a href="./templates.php?action=edit&id=' . $template->id . '">edit</a></td>');
			echo('<td><a href="./templates.php?action=delete&id=' . $template->id . '" onclick="return(confirm(\'Are you sure?\'));">delete</a></td>');
			echo('</tr>');
		}

		echo('</table>');

		echo('<p>Change next template insert id:</p>');
		echo('<form action="./templates.php?action=index" method="post">');
		echo('<input type="text" name="index" value="' . (integer) $db->query('SHOW TABLE STATUS FROM `' . $configuration['database']['database'] . '` LIKE \'' . TUXXEDO_PREFIX . 'templates\'')->fetchObject()->Auto_increment . '" /> ');
		echo('<input type="submit" value="Change" />');
		echo('</form>');

		echo('<h4>File system storage</h4>');
		echo('<p>Storage engine: <strong>' . $cache->options['style_storage'] . '</strong></p>');
		echo('<form action="./templates.php?action=storage" method="post">');
		echo('<input type="submit" value="Synchronize" />');
		echo('</form>');

	}
?>