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

	namespace Tuxxedo\Style\Storage;
	use Tuxxedo\Registry;
	use Tuxxedo\Exception;
	use Tuxxedo\Style;

	/**
	 * Style storage engine for database based templates
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Database extends Style\Storage
	{
		/**
		 * Constructs a new storage engine
		 *
	 	 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	Tuxxedo_Style		Reference to the style object
		 * @param	object			Object reference to the templates data table
		 */
		protected function __construct(Registry $registry, Style $style, \stdClass $templates)
		{
			$this->registry 	= $registry;
			$this->templates	= $templates;
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array			A list of templates to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @param	array			An array passed by reference, this contains all the elements that where loaded if referenced
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			if(!\sizeof($templates))
			{
				return(false);
			}

			$result = $this->registry->db->query('
								SELECT 
									`title`, 
									`compiledsource` 
								FROM 
									`' . \TUXXEDO_PREFIX . 'templates` 
								WHERE 
										`styleid` = %d 
									AND 
										`title` IN (
											\'%s\'
										);', 
								$this->tuxxedo->style['id'], \join('\', \'', \array_map(Array($this->tuxxedo->db, 'escape'), $templates)));

			if($result === false || !$result->getNumRows())
			{
				if($error_buffer !== NULL)
				{
					$error_buffer = $templates;
				}

				return(false);
			}

			while($row = $result->fetchAssoc())
			{
				$this->templates->{$row['title']} = $row['compiledsource'];
			}

			return(true);
		}
	}
?>