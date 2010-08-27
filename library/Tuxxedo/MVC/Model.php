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
    
    namespace Tuxxedo\MVC;
    use \Tuxxedo\Exception;
    
    /**
     * Model class
     * In a MVC application this would be extended to create logical models,
     * that are populated with data from another source (e.g. a datamanager).
     *
     * This class implements a few methods that make writing models less
     * tedious, without having to write the same methods for each model.
     * @package     Engine
     */
    class Model
    {
        protected $methods;
        
        /**
         * Constructor, creates a new model from a set of properties
         * @param   array|null  An associative array of properties to set
         */
        public function __construct(array $properties = null) {
            $this->initMapper();
        
            if ($properties) {
                $this->setOptions($properties);
            }
        }
        
        /**
         * If a getMapper method is defined, we can use it to allow access to
         * the mapper's find, fetchAll, save etc. methods in the model class
         */
        protected function initMapper() {
            if (method_exists($this, "getMapper")) {
                $mapper = $this->getMapper();
                
                // Get the public methods of the mapper
                $this->methods = get_class_methods($mapper);
            }
        }
        
        /**
         * Set an array of properties (without having to set each one 
         * individually).
         * @param   array   An associative array of properties to set
         */
        public function setOptions(array $options) {
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
            // Mapper methods
            if (count($this->methods) != 0) {
                if (in_array($method, $this->methods)) {
                    $mapper = $this->getMapper();
                    
                    return call_user_func_array(array($mapper, $method), $arguments);
                }
            }
        
            // Getter/setter methods
            $prefix = substr($method, 0, 3);
            $property = substr($method, 3);
            
            if ($prefix == "set") {
                $this->set($property, $arguments[0]);
            } elseif ($prefix == "get") {
                $this->get($property);
            } else {
                throw new Exception\Basic("Undefined method '$method' called.");
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
                echo ": exception\n";
                throw new Exception\Basic("Invalid property given.");
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
                throw new Exception\Basic("Invalid property given.");
            }
        }
    }
