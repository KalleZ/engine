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
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Design;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Upload API
	 *
	 * This class allows to queue multiple files to upload files, files 
	 * can be uploaded using different backends. By default the following 
	 * are provided:
	 *
 	 *  - post		Uploads from a HTTP POST form request
	 *
	 * Custom handlers can be used and will be loaded Just-In-Time as an 
	 * the queue reaches that file to be uploaded.
	 *
	 * Filters can be used to filter out files during the upload process, 
	 * and can also be used to filter out files of specific types, for 
	 * example only allow upload of images, file renaming and so on.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Upload extends Design\InfoAccess
	{
		/**
		 * Upload queue
		 *
		 * @var			array
		 */
		protected $queue			= Array();

		/**
		 * List of valid backends
		 *
		 * @var			array
		 */
		protected static $valid_backends	= Array(
								'post'		=> true
								);


		/**
		 * Constructs a new upload object
		 */
		public function __construct()
		{
			$this->information = Array(
							'size_limit'	=> 10485760, 
							'directory'	=> './', 
							'resolve_mime'	=> true
							);
		}

		/**
		 * Queues a new object for upload
		 *
		 * @param	string				The protocol to use as backend for this upload transfer ('post' for HTML forms)
		 * @param	string				The field name (<input type="file" name="XXX" /> name for 'post')
		 * @param	string				Optionally the name identifier if this item might be unqueued at a later point
		 * @return	void				No value is returned
		 */
		public function queue($backend, $input, $name = NULL)
		{
			$backend = \strtolower($backend);

			if(!isset(self::$valid_backends[$backend]))
			{
				return;
			}

			$struct = Array(
					$backend, 
					$input
					);

			if($name !== NULL)
			{
				$this->queue[$name] = $struct;

				return;
			}

			$this->queue[] = $struct;
		}

		/**
		 * Unqueues an object
		 *
		 * @param	string				The name optionally supplied to the queue method
		 * @return	void				No value is returned
		 */
		public function unqueue($name)
		{
			if(isset($this->queue[$name]))
			{
				unset($this->queue[$name]);
			}
		}

		/**
		 * Invokes the uploading process, and clearing the queue after
		 *
		 * @return	array				Returns an array with a list of status descriptors
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception on corrupted backends
		 */
		public function upload()
		{
			static $handlers, $factory;

			if($handlers === NULL)
			{
				$this_ptr	= $this;
				$handlers 	= Array();
				$factory	= function($backend) use($this_ptr, $handlers)
				{
					$class = '\Tuxxedo\Upload\Backend\\' . \ucfirst(\strtolower($backend));

					if(isset($handlers[$backend]))
					{
						return($handlers[$backend]);
					}

					$temp = new $class($this_ptr);

					if(!($temp instanceof Upload\Backend))
					{
						throw new Exception\Basic('Corrupt upload backend, backend does not follow the interface specification');
					}

					return($handlers[$backend] = $temp);
				};
			}

			if(!$this->queue)
			{
				return(false);
			}

			$status = Array();

			foreach($this->queue as $index => $obj)
			{
				$transfer = $factory($obj[0]);

// @todo use \Tuxxedo\Upload\Descriptor
				$status[$index] = $transfer->process($obj[1]);
			}

			return($status);
		}

		/**
		 * __invoke() alias for invoking the uploading process
		 *
		 * @return	array				Returns an array with a list of status codes, false in case of a general failure
		 *
		 * @throws	\Tuxxedo\Exception\Upload	Throws an uploading exception in case of a failed upload
		 */
		public function __invoke()
		{
			return($this->upload());
		}
	}


	__halt_compiler();

	$upload->addFilter('mimecheck', function(stdClass $file)
	{
		static $allowed_types;

		if(!$allowed_types)
		{
			$allowed_types = Array('image/png', 'image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg');
		}

		return(in_array($file->type, $allowed_types));
	});

	$upload->removeFilter('mimecheck');
?>