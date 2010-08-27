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
	
	namespace Tuxxedo\Request;
	
	/**
	 * Generates a HTTP request
	 */
	class Tuxxedo_Request_Http extends Tuxxedo_Request
	{
	    protected static $validMethods = array("GET", "POST", "PUT", 
	                                               "DELETE", "HEAD", "OPTIONS",
	                                               "TRACE", "CONNECT");
	    protected $method;
	    protected $resource;
	    protected $headers = array();
	    protected $getVars = array();
	    protected $postVars = array();
	    
	    public function setVars(array $vars = $_GET) {
	        $this->getVars = $vars;
	    }
	    
	    public function getVars() {
	        return $this->getVars;
	    }
	    
	    public function setPost(array $vars = $_POST) {
	        $this->postVars = $vars;
	    }
	    
	    public function getPost() {
	        return $this->postVars;
	    }
	    
	    public function setHeaders(array $headers) {
	        $this->headers = $headers;
	    }
	    
	    public function addHeader($name, $value) {
	        $this->headers[$name] = $value;
	    }
	    
	    public function getHeaders() {
	        return $this->headers;
	    }
	    
	    /**
	     * Output a HTTP request, conforming to RFC 2616 (sect 5)
	     * @return  string  HTTP request
	     */
	    public function __toString() {
	        $this->method = strtoupper($this->method);
	        $output = $this->method . " " . $this->resource . 
	                  " HTTP/1.1\r\n";
	        
	        foreach ($this->headers as $name => $value) {
	            // For headers with multiple values the values are passed as an
	            // array, so we need to format the value part.
	            // For example: Content-type: text/html; charset=utf-8 would be
	            // array("text/html", "charset" => "utf-8")
	            if (is_array($value)) {
	                $valStr = "";
	                
	                foreach ($value as $key => $val) {
	                    if (is_int($key)) {
    	                    $valStr .= "$val";
    	                } else {
    	                    $valStr .= "$key=$val";
    	                }
    	                
        	            $valStr .= "; ";
	                }
	                
	                $value = trim($valStr, "; ");
	            }
	            
	            $output .= "$name: $value\r\n";
	        }
	    }
	}
