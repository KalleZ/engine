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
	 * Upload API namespace, this contains routines, and patterns for various 
	 * upload related operations.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Upload;


	/**
	 * Aliasing rules
	 */


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Upload descriptor, this contains meta information regarding the 
	 * uploaded file, including error codes in a consistent 
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Descriptor
	{
		/**
		 * Upload error constant - None
		 *
		 * @var		integer
		 */
		const ERR_NONE			= 0;

		/**
		 * Upload error constant - Size exceeded
		 *
		 * @var		integer
		 */
		const ERR_SIZE			= 1;

		/**
		 * Upload error constant - Cannot write (or move the uploaded file)
		 *
		 * @var		integer
		 */
		const ERR_CANT_WRITE		= 2;


		/**
		 * Error code (defaults to none)
		 *
		 * @var		integer
		 */
		public $error			= self::ERR_NONE;
	}
?>