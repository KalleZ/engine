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
	 
    namespace Tuxxedo\Exception;

    /**
	 * SQL Exception
	 *
	 * Exception designed to carry error information from a failed 
	 * query call.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class SQL extends \Tuxxedo\Exception
	{
		/**
		 * Current SQL state
		 *
		 * @var		string
		 */
		public $sqlstate;


		/**
		 * Constructs a new SQL exception
		 *
		 * @param	string			The error that occured
		 * @param	integer			The associated error number for the error
		 * @param	string			Optionally, an SQL state if the database driver supports it
		 */
		public function __construct($sql, $error, $errno, $sqlstate = NULL)
		{
			$this->sql		= $sql;
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
	}
?>