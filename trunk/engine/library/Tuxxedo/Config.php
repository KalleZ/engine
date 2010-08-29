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

	namespace Tuxxedo;

    /**
     * A configuration object
     * @package     Engine
     */
    class Config implements \ArrayAccess
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
?>