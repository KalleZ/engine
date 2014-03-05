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
	 * Upload backend namespace, this contains backend implementations for file 
	 * transfers using various methods for the \Tuxxedo\Upload class.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Upload\Backend;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Upload;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Upload backend for HTTP URLs
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Url implements Upload\Backend
	{
		/**
		 * Upload handle that loaded in this backend, to reference options 
		 * and the like.
		 *
		 * @var		\Tuxxedo\Upload
		 */
		protected $handle;

		/**
		 * Event handler, associated with the upload handle
		 *
		 * @var		\Tuxxedo\Design\EventHandler
		 */
		protected $event_handler;

		/**
		 * Fileinfo handle
		 *
		 * @var		resource
		 */
		protected static $finfo;

		/**
		 * List of allowed protocols
		 *
		 * @var		array
		 */
		protected static $protocols		= [
								'http'
								];


		/**
		 * Constructor
		 *
		 * @param	\Tuxxedo\Upload			The upload handle that initiated this backend
		 * @param	\Tuxxedo\Design\EventHandler	The event handler associated with the upload handle
		 */
		public function __construct(Upload $handle, Design\EventHandler $eh_ptr)
		{
			static $first_time;

			$this->handle 		= $handle;
			$this->event_handler	= $eh_ptr;

			if($first_time === NULL)
			{
				$first_time = true;

				if(\extension_loaded('fileinfo'))
				{
					self::$finfo = \finfo_open(\FILEINFO_MIME_TYPE);
				}

				if(\extension_loaded('openssl'))
				{
					self::$protocols['https'] = true;
				}
			}
		}

		/**
		 * Allows a new protocol to be used
		 *
		 * @param	string				The new protocol to allow
		 * @return	void				No value is returned
		 */
		public static allowProtocol($protocol)
		{
			$protocol = \strtolower($protocol);

			if($protocol && !isset(self::$protocols[$protocol]))
			{
				self::$protocols[$protocol] = true;
			}
		}

		/**
		 * Disallows an already allowed protocol
		 *
		 * @param	string				The protocol to disallow
		 * @return	void				No value is returned
		 */
		public static disallowProtocol($protocol)
		{
			$protocol = \strtolower($protocol);

			if($protocol && isset(self::$protocols[$protocol]))
			{
				unset(self::$protocols[$protocol]);
			}
		}

		/**
		 * Tells the backend to process this input and initiate the transfer 
		 *
		 * @param	string				The input specific to this backend
		 * @param	string				Optionally the file name the file should have, pass NULL to retain the original filename
		 * @param	string				Optionally the extension the file should have (for example: 'jpg'), pass NULL to retain the original extension
		 * @return	boolean				Returns true if the transfer was a success, otherwise false (failed hooks and the like)
		 */
		public function process($input)
		{
			$desc = new Upload\Descriptor;

/* ... */

			return($desc);
		}
	}
?>