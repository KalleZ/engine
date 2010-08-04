<?php
	/**
	 * Tuxxedo Software Engine Development Tools
	 * =============================================================================
	 *
	 * @author		Kalle Sommer Nielsen 	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @copyright		Tuxxedo Software Development 2006+
	 * @package		Engine
	 * @subpackage		DevTools
	 *
	 * =============================================================================
	 */

	defined('TUXXEDO') or exit;


	/**
	 * Minor testing class, used for testing availability for 
	 * various requirement checks
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		DevTools
	 */
	class Tuxxedo_Test
	{
		const OPT_REQUIRED	= 1;
		const OPT_OPTIONAL	= 2;
		const OPT_EXTENSION	= 4;
		const OPT_VERSION	= 8;


		protected $options	= 0;
		protected $argv		= Array();


		public function __construct($options, Array $argv = Array())
		{
			$this->options 	= (integer) $options;
			$this->argv 	= $argv;
		}

		public function isRequired()
		{
			return(($this->options & self::OPT_REQUIRED) > 0);
		}

		public function isOptional()
		{
			return(($this->options & self::OPT_OPTIONAL) > 0);
		}

		public function test()
		{
			if($this->options & self::OPT_EXTENSION)
			{
				return(extension_loaded($this->argv[0]));
			}
			elseif($this->options & self::OPT_VERSION)
			{
				return(version_compare($this->argv[0], $this->argv[1], '>=') == 0);
			}

			return(false);
		}
	}
?>