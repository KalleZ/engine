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
	use Tuxxedo\Helper;
	use Tuxxedo\User;


	/**
	 * Global templates
	 */
	$templates 		= Array(
					'sessions_index', 
					'sessions_index_itembit', 
					'option'
					);


	/**
	 * Action templates
	 */
	$action_templates	= Array(
					'details'	=> Array(
									'session_details'
									)
					);


	/**
	 * Precache datastore elements
	 */
	$precache 		= Array(
					'usergroups'
					);

	/**
	 * Set script name
	 */
	const SCRIPT_NAME	= 'sessions';

	/**
	 * Require the bootstraper
	 */
	require('./includes/bootstrap.php');


	$sessions = $db->query('
				SELECT 
					* 
				FROM 
					`' . TUXXEDO_PREFIX . 'sessions` 
				ORDER BY 
					`userid` 
				ASC');

	if(!$sessions || !$sessions->getNumRows())
	{
		tuxxedo_error('There is currently no users logged in.');
	}

	switch(strtolower($input->get('do')))
	{
		case('kill'):
		{
			switch(strtolower($input->get('action')))
			{
				case('single'):
				{
					if(($result = $db->equery('
									DELETE FROM 
										`' . TUXXEDO_PREFIX . 'sessions` 
									WHERE 
										`sessionid` = \'%s\'', $input->get('id'))) !== false && $db->getAffectedRows($result))
					{
						tuxxedo_redirect('Killed session with success', './sessions.php');
					}

					tuxxedo_error('Invalid session');
				}
				break;
				default:
				{
					Helper::factory('database')->truncate('sessions');

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
							`lastactivity` + %d < %d', $options->cookie_expires, TIMENOW_UTC);

			tuxxedo_redirect('Executed cronjob, ' . ($result ? $db->getAffectedRows($result) : '0') . ' session(s) affected', './sessions.php');
		}
		break;
		case('details'):
		{
			foreach($sessions as $session)
			{
				if($session['sessionid'] == $input->get('id'))
				{
					$matched = true;

					break;
				}
			}

			if(!isset($matched))
			{
				tuxxedo_error('Invalid session identifier');
			}

			$registry->set('user', new User(false, false));

			$session['expires']		= (($expires = ($session['lastactivity'] + $options->cookie_expires)) < TIMENOW_UTC ? 'Expired' : sprintf('%d second(s)', $expires - TIMENOW_UTC));
			$session['lastactivity'] 	= tuxxedo_date($session['lastactivity']);
			$session['location'] 		= htmlspecialchars(html_entity_decode($session['location']));
			$session['useragent'] 		= htmlspecialchars(html_entity_decode($session['useragent']));

			if($session['userid'])
			{
				$userinfo 	= $user->getUserInfo($session['userid'], 'id', User::OPT_CACHE);
				$usergroup	= $user->getUserGroupInfo($userinfo->usergroupid);
			}

			eval(page('session_details'));
		}
		break;
		default:
		{
			$userlist = '';

			$registry->set('user', new User(false, false));

			while($session = $sessions->fetchObject())
			{
				if($session->userid)
				{
					$userinfo = $user->getUserInfo($session->userid, 'id', User::OPT_CACHE);
				}

				$session->expires	= (($expires = ($session->lastactivity + $options->cookie_expires)) < TIMENOW_UTC ? 'Expired' : sprintf('%d second(s)', $expires - TIMENOW_UTC));
				$session->lastactivity 	= tuxxedo_date($session->lastactivity);
				$session->location	= htmlspecialchars(html_entity_decode($session->location));

				if(($pos = strpos($session->location, '?')) !== false)
				{
					$session->location = substr($session->location, 0, $pos);
				}

				eval('$userlist .= "' . $style->fetch('sessions_index_itembit') . '";');
			}

			eval(page('sessions_index'));
		}
		break;
	}
?>