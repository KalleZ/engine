<?php
	require('./includes/bootstrap.php');

	$cache->cache(Array('options'));

	if(!is_array($cache->options))
	{
		throw new Tuxxedo_Basic_Exception('The datastore is corrupt, rebuild to continue');
	}

	function decode_datatype($char)
	{
		switch(strtolower($char{0}))
		{
			case('s'):
			{
				return('string');
			}
			case('i'):
			{
				return('integer');
			}
			case('b'):
			{
				return('boolean');
			}
		}

		return('unknown, possibly corrupt data');
	}

	function dump_datatype($type, $value)
	{
		switch(strtolower($type{0}))
		{
			case('s'):
			{
				return('string(' . strlen($value) . ') "' . htmlize($value) . '"');
			}
			case('i'):
			{
				return('integer(' . (integer) $value . ')');
			}
			case('b'):
			{
				return('boolean(' . ($value ? 'true' : 'false') . ')');
			}
		}

		return('unknown, possibly corrupt data');
	}

	function validate_option()
	{
		if(isset($_GET['name']))
		{
			$_REQUEST['name'] = $_GET['name'];
		}

		if(!isset($_REQUEST['name']) || is_numeric($_REQUEST['name']) || empty($_REQUEST['name']))
		{
			throw new Tuxxedo_Basic_Exception('Invalid option name');
		}

		$db 	= Tuxxedo::get('db');
		$res 	= $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'options` WHERE `option` = \'%s\'', $db->escape($_REQUEST['name']));

		return($res);
	}

	function generate_datatypes_selector($type)
	{
		$type = strtolower($type{0});

		return(
			'<select name="datatype">' . 
			'<option value="s"' . ($type == 's' ? ' selected="selected"' : '') . '>string</option>' . 
			'<option value="i"' . ($type == 'i' ? ' selected="selected"' : '') . '>integer</option>' . 
			'<option value="b"' . ($type == 'b' ? ' selected="selected"' : '') . '>boolean</option>' . 
			'</select>'
			);
	}

	function generate_datatypes_editform($type, $value = '')
	{
		switch(strtolower($type{0}))
		{
			case('b'):
			{
				return(
					'<label><input type="radio" name="value" value="1"' . ($value == 1 ? ' checked="checked"' : '') . ' /> true</label>' . 
					' ' . 
					'<label><input type="radio" name="value" value="0"' . ($value != 1 ? ' checked="checked"' : '') . ' /> false</label>'
					);
			}
			case('i'):
			{
				return('<input type="text" name="value" value="' . (integer) $value . '" />');
			}
		}

		return('<textarea rows="5" cols="40" name="value">' . htmlize($value) . '</textarea>');
	}

	function is_valid_datatype($type)
	{
		switch(strtolower($type{0}))
		{
			case('b'):
			case('s'):
			case('i'):
			{
				return(true);
			}
		}

		return(false);
	}

	$options 	= Array();
	$opts 		= $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'options` ORDER BY `option` ASC');

	if(!$opts->getNumRows())
	{
		throw new Tuxxedo_Basic_Exception('No options to show');
	}

	while($opt = $opts->fetchObject())
	{
		$options[] = $opt;
	}

	if(isset($_GET['action']))
	{
		switch(strtolower($_GET['action']))
		{
			case('edit'):
			{
				$o = validate_option();

				if(!$o || !$o->getNumRows())
				{
					throw new Tuxxedo_Basic_Exception('Invalid option name');
				}

				$o = $o->fetchObject();

				if(isset($_POST['update']))
				{
					if(!isset($_POST['name']) || empty($_POST['name']))
					{
						throw new Tuxxedo_Basic_Exception('Option name cannot be empty');
					}
					elseif(!isset($_POST['datatype']) || !is_valid_datatype($_POST['datatype']))
					{
						throw new Tuxxedo_Basic_Exception('Invalid datatype');
					}

					$db->query('UPDATE `' . TUXXEDO_PREFIX . 'options` SET `option` = \'' . $db->escape($_POST['name']) . '\', `type` = \'' . $_POST['datatype']{0} . '\', `value` = \'' . $db->escape($_POST['value']) . '\' WHERE `option` = \'' . $db->escape($o->option) . '\'');

					redirect('./options.php');
				}
				else
				{
					echo('<h4>Edit option</h4>');
					echo('<form action="./options.php?action=edit&name=' . htmlize($o->option) . '" method="post">');
					echo('<table border="1">');
					echo('<tr>');
					echo('<td nowrap="nowrap">name</td>');
					echo('<td><input type="text" name="name" value="' . htmlize($o->option) . '" /></td>');
					echo('</tr>');
					echo('<tr>');
					echo('<td nowrap="nowrap">datatype</td>');
					echo('<td>' . generate_datatypes_selector($o->type) . '</td>');
					echo('</tr>');
					echo('<tr>');
					echo('<td nowrap="nowrap">value</td>');
					echo('<td>' . generate_datatypes_editform($o->type, $o->value) . '</td>');
					echo('</tr>');
					echo('</table>');
					echo('<input type="submit" name="update" value="Update" />');
					echo('</form>');

					echo('<p>To change the datatype, you must first pick the new datatype, save and then return here to make the input field change</p>');
					echo('<p>Option names should be a-z, A-Z, 0-9 and _ only, numeric only values are not accepted</p>');
				}
			}
			break;
			case('add'):
			{
				if(isset($_POST['save']))
				{
					if(!isset($_POST['datatype']) || !is_valid_datatype($_POST['datatype']))
					{
						throw new Tuxxedo_Basic_Exception('Invalid datatype');
					}

					$o = validate_option();

					if(!$o || !$o->getNumRows())
					{
						throw new Tuxxedo_Basic_Exception('An option with that name already exists');
					}

					$type = strtolower($_POST['datatype']{0});

					$db->query('INSERT INTO `' . TUXXEDO_PREFIX . 'options` VALUES (\'' . $db->escape($_POST['name']) . '\', \'' . $db->escape(convert_to_option_type($type, $_POST['value'])) . '\', \'' . $type . '\')');

					redirect('./options.php');
					exit;
				}
				else
				{
					echo('<h4>Add option</h4>');
					echo('<form action="./options.php?action=add" method="post">');
					echo('<table border="1">');
					echo('<tr>');
					echo('<td nowrap="nowrap">name</td>');
					echo('<td><input type="text" name="name" /></td>');
					echo('</tr>');
					echo('<tr>');
					echo('<td nowrap="nowrap">datatype</td>');
					echo('<td>' . generate_datatypes_selector('void') . '</td>');
					echo('</tr>');
					echo('<tr>');
					echo('<td nowrap="nowrap">value</td>');
					echo('<td>' . generate_datatypes_editform('void') . '</td>');
					echo('</tr>');
					echo('</table>');
					echo('<input type="submit" name="save" value="Add" />');
					echo('</form>');

					echo('<p>Values for different datatypes must use the following rules, if these values are not correct they will be converted and may give unexpected results</p>');
					echo('<ul>');
					echo('<li><strong>string</strong> - any text can be used here</li>');
					echo('<li><strong>integer</strong> - any numeric value, value can be signed or unsigned</li>');
					echo('<li><strong>boolean</strong> - 1 for true, 0 for false</li>');
					echo('</ul>');

					echo('<p>Option names should be a-z, A-Z, 0-9 and _ only, numeric only values are not accepted</p>');
				}
			}
			break;
			case('delete'):
			{
				$o = validate_option();

				if(!$o || !$o->getNumRows())
				{
					throw new Tuxxedo_Basic_Exception('Invalid option name');
				}

				$db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'options` WHERE `option` = \'' . $db->escape($_GET['name']) . '\'');

				redirect('./options.php');			
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
		$reminder = false;

		echo('<h4>Options</h4>');
		echo('<p>Once new options have been added or an option have been edited, the datastore must be rebuilt to reflect the changes</p>');
		echo('<p><a href="./options.php?action=add">add new option</a></p>');

		echo('<table border="1">');
		echo('<tr>');
		echo('<td><strong>Option</strong></td>');
		echo('<td><strong>Datatype</strong></td>');
		echo('<td><strong>Value</strong></td>');
		echo('<td nowrap="nowrap"><strong>Cached in datastore?</strong></td>');
		echo('<td nowrap="nowrap"><strong>Datastore value</strong></td>');
		echo('<td>&nbsp;</td>');
		echo('<td>&nbsp;</td>');
		echo('</tr>');

		foreach($options as $option)
		{
			$cached = array_key_exists($option->option, $cache->options);
			$match = ($cached && $option->value == $cache->options[$option->option]);

			echo('<tr>');
			echo('<td>' . $option->option . '</td>');
			echo('<td>' . decode_datatype($option->type) . '</td>');
			echo('<td>' . (strlen($option->value) ? $option->value : '&nbsp;') . '</td>');
			echo('<td>' . ($cached ? 'yes' : 'no') . '</td>');
			echo('<td>' . ($match ? dump_datatype($option->type, $cache->options[$option->option]) : 'N/A') . '</td>');
			echo('<td><a href="./options.php?action=edit&name=' . $option->option . '">edit</a></td>');
			echo('<td><a href="./options.php?action=delete&name=' . $option->option . '" onclick="return(confirm(\'Are you sure?\'));">delete</a></td>');
			echo('</tr>');

			if(!$reminder && (!$cached || !$match))
			{
				$reminder = true;
			}
		}

		echo('</table>');

		if($reminder)
		{
			echo('<p>Notice, not all elements are cached in the datastore or some may be outdated, you need to rebuild the datastore for these changes to take effect</p>');
		}
	}
?>