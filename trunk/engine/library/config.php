<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;

    /**
     * A configuration object
     * @package     Engine
     */
    class Tuxxedo_Config implements ArrayAccess
    {
        /**
         * @var     array     The configuration options held
         */
        protected $options;
        
        /**
         * Constructor - create a config object from an array of options
         * @var     array|null      Options to load into the object
         */
        public function __construct(Array $options = null) {
            if ($options) {
                $this->options = $this->setOptions($options);
            } else {
                $this->options = array();
            }
        }
        
        /**
         * Recursively convert an array of properties to an ArrayObject
         * @param   array           An array of properties to convert
         */
        protected function setOptions(array $properties) {            
            $this->options = $properties;
        }
        
        /**
         * Set an option
         * @param   string  Name of the option to set
         * @param   mixed   Value of the option to set
         */
        public function offsetSet($name, $value) {
            $this->options[$name] = $value;
        }
        
        /**
         * Get an option's value
         * @param   string  Name of the option to retrieve
         * @return  mixed   Value of the option
         */
        public function offsetGet($name) {
            return $this->options[$name];
        }
        
        /**
         * Check if an option is set
         * @param   string  Name of the option to check
         * @return  bool
         */
        public function offsetExists($name) {
            return isset($this->options[$name]);
        }
        
        /**
         * Unset an option
         * @param   string  Name of the option to remove
         */
        public function offsetUnset($name) {
            unset($this->options[$name]);
        }        
        
        /**
         * Dump the contents of the configuration to an array
         * @return  array
         */
        public function getOptions() {
            return $this->options;
        }
    }

    /**
     * Load configuration options from an XML file
     * @package     Engine
     * @subpackage  Config
     */
    class Tuxxedo_Config_Xml extends Tuxxedo_Config
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
