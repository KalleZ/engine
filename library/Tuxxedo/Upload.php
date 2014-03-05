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
	 *  - url		Uploads from a HTTP URL
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
		 * Event caller trait
		 */
		use Design\EventCaller;


		/**
		 * The event handler
		 *
		 * @var			\Tuxxedo\Design\EventHandler
		 */
		protected $event_handler;

		/**
		 * Upload queue
		 *
		 * @var			array
		 */
		protected $queue			= [];

		/**
		 * List of valid backends
		 *
		 * @var			array
		 */
		protected static $valid_backends	= [
								'post'		=> true, 
								'url'		=> true
								];


		/**
		 * Constructs a new upload object
		 */
		public function __construct()
		{
			$this->setEventHandler($this->event_handler = new Design\EventHandler($this, ['preprocess', 'postprocess']));

			$this->information = [
						'size_limit'		=> 10485760, 
						'directory'		=> './', 
						'resolve_mime'		=> true, 
						'allow_override'	=> false
						];
		}

		/**
		 * Queues a new object for upload
		 *
		 * @param	string				The protocol to use as backend for this upload transfer ('post' for HTML forms, 'url' for URLs)
		 * @param	string				The field name (<input type="file" name="XXX" /> name for 'post', 'http://www.domain.tld/file.ext' for 'url')
		 * @param	string				Optionally the file name the file should have, pass NULL to retain the original filename
		 * @param	string				Optionally the extension the file should have (for example: 'jpg'), pass NULL to retain the original extension
		 * @param	string				Optionally the name identifier if this item might be unqueued at a later point
		 * @return	void				No value is returned
		 */
		public function queue($backend, $input, $filename = NULL, $extension = NULL, $name = NULL)
		{
			$backend = \strtolower($backend);

			if(!isset(self::$valid_backends[$backend]))
			{
				return;
			}

			$struct = [
					$backend, 
					$input, 
					$filename, 
					$extension
					];

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
				$handlers 	= [];
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

			$status = [];

			foreach($this->queue as $index => $obj)
			{
				$status[$index] = $factory($obj[0])->process($obj[1], $obj[2], $obj[3]);
			}

			return($status);
		}

		/**
		 * __invoke() alias for invoking the uploading process
		 *
		 * @return	array				Returns an array with a list of status codes, false in case of a general failure
		 */
		public function __invoke()
		{
			return($this->upload());
		}
	}
?>