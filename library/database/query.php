<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Ross Masters <ross@php.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;
	
	/**
	 * Base query class
	 * @todo    Escaping
	 * @todo    Optional/required clauses
	 */
	abstract class Tuxxedo_Query
	{
	    protected $pattern;
	    protected $clauses = array();
	    
	    /**
	     * Generate the SQL from the pattern and clauses
	     */
	    public function __toString() {
	        preg_match_all("/{([A-Za-z0-9_-]+)}/", $this->pattern, $placeholders);
	        
	        foreach ($placeholders[1] as $index => $placeholder) {
	            $match = $placeholders[0][$index];
	            
	            if (isset($this->clauses[$placeholder])) {
	                $this->pattern = str_replace($match, $this->clauses[$placeholder], $this->pattern);
	            } else {
    	            $this->pattern = str_replace($match, "", $this->pattern);
	            }
	        }
	        
	        return $this->pattern;
	    }
	}
	
	abstract class Tuxxedo_Query_Select
	{
	    abstract public function __construct(array $fields = null);
	    abstract public function from($tableName);
	    abstract public function where(array $fields);
	    abstract public function order(array $fields);
	    abstract public function limit($count, $offset = null);
	}
		
	abstract class Tuxxedo_Query_Insert extends Tuxxedo_Query
	{
	    abstract public function __construct($tableName);
	    abstract public function values(array $values);
	}
	
	//UPDATE tbl SET col=expr [, col=expr] WHERE where ORDER BY col ASC|DESC LIMIT row_count
	abstract class Tuxxedo_Query_Update extends Tuxxedo_Query
	{
	    abstract public function __construct($tableName);
	    abstract public function set(array $values);
	    abstract public function where(array $fields);
	    abstract public function order(array $fields);
	    abstract public function limit($count);
	}
	
	//DELETE FROM tbl WHERE where ORDER BY col ASC|DESC LIMIT row_count
	abstract class Tuxxedo_Query_Delete extends Tuxxedo_Query
	{
	    abstract public function __construct($tableName);
	    abstract public function where(array $fields);
	    abstract public function order(array $fields);
	    abstract public function limit($count);
	}
