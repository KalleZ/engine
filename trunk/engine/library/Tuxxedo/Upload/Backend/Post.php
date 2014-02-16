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
	 * Upload backend for HTTP POST requests, this enables the possibility of the 
	 * HTML tag: <input type="file" ... /> to be used.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Post implements Upload\Backend
	{
		/**
		 * Upload handle that loaded in this backend, to reference options 
		 * and the like.
		 *
		 * @var		\Tuxxedo\Upload
		 */
		protected $handle;

		/**
		 * Fileinfo handle
		 *
		 * @var		resource
		 */
		protected static $finfo;


		/**
		 * Constructor
		 *
		 * @param	\Tuxxedo\Upload			The upload handle that initiated this backend
		 */
		public function __construct(Upload $handle)
		{
			$this->handle = $handle;

			if(self::$finfo === NULL && \extension_loaded('fileinfo'))
			{
				self::$finfo = \finfo_open(\FILEINFO_MIME_TYPE);
			}
		}

		/**
		 * Tells the backend to process this input and initiate the transfer 
		 *
		 * @param	string				The input specific to this backend
		 * @return	boolean				Returns true if the transfer was a success, otherwise false (failed hooks and the like)
		 */
		public function process($input)
		{
			if(!$_FILES || !isset($_FILES[$input]) || !isset($_FILES[$input]['tmp_name']) || \is_array($_FILES[$input]['tmp_name']))
			{
				return(false);
			}

			$desc = new Upload\Descriptor;

			if($_FILES[$input]['size'] < 1 || $_FILES[$input]['size'] > $this->handle['size_limit'])
			{
				$desc->error = self::ERR_SIZE;

				return($desc);
			}
/*
			elseif(self::$finfo && \finfo_file(self::$finfo, $_FILES[$input]['tmp_name']))
			{
				$desc->error = self::ERR_MIME_FINFO;
			}
*/
			elseif(!@\move_uploaded_file($_FILES[$input]['tmp_name'], $this->handle['directory'] . $_FILES[$input]['name']))
			{
				$desc->error = self::ERR_CANT_WRITE;

				return($desc);
			}

			return($desc);
		}
	}
?>