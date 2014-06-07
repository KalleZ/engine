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
	namespace Tuxxedo\Xml;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Design;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;

	
	/**
	 * XML Tree structure, this is the common access interface for the parsed XML data 
	 * returned by all parsers.
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.2.0
	 */
	class Tree extends Design\InfoAccess
	{
		/**
		 * Holds the node name
		 *
		 * @var		string
		 */
		protected $name			= '';

		/**
		 * Holds the node value
		 *
		 * @var		string
		 */
		protected $value		= '';

		/**
		 * Holds the child nodes
		 *
		 * @var		array
		 */
		protected $nodes		= [];


		/**
		 * Constructs a new tree object
		 *
		 * @param	string				The name of this node
		 * @param	string				The value of this node if any, use an empty string for none
		 * @param	array				List of attributes for this node, use NULL for none
		 * @param	array				List of the child nodes for this node, use NULL for none
		 */
		public function __construct($name, $value = '', Array $attributes = NULL, Array $nodes = NULL)
		{
			$this->name 	= $name;
			$this->value	= (string) $value;

			if($attributes)
			{
				$this->information = $attributes;
			}

			if($nodes)
			{
				$this->nodes = $nodes;
			}
		}

		/**
		 * Gets the node name
		 *
		 * @return	string				Returns the current node name
		 */
		public function getName()
		{
			return($this->name);
		}

		/**
		 * Gets the value of the node
		 *
		 * @return	string				Returns the value of this node if any
		 */
		public function getValue()
		{
			return($this->value);
		}

		/**
		 * Gets a node based on its name
		 *
		 * @param	string				The name of the node
		 * @return	mixed				May return an array or a tree structure whether or not there were multiple nodes with this name, and false on error
		 */
		public function getNode($name)
		{
			if(isset($this->nodes[$name]))
			{
				return($this->nodes[$name]);
			}

			return(false);
		}

		/**
		 * Converts the current node and its children to raw XML
		 *
		 * @param	boolean				Whether or not to add the XML PI tag
		 * @return	string				Returns the generated XML
		 */
		public function asXML($use_pi = true)
		{
			$xml = ($use_pi ? '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL : '');
			$xml .= '<' . $this->name . $this->doXmlConvertAttr($this->information) . '>' . PHP_EOL;

			if($this->nodes)
			{
				foreach($this->nodes as $node)
				{
					if(!\is_array($node))
					{
						$node = [$node];
					}

					foreach($node as $n)
					{		
						$xml .= $this->doXmlConvert($n, 1);
					}
				}
			}

			if(!empty($this->value))
			{
				$xml .= "\t" . $this->value . PHP_EOL;
			}

			return($xml . '</' . $this->name . '>');
		}

		/**
		 * Tree to XML converter recurive method
		 *
		 * @param	\Tuxxedo\Xml\Tree		The tree node to convert
		 * @param	integer				The current depth
		 * @return	string				Returns the XML value of this node
		 *
		 * @todo	Implement
		 */
		protected function doXmlConvert(Tree $node, $depth)
		{
		}

		/**
		 * Tree to XML converter for attributes method
		 *
		 * @param	array				The attributes to convert
		 * @return	string				Returns the attributes for generation of a node tag
		 */
		protected function doXmlConvertAttr(Array $attributes)
		{
			$attr = '';

			if($attributes)
			{
				foreach($attributes as $name => $value)
				{
					$attr .= ' ' . $name . '="' . \str_replace('"', '\"', $value) . '"';
				}
			}

			return($attr);
		}

		/**
		 * Gets the value of this node if converted to a string
		 *
		 * @return	string				Returns the value of this node if any
		 */
		public function __toString()
		{
			return($this->value);
		}

		/**
		 * Overloads the get property handler to allow direct access of child nodes
		 *
		 * @param	string				The property name
		 * @return	\Tuxxedo\Xml\Tree		Returns a tree structure for valid nodes, otherwise false
		 */
		public function __get($property)
		{
			if(isset($this->nodes[$property]))
			{
				return($this->nodes[$property]);
			}
		}
	}
?>