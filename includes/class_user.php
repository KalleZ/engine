<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * User session class, this class manages the current user 
	 * session information and permission bitfields.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_User extends Tuxxedo_InfoAccess
	{
		/**
		 * User info constant, also get session information if 
		 * available
		 *
		 * @var		integer
		 */
		const OPT_SESSION	= 1;

		/**
		 * User info constant, cache the user information within 
		 * the class to save a query if trying to query the same 
		 * user again  twice
		 *
		 * @var		integer
		 */
		const OPT_CACHE		= 2;

		/**
		 * User info constant, return a reference to the current 
		 * stored information, no matter if a user is logged on or 
		 * not
		 *
		 * @var		integer
		 */
		const OPT_CURRENT_ONLY	= 4;


		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * User information
		 *
		 * @var		stdClass
		 */
		protected $userinfo;

		/**
		 * Usergroup information
		 *
		 * @var		array
		 */
		protected $usergroupinfo;

		/**
		 * User session
		 *
		 * @var		Tuxxedo_Session
		 */
		protected $session;

		/**
		 * Cached userinfo, for calls to get user information 
		 * about a specific user
		 *
		 * @var		array
		 */
		protected $cache	= Array();


		/**
		 * Constructor, instanciates a new user session.
		 *
		 * @param	boolean				Whether to auto detect if a user is logged in or not
		 * @param	boolean				Whether to start a session or not
		 */
		public function __construct($autodetect = true, $session = true)
		{
			global $tuxxedo;

			$this->tuxxedo = $tuxxedo;

			if($session && $autodetect)
			{
				$this->session	= $tuxxedo->register('session', 'Tuxxedo_Session');

				if(($userid = Tuxxedo_Session::get('userid')) !== false && ($userinfo = $this->getUserInfo($userid, 'id', self::OPT_SESSION)) !== false && $userinfo->password == Tuxxedo_Session::get('password'))
				{
					$this->userinfo		= $userinfo;
					$this->usergroupinfo	= $tuxxedo->cache->usergroups[$userinfo->usergroupid];
				}
			}

			if(!$this->userinfo)
			{
				$this->userinfo		= new stdClass;
				$this->usergroupinfo	= new stdClass;
			}

			$this->userinfo->session	= $this->session;
			$this->information		= $this->userinfo;

			$tuxxedo->db->query('
						REPLACE INTO 
							`' . TUXXEDO_PREFIX . 'sessions` 
						VALUES
						(
							\'%s\', 
							\'%s\',
							\'%s\', 
							%d
						)', Tuxxedo_Session::$id, (isset($this->userinfo->id) ? $this->userinfo->id : ''), $this->tuxxedo->db->escape(TUXXEDO_SELF), time());
		}

		/**
		 * Destructor, executes the cleanup queries etc.
		 */
		public function __destruct()
		{
			$this->cleanup();
		}

		/**
		 * Cleans the user session up by running shutdown queries 
		 * and updates the user location if the user session still
		 * is active
		 *
		 * @return		void			No value is returned
		 */
		protected function cleanup()
		{
		}

		/**
		 * Authenticates a user. If a user is currently logged in, then it 
		 * will be logged out and the session id will be regenerated.
		 *
		 * A user can be logged in by a unique identifier, such as:
		 *  - Username
		 *  - Email
		 *  - etc.
		 *
		 * @param	string			User identifier
		 * @param	string			User's password (raw format)
		 * @param	string			The identifier field to check and validate against
		 * @return	boolean			Returns true if the user was logged in with success, otherwise false
		 */
		public function login($identifier, $password, $identifier_field = 'username')
		{
			if(isset($this->userinfo->id))
			{
				$this->logout(true);
			}

			$userinfo = $this->getUserInfo($identifier, $identifier_field, 0);

			if(!$userinfo || !self::isValidPassword($password, $userinfo->salt, $userinfo->password))
			{
				return(false);
			}

			$this->tuxxedo->db->query('
							REPLACE INTO 
								`' . TUXXEDO_PREFIX . 'sessions` 
							VALUES
							(
								\'%s\', 
								%d,
								\'%s\', 
								%d
							)', Tuxxedo_Session::$id, $userinfo->id , $this->tuxxedo->db->escape(TUXXEDO_SELF), time());

			Tuxxedo_Session::set('userid', $userinfo->id);
			Tuxxedo_Session::set('password', $userinfo->password);

			$this->userinfo		= $userinfo;
			$this->usergroupinfo	= $this->tuxxedo->cache->usergroups[$userinfo->usergroupid];

			$this->tuxxedo->set('userinfo', $userinfo);
			$this->tuxxedo->set('usergroup', $this->usergroupinfo);

			return(true);
		}

		/**
		 * Log the current logged in user out
		 *
		 * @param	boolean			Whether to restart the session or not
		 * @return	void			No value is returned
		 */
		public function logout($restart = false)
		{
			if(!isset($this->userinfo->id))
			{
				return;
			}

			$this->userinfo = $this->usergroupinfo = new stdClass;

			$this->cleanup();
			$this->tuxxedo->db->setShutdownQuery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'sessions` 
								WHERE 
									`sessionid` = \'%s\'', Tuxxedo_Session::$id);

			Tuxxedo_Session::terminate();

			if($restart)
			{
				Tuxxedo_Session::start();
			}
		}

		public function getUserInfo($identifier = NULL, $identifier_field = 'id', $options = 0)
		{
			$identifier_field = strtolower($identifier_field);

			if(isset($this->userinfo->id))
			{
				if(($identifier !== NULL && isset($this->userinfo->{$identifier_field}) && $this->userinfo->{$identifier_field} == $identifier) || $identifier === NULL)
				{
					return($this->userinfo);
				}
			}
			elseif($options & self::OPT_CURRENT_ONLY)
			{
				return($this->userinfo);
			}
			elseif($options & self::OPT_CACHE)
			{
				if($identifier_field == 'id' && isset($this->cache[$identifier]))
				{
					return($this->cache[$identifier]);
				}
				elseif(sizeof($this->cache))
				{
					foreach($this->cache as $userinfo)
					{
						if($userinfo->{$identifier_field} == $identifier)
						{
							return($userinfo);
						}
					}
				}
			}

			if($options & self::OPT_SESSION)
			{
				$query = $this->tuxxedo->db->query('
									SELECT
										' . TUXXEDO_PREFIX . 'sessions.*, 
										' . TUXXEDO_PREFIX . 'users.*
									FROM
										`' . TUXXEDO_PREFIX . 'sessions` 
									LEFT JOIN
										`' . TUXXEDO_PREFIX . 'users` 
										ON 
											' . TUXXEDO_PREFIX . 'sessions.userid = ' . TUXXEDO_PREFIX . 'users.id 
										WHERE 
											' . TUXXEDO_PREFIX . 'users.%s = \'%s\' 
									LIMIT 1', $this->tuxxedo->db->escape($identifier_field), $this->tuxxedo->db->escape($identifier));
			}
			else
			{
				$query = $this->tuxxedo->db->query('
									SELECT 
										* 
									FROM 
										`' . TUXXEDO_PREFIX . 'users` 
									WHERE 
										`%s` = \'%s\'
									LIMIT 1', $this->tuxxedo->db->escape($identifier_field), $this->tuxxedo->db->escape($identifier));
			}

			if($query && $query->getNumRows())
			{
				$userinfo = $query->fetchObject();

				if($options & self::OPT_CACHE)
				{
					$this->cache[$userinfo->id] = $userinfo;
				}

				return($userinfo);
			}

			return(false);
		}

		public function getUserGroupInfo($id = NULL)
		{
			if($id === NULL)
			{
				if(isset($this->usergroupinfo->id))
				{
					return($this->usergroupinfo);
				}

				return(false);
			}
			elseif(isset($this->tuxxedo->cache->usergroups[$id]))
			{
				return($this->tuxxedo->cache->usergroups[$id]);
			}

			return(false);
		}

		/**
		 * Checks whether the user id a member of a 
		 * specific 
		 *
		 * ...
		 */
		public function isMemberOf($groupid)
		{
			return(isset($this->userinfo->id) && $this->userinfo->usergroupid == $groupid);
		}

		/**
		 * Checks whether this session have a user logon or not
		 *
		 * @return	boolean			Returns true if a user is logged on, otherwise false
		 */
		public function isLoggedIn()
		{
			return(isset($this->userinfo->id));
		}

		public function isGranted($permission)
		{
			/**
			 * Checks whether the current logged in user is granted 
			 * a specific permission mask
			 */
		}

		/**
		 * Checks if a password matches with its hash value
		 *
		 * @param	string			The raw password
		 * @param	string			The user salt that generated the password
		 * @param	string			The hashed password
		 * @return	boolean			Returns true if the password matches, otherwise false
		 */
		public static function isValidPassword($password, $salt, $hash)
		{
			return(self::getPasswordHash($password, $salt) === $hash);
		}

		/**
		 * Hashes a password using a salt
		 *
		 * @param	string			The password to encrypt
		 * @param	string			The unique salt for this password
		 * @return	string			Returns the computed password
		 */
		public static function getPasswordHash($password, $salt)
		{
			return(sha1(sha1($password) . $salt));
		}
	}
?>