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
	 * Developmental Tools namespace. This namespace is for all development 
	 * tool related routines, as used by /dev/tools.
	 *
	 * @author              Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version             1.0
	 * @package             Engine
	 * @subpackage          DevTools
	 */
	namespace DevTools;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Datamanager;
	use Tuxxedo\Registry;
	use Tuxxedo\Session;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * DevTools user class, this class is used to bypass some of the 
	 * restrictions the core user class prohibits in order to ease 
	 * the code for some of the development tools and testing.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	class User extends \Tuxxedo\User
	{
		/**
		 * User id of the current user being impersonated, this 
		 * value is 0 if no impersonation is currently happening
		 *
		 * @var		integer
		 */
		protected $impersonate		= 0;


		/**
		 * Destructor
		 */
		public function __destruct()
		{
			if($this->impersonate)
			{
				$this->logout();
			}

			parent::__destruct();
		}

		/**
		 * Checks if a user is currently being impersonated by this instance
		 *
		 * @return	boolean			Returns true if a user is being impersonated, otherwise false
		 */
		public function isImpersonatingUser()
		{
			return(!empty($this->impersonate));
		}

		/**
		 * Impersonates a session as a user.
		 *
		 * This requires the session data to be loaded when the object 
		 * was instanciated.
		 *
		 * Note, if this is used while another user is currently logged in 
		 * from the script its being called from, it will disconnect the 
		 * session and the user will need to log-in again.
		 *
		 * @param	integer			User id
		 * @return	boolean			Returns true if the user was logged in with success, otherwise false
		 */
		public function impersonateAsUser($identifier)
		{
			if(empty(Session::$id))
			{
				return(false);
			}
			elseif(isset($this->userinfo->id) && $this->userinfo->id)
			{
				$this->logout(true);
			}

			$userinfo = $this->getUserInfo($identifier, 'username');

			if(!$userinfo)
			{
				return(false);
			}

			Session::set('userid', $userinfo->id);
			Session::set('password', $userinfo->password);

			$this->impersonate			= $userinfo->id;
			$this->userinfo				= $userinfo;
			$this->usergroupinfo			= $this->registry->datastore->usergroups[$userinfo->usergroupid];
			$this->sessiondm['userid'] 		= $userinfo->id;
			$this->userinfo->permissions		= (integer) $this->userinfo->permissions;
			$this->usergroupinfo['permissions'] 	= (integer) $this->usergroupinfo['permissions'];

			$this->registry->set('userinfo', $userinfo);
			$this->registry->set('usergroup', $this->usergroupinfo);

			$this->setPermissionConstants();

			return(true);
		}

		/**
		 * Impersonates a session with a specific usergroup, if no user 
		 * is specified, then the first possible user in the user table 
		 * will be used. The same rules applies to this method as to the 
		 * user impersonation method.
		 *
		 * @param	integer			The usergroup to identify as
		 * @param	integer			The user to impersonate if any
		 * @return	boolean			Returns true on success, and false on error
		 */
		public function impersonateAsUsergroup($identifier, $user = NULL)
		{
			if(!$user)
			{
				$uid = $this->registry->db->query('
									SELECT 
										`id`
									FROM 
										`' . \TUXXEDO_PREFIX . 'users` 
									LIMIT 1');

				if(!$uid || !$uid->getNumRows())
				{
					return(false);
				}

				$user = $uid->fetchObject()->id;

				$uid->free();
			}

			if(!isset($this->registry->datastore->usergroups[$identifier]) || !$this->impersonateAsUser($user))
			{
				return(false);
			}

			$this->userinfo->usergroupid		= $identifier;
			$this->usergroupinfo			= $this->registry->datastore->usergroups[$identifier];
			$this->usergroupinfo['permissions']	= (integer) $this->usergroupinfo['permissions'];

			$this->registry->set('userinfo', $this->userinfo);
			$this->registry->set('usergroup', $this->usergroupinfo);

			return(true);
		}

		/**
		 * Overloads the logout function to make it complatible 
		 * with the user impersonating code
		 *
		 * @return	boolean				Returns true if the user was logged out, otherwise false
		 */
		public function logout()
		{
			if($this->impersonate)
			{
				$this->impersonate = 0;
			}

			parent::logout();
		}
	}
?>