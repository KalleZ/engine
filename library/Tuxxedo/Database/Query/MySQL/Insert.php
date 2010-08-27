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
	
    class Insert extends \Tuxxedo\Database\Query\Insert
	{
	    public function __construct($tableName) {
	        $this->pattern = "INSERT INTO {table} {values}";
	        
	        $this->clauses["table"] = $tableName;
	    }
	    
	    // associative array of fields => values
	    public function values(array $values) {
	        $clause = "SET ";
	        
	        $i = 0;
	        $count = count($values) - 1;
	        foreach ($values as $field => $value) {
	            $clause .= "$field = $value";
	            
	            if ($i < $count) {
	                $clause .= ", ";
	            }
	            $i++;
	        }
	    }
	}
