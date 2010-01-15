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
	 * Main configuration
	 *
	 * This configuration is split in sections, each section
	 * lets you configure how a function should work.
	 *
	 * This file is only for configuration that cannot or 
	 * should not be stored in the database so its editable 
	 * through the control panel.
	 */
	$configuration = Array(

	/**
	 * Database configuration
	 */
	'database' => Array(

	/**
	 * Driver
	 *
	 * Select which driver you wish to use for accessing the 
	 * database in which you installed Tuxxedo Engine.
	 *
	 * By default Tuxxedo Engine comes with the following 
	 * drivers:
	 *
	 * mysql 	MySQL 3.23+
	 * mysqli	MySQL 4.1+
	 * pdo		Any PDO extension (*)
	 *
	 * Drivers marked with (*) requires a sub driver to be defined
	 */
	'driver' 	=> 'mysqli', 

	/**
	 * Sub driver
	 *
	 * Some drivers, like abstraction layers need a sub driver to 
	 * know which driver to load.
	 *
	 * If you for example are using PDO and want to use MySQL as 
	 * your backend, then this value have to be 'mysql'.
	 */
	'subdriver'	=> 'mysql', 

	/**
	 * DSN prefix
	 *
	 * Some drivers that uses DSN strings, like PDO. May need a 
	 * DSN prefix if the prefix differs from the driver name.
	 */
	'dsnprefix'	=> '', 

	/**
	 * DSN suffix
	 *
	 * If a database driver that uses a DSN, like PDO, needs 
	 * additional parameters when connecting then supply them 
	 * here in the format of:
	 *
	 * parameter1=value1; parameter2=value2;
	 */
	'dsnsuffix'	=> '', 

	/**
	 * Database host
	 *
	 * This is the hostname for where the database system 
	 * can be accessed. In most cases this will simply be 
	 * 'localhost'.
	 */
	'hostname' 	=> 'localhost', 

	/**
	 * Secure connection
	 *
	 * If your database server is protected by a secure 
	 * connection and therefore requires one in order to 
	 * connect it, then set this value to true.
	 *
	 * The following database drivers support this feature:
	 *
	 * mysql
	 * mysqli
	 */
	'ssl'		=> false, 

	/**
	 * Host port
	 *
	 * If your database server is located on a non default 
	 * port, then set this to the port number or leave it 
	 * empty if none.
	 */
	'port'		=> '', 

	/**
	 * Connection timeout
	 *
	 * The timeout limit for the connection before the 
	 * timeout occurs. If non set then it fallbacks on 
	 * the internal timeout from php or the server.
	 */
	'timeout'	=> 3, 

	/**
	 * Database socket
	 *
	 * If your connecting to a database through a socket, 
	 * then specify the path here or leave it empty for 
	 * none.
	 */
	'socket'	=> '', 

	/**
	 * Persistent connection
	 *
	 * Select whenever you want the use persistent connection 
	 * for when connecting the database. Note that not all 
	 * drivers support this and will fallback to regular 
	 * connections even if this option is on.
	 *
	 * To use persistent connections with mysqli, you must 
	 * use PHP 5.3 or greater, else Tuxxedo Engine will 
	 * fallback on regular connections.
	 */
	'persistent'	=> false, 

	/**
	 * Database name
	 *
	 * Name of the database in which Tuxxedo Engine is installed
	 */
	'database'	=> 'tuxxedo', 

	/**
	 * Database user
	 *
	 * Username used for accessing the database, the user 
	 * set here must have permissions to access the database 
	 * set above.
	 */
	'username' 	=> 'tuxxedo', 

	/**
	 * Password
	 *
	 * This is the password accosiated with the user set 
	 * above. If the user doesn't require a password then 
	 * leave this empty.
	 */
	'password'	=> '', 

	/**
	 * Table prefix
	 *
	 * If your  database tables have a prefix, then set this 
	 * to the value of the prefix.
	 */
	'prefix'	=> ''

	/**
	 * End database configuration
	 */
	)

	/**
	 * End configuration
	 */
	);
?>