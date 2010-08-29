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

	namespace Tuxxedo\Database\Query\MySQL;
	use Tuxxedo\Exception;
	
	/**
	 * @todo    Escaping
	 * @todo    HAVING
	 * @todo    GROUP BY
	 * @todo    Sub-statements (as a field), functions (as a field)
	 */
	class Select extends \Tuxxedo\Database\Query\Select
	{
	    public function __construct(array $fields = null) {
	        $this->pattern = "SELECT {fields} {from} {group} {having} {where} {order} {limit}";
	    
	        if (is_array($fields) && count($fields) != 0) {
	            
	            $i = 0;
	            $count = count($fields) - 1;
	            foreach ($fields as $alias => $field) {
	                if (!is_int($alias)) {
	                    $clause .= "$field as $alias";
	                } else {
    	                $clause .= "$field";
    	            }
    	            
    	            if ($i != $count) {
    	                $clause .= ", ";
    	            }
    	            
    	            $i++;
	            }
	            
	            $this->clauses["fields"] = $clause;
	        } elseif (is_string($fields)) {
	            $this->clauses["fields"] = $fields;
	        } else {
	            $this->clauses["fields"] = "*";
	        }
    	    
    	    return $this;
	    }	
	
	    public function from($tableName) {
	        $this->clauses["from"] = "FROM $tableName";
	    }
	
	    public function where(array $fields) {
	        $clause = "WHERE ";
	        
	        $i = 0;
	        $count = count($fields) - 1;
	        
	        foreach ($fields as $field => $value) {
	            $clause .= $field . " = " . $value;
	            
	            if ($i < $count) {
	                $clause .= " AND ";
	            }
	            
	            $i++;
	        }
	        
	        $this->clauses["where"] = $clause;
	        
	        return $this;
	    }
	    
	    public function order(array $fields) {	        
	        $clause = "ORDER BY ";
	        
	        $i = 0;
	        $count = count($fields) - 1;
	        foreach ($fields as $index => $value) {
	            if (is_int($index)) {
	                // If the index is an integer then the value is the column
	                // Order by the DBMS's default ordering direction
	                $clause .= "$value";
	            } else {
	                // Otherwise the key is a column and the value is the direction
	                $value = strtoupper($value);
	                
	                if ($value != "ASC" && $value != "DESC") {
	                    throw new Exception\Basic("Direction must be ASC or DESC.");
	                }
	                
	                $clause .= "$index $value";
	            }
	            
	            if ($i < $count) {
	                $clause .= ", ";
	            }
	        }
	        
	        $this->clauses["order"] = $clause;
	        
	        return $this;
	    }
	    
	    public function limit($count, $offset = null) {
	        $clause = "LIMIT ";
	        
	        if($offset == null) {
	            $clause .= "$count";
	        } else {
	            $clause .= "$offset, $count";
	        }
	        
	        $this->clauses["limit"] = $clause;
	        
	        return $this;
	    }
	}
?>