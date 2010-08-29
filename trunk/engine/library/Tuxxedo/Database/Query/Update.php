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

	namespace Tuxxedo\Database\Query;
	
	//UPDATE tbl SET col=expr [, col=expr] WHERE where ORDER BY col ASC|DESC LIMIT row_count
	abstract class Tuxxedo_Query_Update extends Tuxxedo_Query
	{
	    abstract public function __construct($tableName);
	    abstract public function set(array $values);
	    abstract public function where(array $fields);
	    abstract public function order(array $fields);
	    abstract public function limit($count);
	}
?>