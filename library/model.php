<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Ross Masters <ross@php.net>
	 * @version		1.0
	 * @copyright	Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */
    
    /**
     * Model class
     * In a MVC application this would be extended to create logical models,
     * that are populated with data from another source (e.g. a datamanager).
     *
     * This class implements a few methods that make writing models less
     * tedious, without having to write the same methods for each model.
     * @package     Engine
     */
    class Tuxxedo_Model
    {
        /**
         * Set an array of properties (without having to set each one 
         * individually).
         * @param   array   An associative array of properties to set
         */
        public function setOptions(Array $options) {
            foreach ($options as $property => $value) {
                $this->set($property, $value);
            }
        }
        
        /**
         * Overloaded getter method
         * @param   string  Property name
         * @return  mixed   Property value
         */
        public function __get($property) {
            return $this->get($property);
        }
        
        /**
         * Overloaded setter method
         * @param   string  Property name
         * @param   mixed   Property value
         */
        public function __set($property, $value) {
            $this->set($property, $value);
        }
        
        /**
         * Overloaded caller method
         * @param   string  Method called
         * @param   array   Method arguments
         * @return  mixed|void  If this is a getter method then the property's 
         *                      value is returned
         * @throws  Tuxxedo_Exception_Basic If the method is not a getter/setter
         */
        public function __call($method, $arguments) {
            $prefix = substr($method, 0, 3);
            $property = substr($method, 3);
            
            if ($prefix == "set") {
                $this->set($property, $arguments[0]);
            } elseif ($prefix == "get") {
                $this->get($property);
            } else {
                throw new Tuxxedo_Exception_Basic("Undefined method '$method' called.");
            }
        }
        
        /**
         * Core set method, checks for a model setter method or a property
         * @throws  Tuxxedo_Exception_Basic If no method or property is found
         */
        private function set($property, $value) {
            $method = "set" . ucfirst($property);
            
            if (method_exists($this, $method)) {
                $this->$method($value);
            } elseif (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                throw new Tuxxedo_Exception_Basic("Invalid property given.");
            }
        }
        
        /**
         * Core get method, checks for a model setter method or a property
         * @return  mixed   Value of property
         * @throws  Tuxxedo_Exception_Basic If no method or property is found
         */
        private function get($property) {
            $method = "get" . ucfirst($property);
            
            if (method_exists($this, $method)) {
                return $this->$method();
            } elseif (property_exists($this, $property)) {
                return $this->$property;
            } else {
                throw new Tuxxedo_Exception_Basic("Invalid property given.");
            }
        }
    }
