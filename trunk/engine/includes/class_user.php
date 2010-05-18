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
	class Tuxxedo_UserSession
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
		 * The session options, such as prefix, path etc.
		 *
		 * @var		array
		 */
		protected static $options	= Array(
							'expires'	=> 1800, 
							'prefix'	=> '', 
							'domain'	=> '', 
							'path'		=> ''
							);

		/**
		 * Constructor, instanciates a new user session. It detects 
		 * the session data automaticlly so it can be created instantly
		 *
		 * @param	string			Email address of the user, if creating a new user session only
		 * @param	string			Raw password associated with the above email, if creating a new user session only
		 *
		 * @throws	Tuxxedo_Exception	Throws a regular exception with no error message if the email and password supplied was invalid
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if the usergroup information fails to load from the datastore or if one of the database queries should fail
		 */
		public function __construct($email = NULL, $password = NULL)
		{
			$this->tuxxedo = Tuxxedo::init();

			if(!is_null($email) && !is_null($password))
			{
				if(!$email || empty($password))
				{
					throw new Tuxxedo_Exception('Invalid email or password');
				}

				$userinfo = fetch_userinfo($email, true);

				if(!$userinfo || !$email || !is_valid_password($password, $userinfo->salt, $userinfo->password))
				{
					throw new Tuxxedo_Exception('Invalid email or password');
				}

				self::set('userid', $userinfo->id);
			}

			if($userid = self::get('userid'))
			{
				if(!isset($userinfo))
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
											' . TUXXEDO_PREFIX . 'sessions.sessionid = \'%s\'
											AND 
											' . TUXXEDO_PREFIX . 'users.id = %d
										LIMIT 1', session_id(), $userid);

					if($query && $query->getNumRows())
					{
						$userinfo = $query->fetchObject();
						$query->free();
					}
				}

				if(isset($userinfo))
				{
					if(!isset($this->tuxxedo->cache->usergroups[$userinfo->usergroupid]))
					{
						throw new Tuxxedo_Basic_Exception('Unable to usergroup permissions, datastore possibly corrupted');
					}

					$this->userinfo		= $userinfo;
					$this->usergroupinfo 	= (object) $this->tuxxedo->cache->usergroups[$this->userinfo->usergroupid];

					$this->tuxxedo->db->setShutdownQuery('
										UPDATE 
											`' . TUXXEDO_PREFIX . 'sessions` 
										SET 
											`location` = \'%s\', 
											`lastactivity` = %d 
										WHERE 
											`sessionid` = \'%s\'', $this->tuxxedo->db->escape(TUXXEDO_SELF), session_id(), time());
				}
			}

			$this->tuxxedo->db->query('
							REPLACE INTO 
								`' . TUXXEDO_PREFIX . 'sessions` 
							VALUES
							(
								\'%s\', 
								%s,
								\'%s\', 
								%d
							)', session_id(), (is_object($this->userinfo) ? $this->userinfo->id : '\'\''), $this->tuxxedo->db->escape(TUXXEDO_SELF), time());

			$this->tuxxedo->db->setShutdownQuery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'sessions` 
								WHERE 
									`lastactivity` + %d < %d', self::$options['expires'], time());
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
			self::$options = Array(
						'expires'	=> $options['cookie_expires'], 
						'prefix'	=> $options['cookie_prefix'], 
						'domain'	=> $options['cookie_domain'], 
						'path'		=> $options['cookie_path']
						);

			session_set_cookie_params($options['cookie_expires'], $options['cookie_domain'], $options['cookie_path'], false, true);
			session_start();
		}

		/**
		 * Gets a session variable
		 *
		 * @param	string			Variable name
		 * @param	boolean			Whether to include the session prefix or not, defaults to true
		 * @return	mixed			Returns the session variable value on success, or null on failure
		 */
		public static function get($name, $prefix = true)
		{
			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			if(!isset($_SESSION[$name]))
			{
				return(NULL);
			}

			return($_SESSION[$name]);
		}

		/**
		 * Sets a session variable
		 *
		 * @param	string			Variable name
		 * @param	mixed			Variable value
		 * @param	boolean			Whether to include the session prefix or not, defaults to true
		 * @return	void			No value is returned
		 */
		public static function set($name, $value, $prefix = true)
		{
			if($prefix)
			{
				$name = self::$options['prefix'] . $name;
			}

			$_SESSION[$name] = $value;
		}

		/**
		 * Checks if a user is logged in or not
		 *
		 * @return	boolean			True if a user is logged in, otherwise false
		 */
		public function isLoggedIn()
		{
			return(is_object($this->userinfo));
		}

		/**
		 * Gets the user information for the current logged 
		 * in user
		 *
		 * @return	object			Returns an object with the users information, or boolean false if user isnt logged in
		 */
		public function getUserinfo()
		{
			if(!is_object($this->userinfo))
			{
				return(false);
			}

			return($this->userinfo);
		}

		/**
		 * Gets the usergroup information for the current 
		 * logged in user
		 *
		 * @return	object			Returns an object with the users usergroup information, or boolean false if user isnt logged in
		 */
		public function getUsergroup()
		{
			if(!is_object($this->userinfo))
			{
				return(false);
			}

			return($this->usergroupinfo);
		}

		/**
		 * Checks if a user is a member of a specific usergroup
		 *
		 * @return	boolean			Returns true if the user is a member of that group, otherwise false
		 */
		public function isMemberOf($groupid)
		{
			return(is_object($this->usergroupinfo) && $this->usergroupinfo->id == $groupid);
		}

		/**
		 * Logs a user out
		 *
		 * @return	void			No value is returned
		 */
		public function logout()
		{
			if(!is_object($this->userinfo))
			{
				return;
			}

			session_unset();
			session_destroy();

			$this->tuxxedo->db->setShutdownQuery('
								DELETE FROM 
									`' . TUXXEDO_PREFIX . 'sessions` 
								WHERE 
									`sessionid` = \'%s\'', $this->userinfo->sessionid);

			$this->userinfo = $this->usergroupinfo = NULL;
		}
	}
?>