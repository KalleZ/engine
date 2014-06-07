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
	 * XML Parser that works as an interface for all the parsers, this also contains 
	 * the ability to load up any parser.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Simplexml extends Xml\Parser
	{
		/**
		 * Constructs a new SimpleXML parser object
		 *
		 * @throws	\Tuxxedo\Exception\Basic		Throws a basic exception if the simplexml extension is not available
		 */
		public function __construct()
		{
			if(!\extension_loaded('simplexml'))
			{
				throw new Exception\Basic('Cannot initalize the SimpleXML parser, extension is not available');
			}
		}

		/**
		 * Parses an XML string
		 *
		 * @param	string					The XML to parse
		 * @reutrn	\Tuxxedo\Xml\Tree			Returns an XML tree of the parsed XML
		 *
		 * @throws	\Tuxxedo\Exception\Xml			Throws an XML exception if parsing fails for some reason
		 */
		public function parse($input)
		{
			libxml_use_internal_errors(true);

			$sxe = @\simplexml_load_string($input);

			if($sxe === false)
			{
				throw new Exception\Xml('simplexml');
			}

			return($this->doConvertNode($sxe));
		}

		/**
		 * Converts a node to a tree recursively
		 *
		 * @param	\SimpleXMLElement			The SimpleXMLElement node object
		 * @return	\Tuxxedo\Xml\Tree			Returns a tree structure
		 */
		protected function doConvertNode(\SimpleXMLElement $node)
		{
			$attr = (array) $node->attributes();

			if(isset($attr['@attributes']))
			{
				$attributes = $attr['@attributes'];
			}

			$nodes 	= [];
			$len	= \sizeof((array) $node->children());

			if($len)
			{
				foreach($node as $name => $nptr)
				{
					if(!isset($nodes[$name]))
					{
						$nodes[$name] = [];
					}

					$nodes[$name][] = $this->doConvertNode($nptr);
				}
			}
			else
			{
				return((string) $node);
			}

			$nnodes = [];

			foreach($nodes as $n => $nptr)
			{
				$nnodes[$n] = (\sizeof($nptr) == 1) ? $nptr[0] : $nptr;
			}

			return(new Xml\Tree($node->getName(), (string) $node, (isset($attributes) ? $attributes : NULL), $nnodes));
		}
	}
?>