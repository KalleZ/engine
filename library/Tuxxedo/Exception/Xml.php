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


	/**
	 * Exception namespace, this contains all the core exceptions defined within 
	 * the library.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Exception;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * XML Exception
	 *
	 * Exception designed to carry error information from a failed 
	 * XML parsing attempt.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @since		1.2.0
	 */
	class Xml extends Basic
	{
		/**
		 * The XML parser that generated this exception
		 *
		 * @var		string
		 */
		protected $parser		= '';

		/**
		 * The LibXML error information
		 *
		 * @var		\LibXMLError
		 */
		protected $xml_error;


		/**
		 * Constructs a new XML exception
		 *
		 * @param	string				The XML parser that generated this exception
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception on invalid errors
		 */
		public function __construct($parser)
		{
			if(!($this->xml_error = \libxml_get_last_error()))
			{
				throw new Basic('LibXML did not return any error');
			}

			$this->parser 	= $parser;
			$this->message	= &$this->xml_error->message;
			$this->code	= &$this->xml_error->code;
		}

		/**
		 * Gets the parser if any that generated this exception
		 *
		 * @return	string				Returns the parser name
		 */
		public function getParser()
		{
			return($this->parser);
		}

		/**
		 * Gets the level (depth) of where the parsing error occured
		 * 
		 * @return	integer				Returns the current level
		 */
		public function getLevel()
		{
			return($this->xml_error->level);
		}

		/**
		 * Gets the column of where the parsing error occured
		 *
		 * @return	integer				Returns the column
		 */
		public function getColumn()
		{
			return($this->xml_error->column);
		}

		/**
		 * Gets the line of where the error occured
		 *
		 * @return	integer				Returns the line
		 */
		public function getXmlLine()
		{
			return($this->xml_error->line);
		}
	}
?>