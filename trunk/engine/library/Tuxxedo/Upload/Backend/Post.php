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
		 * Constructor
		 *
		 * @param	\Tuxxedo\Upload			The upload handle that initiated this backend
		 */
		public function __construct(Upload $handle)
		{
			$this->handle = $handle;
		}

		/**
		 * Tells the backend to process this input and initiate the transfer 
		 *
		 * @param	string				The input specific to this backend
		 * @return	boolean				Returns true if the transfer was a success, otherwise false (failed hooks and the like)
		 */
		public function process($input)
		{
			if(!$_FILES || !isset($_FILES[$input]) || !isset($_FILES[$input]['tmp_name']) && !\is_array($_FILES[$input]['tmp_name']))
			{
				return(false);
			}

			var_dump($this->handle->export());
		}
	}
?>