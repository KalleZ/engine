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
	 * @subpackage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Datamanager hooks, this namespace contains special hooks that can be used 
	 * trigger special routines within datamanager adapters.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Datamanager\Hooks;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * Datastore requirement for using the datamanager
	 *
	 * This interface is for datamanagers that interacts with the datastore 
	 * cache to rebuild it to prevent manual update of it.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	interface Cache
	{
		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	array				A virtually populated array from the datamanager abstraction
		 * @return	boolean				Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Registry $registry, Array $virtual);
	}
?>