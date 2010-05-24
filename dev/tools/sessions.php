<?php
	require('./bootstrap.php');

	$tuxxedo->set('user', new Tuxxedo_User(false, false));
	$cache->cache(Array('options', 'usergroups'));

	if(!is_array($cache->usergroups))
	{
		throw new Tuxxedo_Basic_Exception('The datastore is corrupt, rebuild to continue');
	}

	if(isset($_GET['kill']))
	{
		if($result = $db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'sessions` WHERE `sessionid` = \'%s\'', $db->escape($_GET['kill'])) && $db->getAffectedRows($result))
		{
			echo('<p>Killed session: ' . htmlspecialchars($_GET['kill']) . '</p>');
		}
	}

	$db->query('DELETE FROM `' . TUXXEDO_PREFIX . 'sessions` WHERE `lastactivity` + %d < %d', $cache->options['cookie_expires'], TIMENOW);

	echo('<h4>Active user sessions</h4>');

	$sessions = $db->query('SELECT * FROM `' . TUXXEDO_PREFIX . 'sessions` ORDER BY `userid` ASC');

	if(!$sessions || !$sessions->getNumRows())
	{
		echo('<p>There is currently no users logged in.</p>');
		die;
	}

	echo('<table border="1">');
	echo('<tr>');
	echo('<td><strong>Session Id</strong></td>');
	echo('<td><strong>User Id</strong></td>');
	echo('<td><strong>Username</strong></td>');
	echo('<td><strong>Usergroup</strong></td>');
	echo('<td><strong>Last activity</strong></td>');
	echo('<td><strong>Expires in</strong></td>');
	echo('<td><strong>Location</strong></td>');
	echo('<td><strong>&nbsp;</strong></td>');
	echo('</tr>');

	while($session = $sessions->fetchObject())
	{
		$userinfo = $user->getUserInfo($session->userid);

		echo('<tr>');
		echo('<td>' . $session->sessionid . '</td>');

		if($userinfo)
		{
			echo('<td>' . $session->userid . '</td>');
			echo('<td>' . $userinfo->username . '</td>');
			echo('<td>' . $cache->usergroups[$userinfo->usergroupid]['title'] . ' (Id: ' . $userinfo->usergroupid . ')</td>');
		}
		else
		{
			echo('<td colspan="3"><em>Session not authenticated</em></td>');
		}

		echo('<td>' . $session->lastactivity . ' (' . tuxxedo_date($session->lastactivity) . ')</td>');
		echo('<td>' . ($cache->options['cookie_expires'] - (time() - $session->lastactivity)) . '</td>');
		echo('<td>' . htmlspecialchars(html_entity_decode($session->location)) . '</td>');
		echo('<td><a href="./sessions.php?kill=' . $session->sessionid . '">kill</a></td>');
		echo('</tr>');
	}

	echo('</table>');
?>