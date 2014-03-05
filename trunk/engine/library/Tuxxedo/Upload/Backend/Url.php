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
	use Tuxxedo\Design;
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
								'http' => true
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
		public static function allowProtocol($protocol)
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
		public static function disallowProtocol($protocol)
		{
			$protocol = \strtolower($protocol);

			if($protocol && isset(self::$protocols[$protocol]))
			{
				unset(self::$protocols[$protocol]);
			}
		}

		/**
		 * Gets the allowed protocols
		 *
		 * @return	array				Returns the list of allowed protocols (may be an empty array)
		 */
		public static function getProtocols()
		{
			return(\array_keys(self::$protocols));
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
			$desc->backend	= 'url';
			$desc->error	= Upload\Descriptor::ERR_NONE;

			if(empty($input) || ($sock = @fopen($input, 'rb')) === false)
			{
				$desc->error = Upload\Descriptor::ERR_UNKNOWN;

				return($desc);
			}

			$size = 0;
			$type = '';
			$meta = stream_get_meta_data($sock);

			if(!$meta || !isset(self::$protocols[$meta['wrapper_type']]) || !isset($meta['wrapper_data']) || !$meta['wrapper_data'])
			{
				$desc->error = Upload\Descriptor::ERR_UNKNOWN;

				return($desc);
			}

			foreach($meta['wrapper_data'] as $data)
			{
				$data 		= \explode(':', $data);
				$data[0]	= \strtolower($data[0]);

				if($data[0] == 'content-type')
				{
					$type = \trim($data[1]);
				}
				elseif($data[0] == 'content-length')
				{
					$size = (integer) $data[1];
				}
			}

			$split			= \explode('/', \str_replace('\\', '/', $input));
			$split			= \explode('.', end($split));
			$ext			= \array_pop($split);
			$desc->filename		= ($filename !== NULL ? $filename : \join('.', $split));
			$desc->extension	= ($extension !== NULL ? $extension : $ext);
			$desc->mime		= $type;
			$desc->real_mime	= false;

			unset($meta, $type, $ext, $split);

			if(empty($desc->filename))
			{
				$desc->error = Upload\Descriptor::ERR_NAMING;

				return($desc);
			}

			$desc->rsrc = $sock;

			$this->event_handler->fire('preprocess', [$desc]);

			$new_filename = $this->handle['directory'] . $desc->filename . (!empty($desc->extension) ? '.' . $desc->extension : '');

			if(\strpos($desc->filename, '/') !== false || \strpos($desc->filename, '\\') !== false)
			{
				$desc->error = Upload\Descriptor::ERR_NAMING;

				return($desc);
			}
			elseif($size < 1 || $size > $this->handle['size_limit'])
			{
				$desc->error = Upload\Descriptor::ERR_SIZE;

				return($desc);
			}
			elseif(!$this->handle['allow_override'] && \is_file($new_filename))
			{
				$desc->error = Upload\Descriptor::ERR_OVERRIDE;

				return($desc);
			}

/*
			elseif(!self::$finfo && $this->handle['resolve_mime'] || self::$finfo && ($real_mime = \finfo_file(self::$finfo, $_FILES[$input]['tmp_name'])) === false)
			{
				$desc->error = Upload\Descriptor::ERR_MIME_FINFO;

				return($desc);
			}

			if(!@\move_uploaded_file($_FILES[$input]['tmp_name'], $new_filename))
			{
				$desc->error = Upload\Descriptor::ERR_CANT_WRITE;

				return($desc);
			}

*/

			if(isset($real_mime))
			{
				$desc->real_mime = $real_mime;
			}

			$this->event_handler->fire('process', [$desc]);

			if($desc->error != Upload\Descriptor::ERR_NONE)
			{
				return($desc);
			}

			$this->event_handler->fire('postprocess', [$desc]);

			fclose($sock);

			return($desc);
		}
	}
?>