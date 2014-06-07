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
	use Tuxxedo\Exception;


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
	abstract class Parser
	{
		/**
		 * Interface method - Parse the file and convert it into a tree structure
		 *
		 * @return	\Tuxxedo\Xml\Tree		Returns an XML tree structure
		 *
		 * @throws	\Tuxxedo\Exception\Xml		Throws an XML exception if parsing fails for some reason
		 */
		abstract public function parse($input);

		/**
		 * Factory method, creates a new parser
		 *
		 * @param	string				The parser to use, one of the four default parsers can be used here or a fully qualified class name for a custom parser
		 * @return	\Tuxxedo\Xml\Parser		Returns an XML parser of the desired type
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws a basic exception if the parser cannot be created
		 */
		public static function factory($parser)
		{
			$class = (\strpos($parser, '\\') === false ? '\Tuxxedo\Xml\Parser\\' : '') . \ucfirst($parser);

			if(!\class_exists($class))
			{
				throw new Exception\Basic('Invalid XML parser specified');
			}
			elseif(!\is_subclass_of($class, __CLASS__))
			{
				throw new Exception\Basic('Corrupt XML parser');
			}

			return(new $class);
		}
	}
?>