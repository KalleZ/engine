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
	 * Global templates
	 */
	$templates 		= Array(
					'datastore_index', 
					'datastore_index_itembit', 
					'datastore_rebuild', 
					'datastore_rebuild_itembit'
					);

	/**
	 * Action templates
	 */
	$action_templates 	= Array(
					'dump'		=> Array(
									'datastore_dump', 
									'datastore_dump_itembit'
									)
					);

	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'datastore';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	$indices = Array(
				'languages'	=> 'id', 
				'options'	=> 'option', 
				'permissions'	=> 'name', 
				'phrasegroups'	=> 'id', 
				'styleinfo'	=> 'id', 
				'usergroups'	=> 'id', 
				'timezones'	=> NULL
				);

	switch(strtolower($input->get('do')))
	{
		case('truncate'):
		{
			$db->helper->truncate('datastore');

			tuxxedo_redirect('Datastore truncated, the datastore is now empty and must be rebuilt before it can be used again', './datastore.php');
		}
		break;
		case('dump'):
		{
			$ds = $db->query('
						SELECT 
							* 
						FROM 
							`' . TUXXEDO_PREFIX . 'datastore` 
						ORDER BY `name` ASC');

			if(!$ds || !$ds->getNumRows())
			{
				tuxxedo_error('No datastore elements to show');
			}

			$rows 		= '';
			$total_size 	= 0;

			while($row = $ds->fetchObject())
			{
				$row->length 	= strlen($row->data);
				$total_size	+= $row->length;

				if(($data = @unserialize($row->data)) !== false)
				{
					ob_start();
					var_dump($data);

					$row->data = ob_get_clean();
				}

				eval('$rows .= "' . $style->fetch('datastore_dump_itembit') . '";');
			}

			eval(page('datastore_dump'));
		}
		break;
		default:
		{
			if($input->post('progress'))
			{
				require(TUXXEDO_LIBRARY . '/DevTools/functions_options.php');

				$result_list 	= '';
				$corrupt_warn	= false;
				$tables 	= Array(
							'languages'	=> 'languages', 
							'options'	=> 'options', 
							'permissions'	=> 'permissions', 
							'phrasegroups'	=> 'phrasegroups', 
							'styleinfo'	=> 'styles', 
							'usergroups'	=> 'usergroups', 
							'timezones'	=> NULL
							);

				foreach(array_keys($tables) as $element)
				{
					$sucess		= false;
					$current 	= Array();

					switch($element)
					{
						case('languages'):
						case('options'):
						case('permissions'):
						case('phrasegroups'):
						case('styleinfo'):
						case('usergroups'):
						{
							$p = $db->equery('
										SELECT 
											* 
										FROM 
											`' . TUXXEDO_PREFIX . $tables[$element] . '` 
										ORDER BY 
											`%s` 
										ASC', $indices[$element]);

							if(!$p || !$p->getNumRows())
							{
								continue;
							}

							while($s = $p->fetchAssoc())
							{
								switch($element)
								{
									case('options'):
									{
										$current[$s['option']] = options_convert_type(strtolower($s['type']{0}), $s['value']);
									}
									break;
									case('phrasegroups'):
									{
										$query			= $db->equery('
															SELECT 
																COUNT(`id`) as \'phrases\' 
															FROM 
																`' . TUXXEDO_PREFIX . 'phrases` 
															WHERE 
																`phrasegroup` = \'%s\'', $s['title']);
										$current[$s['title']] 	= Array(
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
										$current[$s['id']] 	= $s;
										$styleid		= $s['id'];
									}
									break;
									case('permissions'):
									{
										$current[$s['name']] = $s['bits'];
									}
									break;
								}
							}

							$p->free();

							if($element == 'styleinfo' && !empty($styleid))
							{
								$ids 	= Array();
								$p 	= $db->query('
											SELECT 
												`id` 
											FROM 
												`' . TUXXEDO_PREFIX . 'templates` 
											WHERE
												`styleid` = %d 
											ORDER BY 
												`id`
											ASC', $styleid);

								if(!$p || !$p->getNumRows())
								{
									$current[$styleid]['templateids'] = '';

									continue;
								}

								while($t = $p->fetchAssoc())
								{
									$ids[] = $t['id'];
								}

								$current[$styleid]['templateids'] = implode(',', $ids);

								unset($ids);

								$p->free();
							}
						}
						break;
						case('timezones'):
						{
							$utc	= new DateTime('now');
							$tzlist = timezone_identifiers_list();

							foreach($tzlist as $tzname)
							{
								$tz = new DateTimeZone($tzname);

								if(strpos($tzname, '_') !== false)
								{
									$tzname = str_replace('_', ' ', $tzname);
								}

								$current[$tzname] = (string) ($tz->getOffset($utc) / 3600);
							}

							asort($current);
						}
						break;
					}

					if(($success = sizeof($current) && $cache->rebuild($element, $current, false)) === false)
					{
						$corrupt_warn = true;
					}

					eval('$result_list .= "' . $style->fetch('datastore_rebuild_itembit') . '";');
				}

				eval(page('datastore_rebuild'));
			}
			else
			{
				$cache_items = '';

				foreach(array_keys($indices) as $index)
				{
					eval('$cache_items .= "' . $style->fetch('datastore_index_itembit') . '";');
				}

				eval(page('datastore_index'));
			}
		}
		break;
	}
?>