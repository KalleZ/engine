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
	 *
	 * =============================================================================
	 */


	/**
	 * Exception namespace, this contains all the core exceptions defined within 
	 * the library.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/*
	 * SQL Exception
	 *
	 * Exception designed to carry error information from a failed 
	 * query call.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class SQL extends Basic
	{
		/**
		 * Current SQL state
		 *
		 * @var		string
		 */
		protected $sqlstate;

		/**
		 * Driver that caused this error
		 *
		 * @var		string
		 * @since	1.1.0
		 */
		protected $driver;


		/**
		 * Constructs a new SQL exception
		 *
		 * @param	string			The SQL that caused this error
		 * @param	string			The driver that caused this error
		 * @param	string			The error that occured
		 * @param	integer			The associated error number for the error
		 * @param	string			Optionally, an SQL state if the database driver supports it
		 *
		 * @changelog	1.1.0			Added the $driver parameter
		 */
		public function __construct($sql, $driver, $error, $errno, $sqlstate = NULL)
		{
			$this->sql		= $sql;
			$this->driver		= $driver;
			$this->message 		= $error;
			$this->code		= (integer) $errno;
			$this->sqlstate		= ($sqlstate ? $sqlstate : false);
		}

		/**
		 * Gets the SQL string that caused the exception to trigger
		 *
		 * @return	string			Returns the SQL string
		 */
		public function getSQL()
		{
			return($this->sql);
		}

		/**
		 * Gets the current SQL state if the underlaying database 
		 * driver that threw this exception supports it.
		 *
		 * @return	string			Returns the SQL state if supported, otherwise false is returned
		 */
		public function getSQLState()
		{
			return($this->sqlstate);
		}

		/**
		 * Gets the driver that caused this exception to trigger
		 *
		 * @return	string			Returns the driver name
		 *
		 * @since	1.1.0
		 */
		public function getDriver()
		{
			return($this->driver);
		}
	}
?>