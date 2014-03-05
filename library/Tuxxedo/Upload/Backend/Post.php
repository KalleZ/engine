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
		 * @param	string				Optionally the file name the file should have, pass NULL to retain the original filename
		 * @param	string				Optionally the extension the file should have (for example: 'jpg'), pass NULL to retain the original extension
		 * @return	boolean				Returns true if the transfer was a success, otherwise false (failed hooks and the like)
		 */
		public function process($input, $filename = NULL, $extension = NULL)
		{
			$desc 		= new Upload\Descriptor;
			$desc->error	= Upload\Descriptor::ERR_NONE;
			if(!$_FILES || !isset($_FILES[$input]) || !isset($_FILES[$input]['tmp_name']) || \is_array($_FILES[$input]['tmp_name']) || empty($_FILES[$input]['tmp_name']))
			{
				$desc->error = Upload\Descriptor::ERR_UNKNOWN;

				return($desc);
			}

			$split			= \explode('.', $_FILES[$input]['name']);
			$ext			= \array_pop($split);
			$desc->filename		= ($filename !== NULL ? $filename : \join('.', $split));
			$desc->extension	= ($extension !== NULL ? $extension : $ext);

			unset($ext, $split);

			if(empty($desc->filename))
			{
				$desc->error = Upload\Descriptor::ERR_NAMING;

				return($desc);
			}

			$this->handle->getEventHandler()->fire('preprocess', [$desc]);

			if(\strpos($desc->filename, '/') !== false || \strpos($desc->filename, '\\') !== false)
			{
				$desc->error = Upload\Descriptor::ERR_NAMING;

				return($desc);
			}

			if($_FILES[$input]['size'] < 1 || $_FILES[$input]['size'] > $this->handle['size_limit'])
			{
				$desc->error = Upload\Descriptor::ERR_SIZE;

				return($desc);
			}
			elseif(!self::$finfo && $this->handle['resolve_mime'] || self::$finfo && !\finfo_file(self::$finfo, $_FILES[$input]['tmp_name']))
			{

// @TODO We need the retval from finfo_file() here
var_dump(self::$finfo, $this->handke['resolve_mime'], finfo_file(self::$finfo, $_FILES[$input]['tmp_name']));

				$desc->error = Upload\Descriptor::ERR_MIME_FINFO;

				return($desc);
			}
			elseif(!@\move_uploaded_file($_FILES[$input]['tmp_name'], $this->handle['directory'] . $desc->filename . (!empty($desc->extension) ? '.' . $desc->extension : '')))
			{
				$desc->error = Upload\Descriptor::ERR_CANT_WRITE;

				return($desc);
			}

			$this->handle->getEventHandler()->fire('postprocess', [$desc]);

			return($desc);
		}
	}
?>