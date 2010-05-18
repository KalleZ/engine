<?php
	require('./bootstrap.php');

	$index	= Array(
			'languages'	=> 'id', 
			'options'	=> 'option', 
			'phrasegroups'	=> 'id', 
			'styleinfo'	=> 'id', 
			'usergroups'	=> 'id', 
			'timezones'	=> NULL
			);

	if(isset($_POST['truncate']))
	{
		$db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'datastore`');

		echo('<p>Datastore truncated...</p>');
	}
	elseif(isset($_REQUEST['dump']))
	{
		$ds = $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'datastore` ORDER BY `name` ASC');

		if($ds && $ds->getNumRows())
		{
			$dumpnice = (integer) isset($_REQUEST['dumpnice']) && $_REQUEST['dumpnice'];

			echo('<p>Dumping datastore...</p>');
			echo('<table border="1">');
			echo('<tr>');
			echo('<td><strong>Name</strong></td>');
			echo('<td><strong>Size</strong></td>');
			echo('<td><strong>Data</strong></td>');
			echo('</tr>');

			while($r = $ds->fetchObject())
			{
				echo('<tr>');
				echo('<td>' . $r->name . '</td>');
				echo('<td>' . strlen($r->data) . '</td>');

				if($dumpnice)
				{
					echo('<td>');
					echo('<pre>');
					var_dump(@unserialize($r->data));
					echo('</pre>');
					echo('</td>');
				}
				else
				{
					echo('<td>' . $r->data . '</td>');
				}

				echo('</tr>');
			}

			echo('</table>');
			echo('<form action="' . $_SERVER['PHP_SELF'] . '" method="post">');
			echo('<input type="hidden" name="dump" value="true" />');
			echo('<input type="hidden" name="dumpnice" value="' . !$dumpnice . '" />');
			echo('<input type="submit" value="' . ($dumpnice ? 'Show raw data' : 'Extract data') . '" />');
			echo('</form>');
		}
		else
		{
			echo('<p>No elements found in the datastore');
		}
	}
	elseif(isset($_POST['rebuild']))
	{
		echo('<p>Rebuilding datastore...</p>');
		echo('<ul>');

		$tables = Array(
				'languages'	=> 'languages', 
				'options'	=> 'options', 
				'phrasegroups'	=> 'phrasegroups', 
				'styleinfo'	=> 'styles', 
				'usergroups'	=> 'usergroups', 
				'timezones'	=> NULL
				);

		foreach($tables as $element => $tmp)
		{
			$datastore = Array();

			echo('<li>' . $element);

			switch($element)
			{
				case('languages'):
				case('options'):
				case('phrasegroups'):
				case('styleinfo'):
				case('usergroups'):
				{
					$p = $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . $tables[$element] . '` ORDER BY `' . $index[$element] . '` ASC');

					if(!$p || !$p->getNumRows())
					{
						echo(' - FAILED');
					}
					else
					{
						while($s = $p->fetchAssoc())
						{
							switch($element)
							{
								case('options'):
								{
									$datastore[$s['option']] = convert_to_option_type(strtolower($s['type']{0}), $s['value']);
								}
								break;
								case('phrasegroups'):
								{
									$query				= $db->query('SELECT COUNT(`id`) as \'phrases\' FROM `' . TUXXEDO_PREFIX . 'phrases` WHERE `phrasegroup` = \'' . $db->escape($s['title']) . '\'');
									$datastore[$s['title']] 	= Array(
														'id'		=> $s['id'], 
														'phrases'	=> ($query && $query->getNumRows() ? (integer) $query->fetchObject()->phrases : 0)
														);

									unset($query);
								}
								break;
								case('languages'):
								case('styleinfo'):
								case('usergroups'):
								{
									$datastore[$s['id']] = $s;
								}
								break;
							}
						}

						$p->free();
					}
				}
				break;
				case('timezones'):
				{
					$datastore 	= Array();
					$utc		= new DateTime('now');
					$tzlist 	= timezone_identifiers_list();

					foreach($tzlist as $tzname)
					{
						$tz = new DateTimeZone($tzname);

						$datastore[str_replace('_', ' ', $tzname)] = (string) ($tz->getOffset($utc) / 3600);
					}

					asort($datastore);
				}
				break;
			}

			echo(' - ' . (sizeof($datastore) && $cache->rebuild($element, $datastore, false) ? 'SUCCESS' : 'FAILED'));
			echo('</li>');
		}

		echo('</ul>');
	}

	echo('<h4>Rebuild Datastore</h4>');
	echo('<p>Rebuilds the following cache elements:</p>');
	echo('<ul>');

	foreach(array_keys($index) as $element)
	{
		echo('<li>' . $element . '</li>');
	}

	echo('</ul>');
	echo('<form action="' . $_SERVER['PHP_SELF'] . '" method="post">');
	echo('<input type="submit" name="rebuild" value="Rebuild" />');
	echo('<input type="submit" name="truncate" value="Truncate" />');
	echo('<input type="submit" name="dump" value="Dump" />');
	echo('</form>');
?>