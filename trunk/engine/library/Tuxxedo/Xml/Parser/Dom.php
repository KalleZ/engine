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
	 * DOM parser interface.
	 *
	 * This interface extends the SimpleXML parser as it will use that for 
	 * node convertion in case that this is manually set as an internal 
	 * parser and SimpleXML is available.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Dom extends Xml\Parser\Simplexml
	{
		/**
		 * Constructs a new DOM parser object
		 *
		 * @throws	\Tuxxedo\Exception\Basic		Throws a basic exception if the DOM extension is not available
		 */
		public function __construct()
		{
			if(!\extension_loaded('DOM'))
			{
				throw new Exception\Basic('Cannot initalize the DOM parser, extension is not available');
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
			static $have_simplexml;

			if($have_simplexml === NULL)
			{
				$have_simplexml = \extension_loaded('simplexml');
			}

			\libxml_use_internal_errors(true);

			$dom = new \DomDocument;

			if(@$dom->loadXML($input) === false)
			{
				throw new Exception\Xml('DOM');
			}

			if($have_simplexml && ($sxe = @\simplexml_import_dom($dom)) !== false)
			{
				return($this->doConvertNode($sxe));
			}

			return($this->doConvertDOMNode($dom->documentElement, true));
		}

		/**
		 * Converts a node to a tree recursively
		 *
		 * @param	\DOMNode				The DOMNode node object
		 * @param	boolean					Whether or not this is the root element, defaults to false
		 * @return	\Tuxxedo\Xml\Tree			Returns a tree structure
		 */
		protected function doConvertDOMNode(\DOMNode $node, $root = false)
		{
			$attrs = NULL;

			if($node->attributes && $node->attributes->length)
			{
				$attrs = [];

				for($x = 0; $x < $node->attributes->length; ++$x)
				{
					$attr 			= $node->attributes->item($x);
					$attrs[$attr->name] 	= $attr->value;
				}
			}

			$value = '';
			$nnodes = NULL;

			if($node->childNodes && $node->childNodes->length)
			{
				$nodes = [];

				for($x = 0; $x < $node->childNodes->length; ++$x)
				{
					$child = $node->childNodes->item($x);

					if($child->nodeName == '#text')
					{
						$value = $child->nodeValue;

						if(!$root)
						{
							return($value);
						}

						continue;
					}

					if(!isset($nodes[$child->nodeName]))
					{
						$nodes[$child->nodeName] = [];
					}

					$nodes[$child->nodeName][] = $this->doConvertDOMNode($child);
				}

				$nnodes = [];

				foreach($nodes as $n => $nptr)
				{
					$nnodes[$n] = (\sizeof($nptr) == 1) ? $nptr[0] : $nptr;
				}
			}

			return(new Xml\Tree((!isset($node->tagName) ? $node->parentNode->tagName : $node->tagName), $value, $attrs, $nnodes));
		}
	}
?>