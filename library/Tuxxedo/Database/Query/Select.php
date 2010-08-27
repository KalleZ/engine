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
	
	abstract class Tuxxedo_Query_Select
	{
	    abstract public function __construct(array $fields = null);
	    abstract public function from($tableName);
	    abstract public function where(array $fields);
	    abstract public function order(array $fields);
	    abstract public function limit($count, $offset = null);
	}
