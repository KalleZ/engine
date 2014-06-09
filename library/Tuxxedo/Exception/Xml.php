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
		 * Exception type constant - LibXML (default)
		 *
		 * @var		integer
		 */
		const TYPE_LIBXML		= 1;

		/**
		 * Exception type constant - EXPAT
		 *
		 * @var		integer
		 */
		const TYPE_EXPAT		= 2;


		/**
		 * The XML parser that generated this exception
		 *
		 * @var		string
		 */
		protected $parser		= '';

		/**
		 * The XML parser type (One of the TYPE_* constants)
		 *
		 * @var		integer
		 */
		protected $type;

		/**
		 * The LibXML error information
		 *
		 * @var		\LibXMLError|\stdClass
		 */
		protected $xml_error;


		/**
		 * Constructs a new XML exception
		 *
		 * @param	string				The XML parser that generated this exception if any
		 * @param	integer				The Exception type, this is one of the TYPE_* constants
		 * @param	mixed				Optionally a resource needed to fetch the error
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception on invalid errors
		 */
		public function __construct($parser = '', $type = self::TYPE_LIBXML, $rsrc = NULL)
		{
			if($parser)
			{
				$this->parser = $parser;
			}

			switch($type)
			{
				case(self::TYPE_LIBXML):
				{	
					$this->xml_error = libxml_get_last_error();

					if(!$this->xml_error)
					{
						throw new Basic('No error could be found from LibXML');
					}

					$this->message	= $this->xml_error->message;
					$this->code	= $this->xml_error->code;
				}
				break;
				case(self::TYPE_EXPAT):
				{
					$this->xml_error 		= new \stdClass;
					$this->xml_error->level		= false;
					$this->xml_error->column	= xml_get_current_column_number($rsrc);
					$this->xml_error->line		= xml_get_current_line_number($rsrc);

					$this->message			= xml_error_string($this->code = xml_get_error_code($rsrc));
				}
				break;
				default:
				{
					throw new Basic('Invalid XML error type');
				}
				break;
			}

			$this->type = (integer) $type;
		}

		/**
		 * Gets the backend library used for parsing for this parser
		 *
		 * @param	boolean				Whether or not to return a name instead of the constant type
		 * @return	integer|string			Returns either one of the TYPE_* constants or the textural name for that type
		 */
		public function getType($textural = false)
		{
			static $conv;

			if($textural)
			{
				if(!$conv)
				{
					$conv = [
							self::TYPE_LIBXML 	=> 'LibXML', 
							self::TYPE_EXPAT 	=> 'EXPAT'
							];
				}

				return($conv[$this->type]);
			}

			return($this->type);
		}

		/**
		 * Gets the parser if any that generated this exception
		 *
		 * @return	string				Returns the parser name or empty string if none
		 */
		public function getParser()
		{
			return($this->parser);
		}

		/**
		 * Gets the level (depth) of where the parsing error occured
		 * 
		 * @return	integer				Returns the current depth, the return value of this is always false when using EXPAT
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