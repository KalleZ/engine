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
	 * Database Access Layer implementation. This namespace controls 
	 * all access to the database, multiple drivers for the database 
	 * can be loaded at the same time, along with multiple database 
	 * connection, even to the same database.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Database;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Base database driver interface
	 *
	 * This defines the driver structure of which functions a driver 
	 * must implement and how they should be called.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	interface Driver
	{
		/**
		 * Returns if the current system supports the driver, if this 
		 * method isn't called, a driver may start not function properly 
		 * on the system
		 *
		 * @return	boolean				True if dirver is supported, otherwise false
		 */
		public function isDriverSupported();

		/**
		 * Get driver requirements, as an array that can be iterated to 
		 * see which requirements that passes, and which that do not
		 *
		 * Each driver may return their own set of keys, but built-in 
		 * drivers will remain consistent across each other
		 *
		 * @return	array				Returns an array containing elements of which requirements and their status
		 */
		public function getDriverRequirements();

		/**
		 * Connect to a database, if no connection isn't already 
		 * active
		 *
		 * @param	array				Change the configuration and use this new configuration to connect with
		 * @return	boolean				True if a successful connection was made
	 	 *
		 * @throws	\Tuxxedo\Exception\Basic	If a database connection fails
		 */
		public function connect(Array $configuration = NULL);

		/**
		 * Close a database connection
		 *
		 * @return	boolean				True if the connection was closed, otherwise false
		 */
		public function close();

		/**
		 * Checks if a connection is active
		 *
		 * @return	boolean				True if a connection is currently active, otherwise false
		 */
		public function isConnected();

		/**
		 * Checks if a variable is a connection of the same type 
		 * as the one used by the driver
		 *
		 * @param	mixed				The variable to check
		 * @return	boolean				True if the variable type matches, otherwise false
		 */
		public function isLink($link);

		/**
		 * Checks if the current connection is persistent
		 *
		 * @return	boolean				True if the connection is persistent, otherwise false
		 */
		public function isPersistent();

		/**
		 * Checks if a variable is a result of the same type as 
		 * the one used by the driver
		 *
		 * @param	mixed				The variable to check
		 * @return	boolean				True if the variable type matches, otherwise false
		 */
		public function isResult($result);

		/**
		 * Get the error message from the last occured error
		 * error
		 *
		 * @return	string				The error message
		 */
		public function getError();

		/**
		 * Get the error number from the last occured error
		 *
		 * @return	integer				The error number
		 */
		public function getErrno();

		/**
		 * Get the last insert id from last executed INSERT statement
		 *
		 * @return	integer				Returns the last insert id, and boolean false on error
		 */
		public function getInsertId();

		/**
		 * Escape a piece of data using the database specific 
		 * escape method
		 *
		 * @param	mixed				The data to escape
		 * @return	string				Escaped data
		 */
		public function escape($data);

		/**
		 * Executes a query and returns the result on SELECT 
		 * statements
		 *
		 * @param	string				SQL to execute
		 * @return	boolean|object			Returns a result object on SELECT statements, and boolean true otherwise if the statement was executed
		 *
		 * @throws	\Tuxxedo\Exception\SQL		If the SQL should fail for whatever reason, an exception is thrown
		 */
		public function query($sql);
	}
?>