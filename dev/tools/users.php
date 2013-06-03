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
	 * Alasing rules
	 */
	use DevTools\User;
	use Tuxxedo\Datamanager;
	use Tuxxedo\Input;


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'option', 
					'users_index'
					);

	$action_templates	= Array(
					'user'		=> Array(
									'users_add_edit_form', 
									'users_delete', 
									'users_edit_permissions', 
									'users_edit_permissions_itembit', 
									'users_search', 
									'users_search_itembit'
									), 

					'usergroup'	=> Array(
									'users_usergroup_add_edit_form', 
									'users_usergroup_list', 
									'users_usergroup_list_itembit', 
									'users_usergroup_permission', 
									'users_usergroup_permission_itembit'
									),

					'permission'	=> Array(
									'users_permission_add_edit_form', 
									'users_permission_delete', 
									'users_permission_delete_itembit', 
									'users_permission_list', 
									'users_permission_list_itembit', 
									'users_permission_tree_index', 
									'users_permission_tree_view', 
									'users_permission_tree_view_itembit'
									)
					);


	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'languages', 
					'permissions', 
					'styleinfo', 
					'timezones', 
					'usergroups'
					);


	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'users';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	$user = new User;

	switch($do = strtolower($input->get('do')))
	{
		case('user'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('user', $input->get('user'));
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm = Datamanager\Adapter::factory('user');
						}

						$dm['username'] 	= $input->post('username');
						$dm['email']		= $input->post('eaddress');
						$dm['name']		= $input->post('name');
						$dm['usergroupid']	= $input->post('usergroupid', Input::TYPE_NUMERIC);

						if($action == 'add' || $action == 'edit' && !empty($_POST['newpassword']))
						{
							$dm['password']	= $input->post('newpassword');
						}

						$dm['language_id']	= (isset($_POST['languageid']) && is_numeric($_POST['languageid']) ? $input->post('languageid', Input::TYPE_NUMERIC) : NULL);
						$dm['style_id']		= (isset($_POST['styleid']) && is_numeric($_POST['styleid']) ? $input->post('styleid', Input::TYPE_NUMERIC) : NULL);
						$dm['timezone'] 	= $input->post('timezone');

						$dm->save();

						tuxxedo_redirect(($action == 'edit' ? 'Edited user' : 'Added user'), './users.php?do=user&action=search');
					}

					$usergroups_dropdown = '';

					if($datastore->usergroups)
					{
						foreach($datastore->usergroups as $value => $group)
						{
							$name 		= $group['title'];
							$selected	= (isset($dm) && $dm['usergroupid'] == $value);

							eval('$usergroups_dropdown .= "' . $style->fetch('option') . '";');
						}
					}

					foreach(Array('styles' => Array('styleinfo', 'name', 'style_id'), 'languages' => Array('languages', 'title', 'language_id')) as $item => $info)
					{
						${$item . '_dropdown'} = '';

						if(!$datastore->{$info[0]})
						{
							continue;
						}

						foreach($datastore->{$info[0]} as $value => $data)
						{
							$name		= $data[$info[1]];
							$selected	= (isset($dm) && $dm[$info[2]] == $value);

							eval('${$item . \'_dropdown\'} .= "' . $style->fetch('option') . '";');
						}
					}

					$timezones_dropdown = '';

					if($datastore->timezones)
					{
						foreach($datastore->timezones as $tzname => $value)
						{
							$selected	= (isset($dm) && $dm['timezone'] == $tzname || !isset($dm) && $tzname == 'UTC');
							$name 		= sprintf('%s (%s)', $tzname, ($value >= 0 ? '+' . $value : $value));
							$value		= $tzname;

							eval('$timezones_dropdown .= "' . $style->fetch('option') . '";');
						}
					}

					eval(page('users_add_edit_form'));
				}
				break;
				case('permissions'):
				{
					if(!$datastore->permissions)
					{
						tuxxedo_error('No permissions currently exists');
					}

					$dm 	= Datamanager\Adapter::factory('user', $input->get('user'));
					$udm	= Datamanager\Adapter::factory('usergroup', $dm['usergroupid']);

					if(isset($_POST['submit']) && isset($_POST['permission']) && $_POST['permission'])
					{
						$bitfield = 0;

						foreach($_POST['permission'] as $permission => $status)
						{
							if(!$status || !isset($datastore->permissions[$permission]))
							{
								continue;
							}

							$bitfield |= (integer) $datastore->permissions[$permission];
						}

						$dm['permissions'] = $bitfield;

						$dm->save();

						tuxxedo_redirect('Updated permissions', './users.php?do=user&action=search');
					}

					$rows = '';

					foreach($datastore->permissions as $name => $bits)
					{
						$bits			= (integer) $bits;
						$permitted 		= ($dm['permissions'] & $bits);
						$group_permitted	= ($udm['permissions'] & $bits);

						eval('$rows .= "' . $style->fetch('users_edit_permissions_itembit') . '";');
					}

					eval(page('users_edit_permissions'));
				}
				break;
				case('delete'):
				{
					$dm = Datamanager\Adapter::factory('user', $input->get('user'));

					if(isset($_POST['submit']))
					{
						$dm->delete();

						tuxxedo_redirect('Deleted user', './users.php?do=user&action=search');
					}

					eval(page('users_delete'));
				}
				break;
				case('search'):
				{
					$fields = array_filter(Datamanager\Adapter::factory('user')->getFields(), function($field)
					{
						static $hidden_fields;

						if(!$hidden_fields)
						{
							$hidden_fields = Array(
										'id', 
										'password', 
										'salt'
										);
						}

						return(!in_array($field, $hidden_fields));
					});

					$fields_dropdown = '';

					if(isset($_POST['submit']) && isset($_POST['query']) && $_POST['query'] && isset($_POST['query_field']) && in_array((string) $_POST['query_field'], $fields))
					{
						$safe_query	= htmlspecialchars($input->post('query'), ENT_QUOTES);
						$query 		= str_replace(Array('*', '%'), Array('%', '\%'), $input->post('query'));
						$stripped_query	= str_replace('*', '', $query);

						if($query{0} != '*')
						{
							$query = '%' . $query;
						}

						if(($length = strlen($query)) !== false && $query{$length - 1} != '*')
						{
							$query .= '%';
						}

						$query = $db->equery('
									SELECT 
										`id`, 
										`username`, 
										`usergroupid`, 
										`permissions`
									FROM 
										`' . TUXXEDO_PREFIX . 'users` 
									WHERE 
										`%s` LIKE \'%s\'', $input->post('query_field'), $query);


						if(!$query || !$query->getNumRows())
						{
							tuxxedo_error('Search return zero results');
						}

						$table 		= '';
						$matches	= $query->getNumRows();

						while($user = $query->fetchArray())
						{
							$usergroup = $datastore->usergroups[$user['usergroupid']]['title'];

							eval('$table .= "' . $style->fetch('users_search_itembit') . '";');
						}
					}

					foreach($fields as $value)
					{
						$name 		= ucfirst(str_replace('_', ' ', $value));
						$selected	= (isset($safe_query) && $value == (string) $_POST['query_field']);

						eval('$fields_dropdown .= "' . $style->fetch('option') . '";');
					}

					eval(page('users_search'));
				}
				break;
				default:
				{
					tuxxedo_error('Invalid action');
				}
				break;
			}
		}
		break;
		case('usergroup'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('usergroup', $input->get('usergroup'));
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm = Datamanager\Adapter::factory('usergroup');
						}

						$dm['title'] = $input->post('title');

						$dm->save();

						tuxxedo_redirect(($action == 'edit' ? 'Edited usergroup' : 'Added usergroup'), './users.php?do=usergroup&action=list');
					}

					eval(page('users_usergroup_add_edit_form'));
				}
				break;
				case('delete'):
				{
					Datamanager\Adapter::factory('usergroup', $input->get('usergroup'))->delete();

					tuxxedo_redirect('Deleted usergroup', './users.php?do=usergroup&action=list');
				}
				break;
				case('permissions'):
				{
					if(!$datastore->permissions)
					{
						tuxxedo_error('No permissions currently exists');
					}

					$dm = Datamanager\Adapter::factory('usergroup', $input->get('usergroup'));

					if(isset($_POST['submit']) && isset($_POST['permission']) && $_POST['permission'])
					{
						$bitfield = 0;

						foreach($_POST['permission'] as $permission => $status)
						{
							if(!$status || !isset($datastore->permissions[$permission]))
							{
								continue;
							}

							$bitfield |= $datastore->permissions[$permission];
						}

						$dm['permissions'] = $bitfield;

						$dm->save();

						tuxxedo_redirect('Updated permissions', './users.php?do=usergroup&action=list');
					}

					$rows = '';

					foreach($datastore->permissions as $name => $bits)
					{
						$permitted = ($dm['permissions'] & $bits);

						eval('$rows .= "' . $style->fetch('users_usergroup_permission_itembit') . '";');
					}

					eval(page('users_usergroup_permission'));
				}
				break;
				case('list'):
				{
					if(!$datastore->usergroups)
					{
						tuxxedo_error('No usergroups currently exists');
					}

					$rows = '';

					foreach($datastore->usergroups as $id => $group)
					{
						eval('$rows .= "' . $style->fetch('users_usergroup_list_itembit') . '";');
					}

					eval(page('users_usergroup_list'));
				}
				break;
				default:
				{
					tuxxedo_error('Invalid action');
				}
				break;
			}
		}
		break;
		case('permission'):
		{
			switch($action = strtolower($input->get('action')))
			{
				case('edit'):
				{
					$dm = Datamanager\Adapter::factory('permission', $input->get('permission'));
				}
				case('add'):
				{
					if(isset($_POST['submit']))
					{
						if(!isset($dm))
						{
							$dm = Datamanager\Adapter::factory('permission');
						}

						$dm['name'] = $input->post('name');
						$dm['bits'] = $input->post('bits');

						$dm->save();

						tuxxedo_redirect(($action == 'edit' ? 'Edited permission' : 'Added permission'), './users.php?do=permission&action=list');
					}

					eval(page('users_permission_add_edit_form'));
				}
				break;
				case('delete'):
				{
					if(!$datastore->permissions)
					{
						tuxxedo_error('No permissions currently exists');
					}

					$dm = Datamanager\Adapter::factory('permission', $input->get('permission'));

					if(isset($_POST['submit']))
					{
						if(isset($_POST['deleteperm']))
						{
							foreach(Array('user', 'usergroup') as $type)
							{
								if(!isset($_POST['deleteperm'][$type]) || !$_POST['deleteperm'][$type])
								{
									continue;
								}

								foreach($_POST['deleteperm'][$type] as $id)
								{
									$udm = Datamanager\Adapter::factory($type, $id);

									if(($udm['permissions'] & $dm['bits']) > 0)
									{
										$udm['permissions'] &= ~$dm['bits'];

										$udm->save();
									}
								}
							}
						}

						$dm->delete();

						tuxxedo_redirect('Deleted permission', './users.php?do=permission&action=list');
					}

					$users = $usergroups = '';
					$query = $db->query('
								SELECT 
									`id`, 
									`username`
								FROM 
									`' . TUXXEDO_PREFIX . 'users` 
								WHERE 
									`permissions` & %d
								ORDER BY 
									`id` 
								DESC', $dm['bits']);

					if($query && $query->getNumRows())
					{
						$prefix = 'user';

						foreach($query as $item)
						{
							eval('$users .= "' . $style->fetch('users_permission_delete_itembit') . '";');
						}
					}

					if($datastore->usergroups)
					{
						$prefix = 'usergroup';

						foreach($datastore->usergroups as $item)
						{
							if($item['permissions'] & $dm['bits'])
							{
								eval('$usergroups .= "' . $style->fetch('users_permission_delete_itembit') . '";');
							}
						}
					}

					$const = 'PERMISSION_' . strtoupper($dm['name']);

					eval(page('users_permission_delete'));
				}
				break;
				case('list'):
				{
					if(!$datastore->permissions)
					{
						tuxxedo_error('No permissions currently exists');
					}

					$rows = '';

					foreach($datastore->permissions as $name => $bits)
					{
						eval('$rows .= "' . $style->fetch('users_permission_list_itembit') . '";');
					}

					eval(page('users_permission_list'));
				}
				break;
				case('tree'):
				{
					if(!$datastore->permissions)
					{
						tuxxedo_error('No permissions currently exists');
					}

					if(isset($_POST['submituser']) || isset($_POST['submitusergroup']))
					{
						if(isset($_POST['submituser']))
						{
							$userinfo = $user->getUserInfo($input->post('useridentifier'), in_array(strtolower($input->post('identifiertype')), Array('id', 'username', 'email')) ? $input->post('identifiertype') : 'username');

							if(!$userinfo)
							{
								tuxxedo_error('Invalid user');
							}

							$groupinfo = $user->getUserGroupInfo($userinfo->usergroupid);
						}
						else
						{
							$groupinfo = $user->getUserGroupInfo($input->post('usergroupidentifier'));
						}

						if(!$groupinfo)
						{
							tuxxedo_error('Invalid usergroup');
						}

						$rows = '';

						foreach($datastore->permissions as $permission => $bits)
						{
							if(isset($_POST['opt_showconst']))
							{
								$permission = 'PERMISSION_' . strtoupper($permission);
							}

							$uaccessible	= ($groupinfo->permissions & $bits);
							$group_perm 	= ($uaccessible) ? 'Permitted' : 'Restricted';

							if(isset($userinfo))
							{
								$user_perm = ($userinfo->permissions ? ($userinfo->permissions & $bits ? 'Permitted' : 'Restrictired') : 'Inherited');
							}

							$accessible = !(isset($user_perm) && ($user_perm == 'Restricted' || $user_perm == 'Inherited') && $group_perm == 'Restricted' || !isset($user_perm) && $group_perm == 'Restricted');

							eval('$rows .="' . $style->fetch('users_permission_tree_view_itembit') . '";');

							unset($user_perm);
						}

						eval(page('users_permission_tree_view'));
					}
					else
					{
						$usergroups = '';

						if($datastore->usergroups)
						{
							foreach($datastore->usergroups as $group)
							{
								$value 	= $group['id'];
								$name	= $group['title'];

								eval('$usergroups .= "' . $style->fetch('option') . '";');
							}
						}

						eval(page('users_permission_tree_index'));
					}
				}
				break;
				default:
				{
					tuxxedo_error('Invalid action');
				}
				break;
			}
		}
		break;
		default:
		{
			eval(page('users_index'));
		}
		break;
	}
?>