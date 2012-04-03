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
		 * Constructor
		 */
		public function __construct()
		{
			$this->registry 	= Registry::init();
			$this->userinfo 	= $this->usergroupinfo = new \stdClass;
			$this->information	= $this->userinfo;

			if($this->registry->session['__devtools_userid'])
			{
				$this->userinfo 		= $this->getUserinfo($this->registry->session['__devtools_userid']);
				$this->userinfo->permissions	= (integer) $this->userinfo->permissions;
			}

			$this->setPermissionConstants();
		}
	}
?>