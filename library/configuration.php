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
	 * @subpckage		Library
	 *
	 * =============================================================================
	 */


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
	 * Application configuration
	 */
	'application' => Array(

	/**
	 * Name
	 *
	 * Name of the application, used on error screens. This is not 
	 * required
	 */
	'name'		=> 'Engine Development Labs', 

	/**
	 * Version
	 *
	 * Version of the application, if any. Used together with the name 
	 * on error screens
	 */
	'version'	=> '1.1.0', 

	/**
	 * Debug mode
	 *
	 * Enables the debugging mode, error screens become more expressive 
	 * and some error messages will become more verbose. Fatal errors 
	 * will display a backtrace
	 */
	'debug'		=> true, 

	/**
	 * End application configuration
	 */
	), 

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
	 * sqlite	SQLite 3+
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
	'subdriver'	=> '', 

	/**
	 * DSN prefix
	 *
	 * Some drivers that uses DSN strings, like PDO. May need a 
	 * DSN prefix if the prefix differs from the driver name.
	 *
	 * This parameter is ignored in PDO_SQLite.
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
	 *
	 * This parameter is ignored in PDO_SQLite.
	 */
	'dsnsuffix'	=> '', 

	/** 
	 * Database host
	 *
	 * This is the hostname for where the database system 
	 * can be accessed. In most cases this will simply be 
	 * 'localhost'.
	 *
	 * Some databases, like SQLite, thats file based does 
	 * not use a hostname.
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
	 *
	 * Some databases, like SQLite, thats file based does 
	 * not use a port.
	 */
	'port'		=> '', 

	/**
	 * Connection timeout
	 *
	 * The timeout limit for the connection before the 
	 * timeout occurs. If non set then it fallbacks on 
	 * the internal timeout from php or the server.
	 *
	 *
	 * Some databases, like SQLite, thats file based does 
	 * not use a timeout.
	 */
	'timeout'	=> 3, 

	/**
	 * Database socket
	 *
	 * If your connecting to a database through a socket, 
	 * then specify the path here or leave it empty for 
	 * none.
	 *
	 * Some databases, like SQLite, thats file based does 
	 * not use a socket.
	 */
	'socket'	=> '', 

	/**
	 * Connection delay
	 *
	 * This options delays creating the actual database 
	 * connection until the first query call within the 
	 * instance.
	 */
	'delay'		=> true, 

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
	 *
	 *
	 * Some databases, like SQLite, thats file based cannot 
	 * benefit from persistent connections.
	 */
	'persistent'	=> false, 

	/**
	 * Database name
	 *
	 * Name of the database in which Tuxxedo Engine is installed.
	 *
	 * File based databases, like SQLite, will uses this value 
	 * as a path to where the database is located, like:
	 *
	 * 'database' => '/path/to/tuxxedo.sqlite3'
	 *
	 * SQLite may use :memory: aswell.
	 */
	'database'	=> 'tuxxedo', 

	/**
	 * Database user
	 *
	 * Username used for accessing the database, the user 
	 * set here must have permissions to access the database 
	 * set above.
	 *
	 * Some databases, like SQLite, thats file based does 
	 * not use a username.
	 */
	'username' 	=> 'tuxxedo', 

	/**
	 * Password
	 *
	 * This is the password accosiated with the user set 
	 * above. If the user doesn't require a password then 
	 * leave this empty.
	 *
	 * If the SQLite driver is used and the password is set
 	 * then it will act as an encryption key for opening the 
	 * database. Note that this does not work for SQLite when 
	 * used inconjuction with PDO.
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
	), 

	/**
	 * Development Tools configuration
	 */
	'devtools' => Array(

	/**
	 * Database path for file based databases if above 
	 * path is relative.
	 */
	'database'	=> '../sql/bin/tuxxedo.sqlite3'

	/**
	 * End Development Tools configuration
	 */
	)

	/**
	 * End configuration
	 */
	);
?>