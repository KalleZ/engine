<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	namespace Tuxxedo\Datamanager;

	/**
	 * Datastore requirement for using the datamanager
	 *
	 * This interface is for datamanagers that interacts with the datastore 
	 * cache to rebuild it to prevent manual update of it.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	interface APICache
	{
		/**
		 * This event method is called if the query to store the 
		 * data was success, to rebuild the datastore cache
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			A virtually populated array from the datamanager abstraction
		 * @return	boolean			Returns true if the datastore was updated with success, otherwise false
		 */
		public function rebuild(Registry $registry, Array $virtual);
	}
