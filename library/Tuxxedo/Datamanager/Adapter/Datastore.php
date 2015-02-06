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
	 * @subpackage		Library
	 *
	 * =============================================================================
	 */


	/**
	 * Datamanagers adapter namespace, this contains all the different 
	 * datamanager handler implementations to comply with the standard 
	 * adapter interface, and with the plugins for hooks.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo\Datamanager\Adapter;


	/**
	 * Aliasing rules
	 */
	use Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for datastore
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 * @since		1.1.0
	 */
	class Datastore extends Adapter
	{
		/**
		 * Datamanager name
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const DM_NAME			= 'datastore';

		/**
		 * Identifier name for the datamanager
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const ID_NAME			= 'name';

		/**
		 * Table name for the datamanager
		 *
		 * @var		string
		 *
		 * @since	1.2.0
		 */
		const TABLE_NAME		= 'datastore';


		/**
		 * Fields for validation of datastore elements
		 *
		 * @var		array
		 */
		protected $fields		= [
							'name'		=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_IDENTIFIER
										], 
							'data'		=> [
										'type'		=> parent::FIELD_REQUIRED, 
										'validation'	=> parent::VALIDATE_CALLBACK, 
										'callback'	=> [__CLASS__, 'isValidDatastoreData']
										]
							];


		/**
		 * Constructor, fetches a new datastore element based on its name if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The datastore element name
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the datastore name is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = parent::OPT_DEFAULT, Adapter $parent = NULL)
		{
			if($identifier !== NULL)
			{
				$element = $registry->db->equery('
									SELECT 
										* 
									FROM 
										"' . \TUXXEDO_PREFIX . 'datastore" 
									WHERE 
										"name" = \'%s\'', $identifier);

				if($element && $element->getNumRows())
				{
					$this->data 		= $element->fetchAssoc();
					$this->data['data']	= @\unserialize($this->data['data']);
					$this->identifier 	= $identifier;

					$element->free();
				}
				else
				{
					$this->data['name'] = $identifier;
				}
			}

			parent::init($registry, $options, $parent);
		}

		/**
		 * Checks whether the data is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The data to check
		 * @return	boolean				Returns true if the data is valid
		 */
		public static function isValidDatastoreData(Adapter $dm, Registry $registry, $data)
		{
			$dm->data['data'] = @\serialize($data);

			return($dm->data['data'] !== false);
		}
	}
?>