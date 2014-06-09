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
	 * XML namespace, this namespace contains all the XML parsers, including the 
	 * core structure like the tree that is common for all parsers.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Xml\Parser;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Exception;
	use Tuxxedo\Xml;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

	
	/**
	 * EXPAT parser interface.
	 *
	 * This interface uses the common XML extension in PHP to parse XML data, 
	 * unlike other interfaces this does not use LibXML as a backend and therefore 
	 * exceptions may not express the same sort of information as others.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Expat extends Xml\Parser
	{
		/**
		 * Holds the parser resource instance
		 *
		 * @var		resource
		 */
		protected static $parser;


		/**
		 * Constructs a new EXPAT parser object
		 *
		 * @throws	\Tuxxedo\Exception\Basic		Throws a basic exception if the XML extension is not available
		 */
		public function __construct()
		{
			if(!\extension_loaded('XML'))
			{
				throw new Exception\Basic('Cannot initalize the XML parser, extension is not available');
			}

			if(!self::$parser)
			{
				self::$parser = xml_parser_create();

				xml_set_element_handler(self::$parser, [$this, 'doParseStart'], [$this, 'doParseEnd']);
			}
		}

		/**
		 * Destructs the parser resource
		 */
		public function __destruct()
		{
			if(self::$parser)
			{
				xml_parser_free(self::$parser);
			}
		}

		/**
		 * Parses an XML string
		 *
		 * @param	string					The XML to parse
		 * @reutrn	\Tuxxedo\Xml\Tree			Returns an XML tree of the parsed XML
		 *
		 * @throws	\Tuxxedo\Exception\Xml			Throws an XML exception if parsing fails for some reason
		 *
		 * @todo	Implement
		 */
		public function parse($input)
		{
			if(!self::$parser)
			{
				self::$parser = xml_parser_create();

				xml_set_element_handler(self::$parser, [$this, 'doParseStart'], [$this, 'doParseEnd']);
			}

			if(!xml_parse(self::$parser, $input))
			{
				throw new Exception\Xml('XML', Exception\Xml::TYPE_EXPAT, self::$parser);
			}

			var_dump(self::$parser);
		}
	}
?>