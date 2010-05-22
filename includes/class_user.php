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
		 */
		public function __construct($autodetect = true)
		{
			$this->tuxxedo 			= Tuxxedo::init();
			$this->session			= $this->tuxxedo->register('session', 'Tuxxedo_Session');

			if($autodetect && $userid = Tuxxedo_Session::get('userid') && ($userinfo = $this->getUserInfo($userid)) !== false)
			{
				$this->userinfo		= $userinfo;
				$this->usergroupinfo	= $this->tuxxedo->cache->usergroups[$userinfo->usergroupid];
			}
			else
			{
				$this->userinfo		= new stdClass;
				$this->usergroupinfo	= new stdClass;
			}

			$this->userinfo->session 	= $this->session;
			$this->information		= $this->userinfo;

			$this->tuxxedo->db->query('
							REPLACE INTO 
								`' . TUXXEDO_PREFIX . 'sessions` 
							VALUES
							(
								\'%s\', 
								%s,
								\'%s\', 
								%d
							)', Tuxxedo_Session::$id, (isset($this->userinfo->id) ? $this->userinfo->id : '\'\''), $this->tuxxedo->db->escape(TUXXEDO_SELF), time());
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

		public function login($identifier, $password, $identifier_field = 'username')
		{
			if(isset($this->userinfo->id))
			{
				$this->logout();
			}

			$userinfo = $this->getUserInfo($identifer, $identifier_field);

			if(!$userinfo || !self::isValidPassword($password, $userinfo->salt, $userinfo->password))
			{
				return(false);
			}

			$this->userinfo		= $userinfo;
			$this->usergroupinfo	= $this->tuxxedo->cache->usergroups[$userinfo->usergroupid];

			return(true);
		}

		public function logout($restart = false)
		{
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

		public function getUserInfo($identifier = NULL, $identifier_field = 'id', $cache = false)
		{
			$identifier_field = strtolower($identifier_field);

			if(isset($this->userinfo->id) && $this->userinfo->{$identifier_field} == $identifier)
			{
				return($this->userinfo);
			}
			elseif($cache)
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

			$query = $this->tuxxedo->db->query('
								SELECT 
									* 
								FROM 
									`' . TUXXEDO_PREFIX . 'users` 
								WHERE 
									`%s` = \'%s\'
								LIMIT 1', $this->tuxxedo->db->escape($identifier_field), $this->tuxxedo->db->escape($identifier));

			if($query && $query->getNumRows())
			{
				$userinfo = $query->fetchObject();

				if($cache)
				{
					$this->cache[$userinfo->id] = $userinfo;
				}

				return($this->userinfo);
			}

			return(false);
		}

		public function getUserGroupInfo($id = NULL)
		{
			if($id === NULL)
			{
				return($this->usergroupinfo);
			}

			return($this->tuxxedo->cache->usergroups[$id]);
		}

		public function isMemberOf($groupid)
		{
			return(isset($this->userinfo->id) && $this->userinfo->usergroupid == $groupid);
		}

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