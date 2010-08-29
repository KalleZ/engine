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

	namespace Tuxxedo\Config;

    /**
     * Load configuration options from an XML file
     * @package     Engine
     * @subpackage  Config
     */
    class Xml extends \Tuxxedo\Config
    {
        /**
         * Parse the input given. If inputAsPath is true input is a path to an
         * XML file, otherwise it is raw XML.
         * @param   string  A path to an XML file or an XML string.
         * @param   bool    Whether to interpret input as a path or XML.
         */
        public function parse($input, $inputAsPath = true) {
            if ($inputAsPath) {
                $input = file_get_contents($input);
            }
            
            $xml = new SimpleXmlElement($input);

            $this->options = $this->xmlToArray($xml);
        }
        
        /**
         * Recursively convert SimpleXmlElements with children to an array of the
         * children's values.
         * @param   SimpleXmlElement    Input element (with children)
         * @return  array
         */
        protected function xmlToArray(SimpleXmlElement $element) {
            $array = array();
        
            foreach ($element->children() as $child) {
                $key = $child->getName();
            
                if (count($child->children()) != 0) {
                    $child = $this->xmlToArray($child);
                } else {
                    // Attempt to convert some string values to data types
                    if (strtolower($child) == "false" || strtolower($child) == "true") {
                        $child = (bool) $child;
                    } elseif (is_numeric((string) $child)) {
                        $child = (int) $child;
                    } else {
                        $child = (string) $child;
                    }
                }
                
                $array[$key] = $child;
            }
            
            return $array;
        }
    }
?>