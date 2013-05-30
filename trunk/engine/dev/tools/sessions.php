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
	use DevTools\User;
	use Tuxxedo\Datamanager;


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
		tuxxedo_error('There is currently no active sessions', false);
	}

	function cleanup_cron(&$affected_rows = NULL)
	{
		global $registry;

		$registry->db->query('
					DELETE FROM 
						`' . TUXXEDO_PREFIX . 'sessions` 
					WHERE 
						`lastactivity` + %d < %d', $registry->options->cookie_expires, TIMENOW_UTC);

		if($affected_rows !== NULL)
		{
			$affected_rows = $registry->db->getAffectedRows();
		}
	}

	switch(strtolower($input->get('do')))
	{
		case('rehash'):
		{
			switch(strtolower($input->get('action')))
			{
				case('single'):
				{
					if(($db->equery('
								UPDATE 
									`' . TUXXEDO_PREFIX . 'sessions` 
								SET 
									`rehash` = 1
								WHERE 
									`sessionid` = \'%s\'', $input->get('id'))) !== false && $db->getAffectedRows())
					{
						tuxxedo_redirect('Marked session for rehashing', './sessions.php');
					}

					tuxxedo_error('Invalid session');
				}
				break;
				default:
				{
					cleanup_cron();

					$db->query('
							UPDATE 
								`' . TUXXEDO_PREFIX . 'sessions` 
							SET 
								`rehash` = 1');

					tuxxedo_redirect('Cleaned up all expired sessions and marked active ones for rehashing', './sessions.php');
				}
				break;
			}
		}
		break;
		case('expired'):
		{
			$dm = Datamanager\Adapter::factory('session', $input->get('id'));

			if(!$dm->export())
			{
				tuxxedo_error('Invalid session identifier');
			}

			$dm['lastactivity'] = TIMENOW_UTC - $registry->options->cookie_expires - 1;

			$dm->save();

			tuxxedo_redirect('Session marked as \'expired\'', './sessions.php');
		}
		break;
		case('cron'):
		{
			$affected_rows = 0;

			cleanup_cron($affected_rows);

			tuxxedo_redirect('Executed cronjob, ' . $affected_rows . ' session(s) affected', './sessions.php');
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

			$registry->set('user', new User);

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

			$registry->set('user', new User(false));

			while($session = $sessions->fetchObject())
			{
				if($session->userid)
				{
					$userinfo = $user->getUserInfo($session->userid, 'id', User::OPT_CACHE);
				}

				$session->expires	= (($expires = ($session->lastactivity + $options->cookie_expires)) < TIMENOW_UTC ? 'Expired' : sprintf('%d second(s)', $expires - TIMENOW_UTC));
				$session->lastactivity 	= tuxxedo_date($session->lastactivity);

				eval('$userlist .= "' . $style->fetch('sessions_index_itembit') . '";');
			}

			eval(page('sessions_index'));
		}
		break;
	}
?>