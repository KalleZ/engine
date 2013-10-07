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
		'name'		=> '', 

		/**
		 * Version
		 *
		 * Version of the application, if any. Used together with the name 
		 * on error screens
		 */
		'version'	=> '', 

		/**
		 * Canonical name
		 *
		 * If you are developing components that can be utilized by more 
		 * than one application, then you can specify a canonical name 
		 * for the application.
		 *
		 * These names are generally expected to be lower case and not have 
		 * special characters and spaces. Underscores is an exclusion to 
		 * this. The main reason for these is to have an autoloadable canonical
		 * name.
		 *
		 * This can also be utilized by the MVC component for routing.
		 */
		'canonical'	=> '', 

		/**
		 * Debug mode
		 *
		 * Enables the debugging mode, error screens become more expressive 
		 * and some error messages will become more verbose. Fatal errors 
		 * will display a backtrace
		 */
		'debug'		=> true

	/**
	 * End application configuration
	 */
	), 

	/**
	 * Debug configuration
	 */
	'debug' => Array(

		/**
		 * Trace mode
		 *
		 * If enabled this activates the tracing component 
		 * which will time certain calls and trace them 
		 * back to where they originated from.
		 *
		 * Tracing is costly, and only applies if debug mode 
		 * is enabled. Application performance will greatly 
		 * be reduced, so only enable this for in-depth 
		 * debugging.
		 *
		 * Currently the following parts can be traced:
		 *
		 * 1) Queries sent to the database layer
		 */
		'trace'		=> true, 

		/**
		 * Trace timer precision
		 *
		 * Precision can be used to fine tune how the trace 
		 * timers are represented.
		 */
		'precision'	=> 5, 

		/**
		 * Backtrace call details
		 *
		 * If this option is enabled, then all frames in the 
		 * backtraces shown on error screens will have the full 
		 * call line with arguments.
		 */
		'fullbacktrace'	=> false
	
	/**
	 * End debug configuration
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
		 *
		 * A quick note for DevTools and SQLite, if you are using an 
		 * absolute path here, then you can ignore the additional 
		 * path in the 'devtools' section below.
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
		'username' 	=> 'root', 

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
		 * Protective mode
		 *
		 * This directive can have 3 meanings depending on its value:
		 *
		 * 0) Disabled, no password protection
		 * 1) Enabled, requires a single password
		 *
		 * For more in-depth configuration of each of these modes 
		 * when enabled, see below options.
		 */
		'protective'	=> 0,  

		/**
		 * Password
		 *
		 * This only applies if the protective mode is set to the 
		 * value of '1' (single password).
		 */
		'password'	=> '', 

		/**
		 * Database path
		 *
		 * This only have an effect if you are using a file system 
		 * based database like SQLite. If you are using an absolute 
		 * path in the 'database' section then you can ignore this.
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