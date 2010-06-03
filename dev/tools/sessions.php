<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		DevTools
	 *
	 * =============================================================================
	 */


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'sessions_index', 
					'sessions_index_itembit'
					);

	/**
	 * Set script name
	 */
	define('SCRIPT_NAME', 'sessions');

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');

	$tuxxedo->set('user', new Tuxxedo_User(false, false));

	$cache_buffer = Array();
	$cache->cache(Array('options', 'usergroups'), $cache_buffer) or tuxxedo_multi_error('Unable to load datastore element \'%s\', datastore possibly corrupted', $cache_buffer);
	unset($cache_buffer);

	$tuxxedo->set('options', $cache->options);

	$sessions = $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'sessions` ORDER BY `userid` ASC');

	if(!$sessions || !$sessions->getNumRows())
	{
		tuxxedo_gui_error('There is currently no users logged in.');
	}

	switch(strtolower($filter->get('do')))
	{
		case('kill'):
		{
			switch(strtolower($filter->get('action')))
			{
				case('single'):
				{
					if(($result = $db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'sessions` WHERE `sessionid` = \'%s\'', $db->escape($filter->get('id')))) !== false && $db->getAffectedRows($result))
					{
						tuxxedo_redirect('Killed session with success', './sessions.php');
					}

					tuxxedo_gui_message('Invalid session');
				}
				break;
				default:
				{
					$db->query('
							TRUNCATE TABLE 
								`' . TUXXEDO_PREFIX . 'sessions`');

					tuxxedo_redirect('Deleted all active and expired sessions', './sessions.php');
				}
				break;
			}
		}
		break;
		case('cron'):
		{
			$result = $db->query('
						DELETE FROM 
							`' . TUXXEDO_PREFIX . 'sessions` 
						WHERE 
							`lastactivity` + %d < %d', 
						$options['cookie_expires'], TIMENOW_UTC);

			tuxxedo_redirect('Executed cronjob, ' . ($result ? $db->getAffectedRows($result) : '0') . ' session(s) affected', './sessions.php');
		}
		break;
		default:
		{
			$userlist = '';

			while($session = $sessions->fetchObject())
			{
				if($session->userid)
				{
					$userinfo 	= $user->getUserInfo($session->userid, 'id', Tuxxedo_User::OPT_CACHE);
					$usergroup	= $user->getUserGroupInfo($userinfo->usergroupid);
					$usergroup	= $usergroup['title'];
				}

				$session->expires	= ($session->lastactivity + $options['cookie_expires']);
				$session->expires	= ($session->expires < TIMENOW_UTC ? 'Expired' : sprintf('Expires in %d second(s)', $session->expires - TIMENOW_UTC));
				$session->lastactivity 	= tuxxedo_date($session->lastactivity);
				$session->location	= htmlspecialchars(html_entity_decode($session->location));

				eval('$userlist .= "' . $style->fetch('sessions_index_itembit') . '";');
			}

			eval(page('sessions_index'));
		}
		break;
	}
?>