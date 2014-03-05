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
		 * Upload error constant - Unknown (like no input etc)
		 *
		 * @var		integer
		 */
		const ERR_UNKNOWN		= 1;

		/**
		 * Upload error constant - Size exceeded
		 *
		 * @var		integer
		 */
		const ERR_SIZE			= 2;

		/**
		 * Upload error constant - Cannot write (or move the uploaded file)
		 *
		 * @var		integer
		 */
		const ERR_CANT_WRITE		= 3;

		/**
		 * Upload error constant - Fileinfo failed (extension not available, or unable to resolve)
		 *
		 * @var		integer
		 */
		const ERR_MIME_FINFO		= 4;

		/**
		 * Upload error constant - Custom (caused by event callbacks)
		 *
		 * @var		integer
		 */
		const ERR_CUSTOM		= 5;

		/**
		 * Upload error constant - Naming (could not determine name if filename or extension was missing in the original file)
		 *
		 * @var		integer
		 */
		const ERR_NAMING		= 6;

		/**
		 * Upload error constant - File override (if 'allow_override' is false and naming matches)
		 *
		 * @var		integer
		 */
		const ERR_OVERRIDE		= 7;


		/**
		 * Error code (defaults to none)
		 *
		 * @var		integer
		 */
		public $error			= self::ERR_NONE;

		/**
		 * Filename without the extension
		 *
		 * @var		string
		 */
		public $filename;

		/**
		 * File extension (may be empty) and does not contain the initial dot
		 *
		 * @var		string
		 */
		public $extension;

		/**
		 * File MIME type, this may be fake
		 *
		 * @var		string
		 */
		public $mime;

		/**
		 * Real MIME type, this is false by default and will only have a value if 
		 * the 'resolve_mime' option was on in the upload handle
		 *
		 * @var		boolean|string
		 */
		public $real_mime		= false;
	}
?>