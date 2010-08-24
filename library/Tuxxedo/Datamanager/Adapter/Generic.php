<?php
	/**
	 * Tuxxedo Software Engine
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 *
	 * =============================================================================
	 */
	
	namespace Tuxxedo\Datamanager\Adapter;
	use Tuxxedo\Exception;
	
	/**
	 * Generic datamanager, allowing runtime created datamanagers
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	class Generic extends \Tuxxedo\Datamanager\Adapter
	{
		const OPT_TABLENAME		= 1;
		const OPT_IDENTIFIERNAME	= 2;

		/**
		 * Fields for validation of styles
		 *
		 * @var		array
		 */
		protected $fields		= Array();

		/**
		 * Options map
		 *
		 * @var		array
		 */
		protected $options		= Array(
							self::OPT_TABLENAME		=> 'tablename', 
							self::OPT_


		/**
		 * Constructor for the generic datamanager
		 *
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	array			Options for the generic datamanagers
		 */
		public function __construct(Registry $registry, Array $options = NULL)
		{
			$this->registry 		= $registry;

			$this->dmname		= 'generic';
			$this->information	= &$this->userdata;

			if($options && sizeof($options))
			{
				foreach($options as $name => $value)
				{
					$this->setOption($name, $value);
				}
			}
		}

		/**
		 * Updates an option
		 *
		 * @param	integer			The option to change
		 * @param	string			The new option value
		 * @return	void			No value is returned
		 */
		
	}
