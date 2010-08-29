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

	namespace Tuxxedo\Database;
	
	/**
	 * Base query class
	 * @todo    Escaping
	 * @todo    Optional/required clauses
	 */
	abstract class Query
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
?>