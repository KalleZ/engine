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
	use Tuxxedo\Debug;
	use Tuxxedo\Exception;
	use Tuxxedo\Xml\Parser;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

	
	/**
	 * XML Parser for Engine, this class works as a factory for a generic 
	 * xml parsing. By default the internal parser will use the following 
	 * fallbacks:
	 *
	 *   > simplexml
	 *   > dom
	 *
	 * A selected parser can be choosen if desired using the setInternalParser() 
	 * method. The returned XML will be returned in a Tree structure, which is 
	 * common for all backends.
	 *
	 * Parsers may change the internal values of the LibXML configuration during 
	 * parsing to either get better debuggable information or other reasons.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Xml
	{
		/**
		 * Debugable trait
		 */
		use Debug\Debugable;


		/**
		 * Parser constant - SimpleXML
		 *
	 	 * @var		integer
		 */
		const PARSER_SIMPLEXML		= 1;

		/**
		 * Parser constant - DOM
		 *
		 * @var		integer
		 */
		const PARSER_DOM		= 2;


		/**
		 * Internal parser to use
		 *
		 * @var		integer
		 */
		protected $internal_parser		= self::PARSER_SIMPLEXML;

		/**
		 * Internal XML pointer reference
		 *
		 * @var		array
		 */
		protected $ref				= [
								'ptr'	=> NULL, 
								'type'	=> NULL
								];


		/**
		 * Constructs a new XML parser, this will auto determine the parser to use 
		 * unless disabled, if disabled then the setInternalParser() method must be 
		 * called manually.
		 *
		 * @param	boolean				Whether or not to disable the auto check (defaults to off)
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if no parser could be found
		 */
		public function __construct($disable_autocheck = false)
		{
			if(!$disable_autocheck)
			{
				$this->setInternalParser();
			}
		}

		/**
		 * Gets the current state of this object for debugging purposes
		 *
		 * @return	array			Returns an assosicated array. Keys may differ per object and values are scalar
		 */
		public function getDebugState()
		{
			static $engines_table;

			if(!$engines_table)
			{
				$engines_table = [
							self::PARSER_SIMPLEXML	=> 'simplexml', 
							self::PARSER_DOM	=> 'dom'
							];
			}

			return([
				'current_engine'	=> ($this->ref['type'] ? $this->ref['type'] : false), 
				'internal_parser'	=> $engines_table[$this->internal_parser]
				]);
		}

		/**
		 * Internal factory method, this creates and returns a new XML parser object 
		 * based on the internal parser value set.
		 *
		 * @return	\Tuxxedo\Xml\Parser		Returns a new XML parser to use
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if no parser is set
		 */
		protected function factory()
		{
			static $refs;

			if(!\is_array($refs))
			{
				$refs 	= [
						self::PARSER_SIMPLEXML	=> 'Simplexml', 
						self::PARSER_DOM	=> 'Dom'
						];
			}

			if(!$this->internal_parser)
			{
				throw new Exception\Basic('No XML parser defined');
			}

			if($this->ref['type'] != $this->internal_parser)
			{
				$this->ref['ptr']	= Parser::factory($refs[$this->internal_parser]);
				$this->ref['type']	= $this->internal_parser;
			}

			return($this->ref['ptr']);
		}

		/**
		 * Sets the internal parser
		 *
		 * @param	integer				If no parameter is passed, then a new parser is auto detected, otherwise supply one of the PARSER_* class constants
		 * @return	void				No value is returned
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if no parser could be found
		 */
		public function setInternalParser($parser = NULL)
		{
			if(!$parser)
			{
				if(\extension_loaded('simplexml'))
				{
					$this->internal_parser = self::PARSER_SIMPLEXML;

					return;
				}
				elseif(\extension_loaded('dom'))
				{
					$this->internal_parser = self::PARSER_DOM;

					return;
				}

				throw new Exception\Basic('Unable to find a suitable parser backend');
			}

			if($parser > 0 && $parser < 3)
			{
				$this->internal_parser = (integer) $parser;

				return;
			}

			throw new Exception\Basic('Unable to set parser backend, invalid type passed');
		}

		/**
		 * Parses a file
		 *
		 * @param	string				The XML file to parse
		 * @return	\Tuxxedo\Xml\Tree		Returns the parsed XML as a tree object
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the content from the file could not be obtained or if the parser could not be created
		 * @throws	\Tuxxedo\Exception\Xml		Throws an XML exception if XML parsing fails for some reason
		 */
		public function parseFile($file)
		{
			$xml = @\file_get_contents($file);

			if(!$xml)
			{
				throw new Exception\Basic('Unable to fetch contents of the XML file');
			}

			if($this->ref['ptr'] && $this->ref['type'] == $this->internal_parser)
			{
				return($this->ref['ptr']->parse($xml));
			}

			return($this->factory()->parse($xml));
		}

		/**
		 * Parses a string
		 *
		 * @param	string				The XML string to parse
		 * @return	\Tuxxedo\Xml\Tree		Returns the parsed XML as a tree object
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the parser could not be created
		 * @throws	\Tuxxedo\Exception\Xml		Throws an XML exception if XML parsing fails for some reason
		 */
		public function parseString($string)
		{
			if($this->ref['ptr'] && $this->ref['type'] == $this->internal_parser)
			{
				return($this->ref['ptr']->parse($string));
			}

			return($this->factory()->parse($string));
		}

		/**
		 * Parses a stream
		 *
		 * This will internally reset the internal file pointer to position 0 
		 * and start reading from there.
		 *
		 * @param	resource			The XML stream to parse, this must be a stream compatible resource
		 * @return	\Tuxxedo\Xml\Tree		Returns the parsed XML as a tree object
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if no data could be read or if the stream is invalid or if the parser could not be created
		 * @throws	\Tuxxedo\Exception\Xml		Throws an XML exception if XML parsing fails for some reason
		 */
		public function parseStream($stream)
		{
			if(!\is_resource($stream))
			{
				throw new Exception\Basic('Invalid stream supplied');
			}

			\fseek($stream, 0);

			$xml = '';

			while(!\feof($stream))
			{
				$xml .= \fread($stream, 4096);
			}

			if($this->ref['ptr'] && $this->ref['type'] == $this->internal_parser)
			{
				return($this->ref['ptr']->parse($xml));
			}

			return($this->factory()->parse($xml));
		}
	}
?>