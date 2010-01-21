<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Styling API, this enables basic styling frontend for 
	 * caching templates and fetching them for execution.
	 *
	 * To compile templates thats loadable through this class 
	 * you should look at the {@link Tuxxedo_Template_Compiler} 
	 * class.
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 */
	class Tuxxedo_Style extends Tuxxedo_InfoAccess
	{
		/**
		 * Private instance to the Tuxxedo registry
		 *
		 * @var		Tuxxedo
		 */
		protected $tuxxedo;

		/**
		 * Holds the current loaded templates
		 *
		 * @var		array
		 */
		protected $templates	= Array();


		/**
		 * Constructs a new style object
		 *
		 * @param	array			The style data to use
		 */
		public function __construct(Array $styleinfo)
		{
			$this->tuxxedo		= Tuxxedo::init();
			$this->information 	= $styleinfo;
		}

		/**
		 * Magic method called when creating a new instance of the 
		 * object from the registry
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			The configuration array
		 * @param	array			The options array
		 * @return	object			Object instance
		 *
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if an invalid (or not cached) style id was used
		 */
		public static function invoke(Tuxxedo $tuxxedo, Array $configuration = NULL, Array $options = NULL)
		{
			$styledata 	= $tuxxedo->cache->styleinfo;
			$styleid	= ($options ? (!empty($tuxxedo->userinfo->id) && $tuxxedo->userinfo->style_id != $options['style_id'] ? $tuxxedo->userinfo->style_id : $options['style_id']) : 0);

			if($styleid && isset($styledata[$styleid]))
			{
				return(new self($styledata[$styleid]));
			}

			throw new Tuxxedo_Basic_Exception('Invalid style id, try rebuild the datastore or use the repair tools');
		}

		/**
		 * Caches a template, trying to cache an already loaded 
		 * template will recache it
		 *
		 * @param	array			A list of templates to load
		 * @param	array			An array passed by reference, if one or more elements should happen not to be loaded, then this array will contain the names of those elements
		 * @return	boolean			Returns true on success otherwise false
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the query should fail
		 */
		public function cache(Array $templates, Array &$error_buffer = NULL)
		{
			if(!sizeof($templates))
			{
				return(false);
			}

			$result = $this->tuxxedo->db->query('
								SELECT 
									`title`, 
									`compiledsource` 
								FROM 
									`' . TUXXEDO_PREFIX . 'templates` 
								WHERE 
										`styleid` = %d 
									AND 
										`title` IN (
											\'%s\'
										);', 
								$this['id'], join('\', \'', array_map(Array($this->tuxxedo->db, 'escape'), $templates)));

			if($result === false || !sizeof($result))
			{
				if(!is_null($error_buffer))
				{
					$error_buffer = $templates;
				}

				return(false);
			}

			$loaded = Array();

			while($row = $result->fetchObject())
			{
				$loaded[] 			= $row->title;
				$this->templates[$row->title] 	= $row->compiledsource;
			}

			if(!is_null($error_buffer) && ($diff = array_diff($templates, $loaded)) && sizeof($diff))
			{
				$error_buffer = $diff;

				return(false);
			}

			return(true);
		}

		/**
		 * Fetches a cached template
		 *
		 * @param	string			The name of the template to fetch
		 * @return	string			Returns the compiled template code for execution, and boolean false on error
		 */
		public function fetch($template)
		{

			$template = strtolower($template);

			if(!array_key_exists($template, $this->templates))
			{
				return(false);
			}

			return($this->templates[$template]);
		}
	}
?>