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
	 * @subpackage		Core
	 *
	 * =============================================================================
	 */


	/**
	 * Core engine namespace, standard exceptions are integrated within this 
	 * part of the namespace, functions that previously were procedural is 
	 * defined as static classes.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	namespace \Tuxxedo\Core;

	/**
	 * Versioning class, contains all of the Core versioning
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Core
	 */
	class Versioning
	{
		/**
		 * Engine simple version, this contains the current 
		 * release in the form of:
		 *
		 * major.minor.release
		 *
		 * For example, 1.0, 1.0.1 ect.
	 	 *
		 * @var		string
		 */
		const VERSION			= '1.0.0';

		/**
		 * Major version number
		 *
		 * @var		integer
		 */
		const VERSION_MAJOR		= 1;

		/**
		 * Minor version number
		 *
		 * @var		integer
		 */
		const VERSION_MINOR		= 0;

		/**
		 * Release version number
		 *
		 * @var		integer
		 */
		const VERSION_RELEASE		= 0;

		/**
		 * Engine version ID, this contains the version id in the form 
		 * of:
		 *
		 * id = (major_version * 10000) + (minor_version * 100) + release_version
		 *
		 * Examples of the version id string can be:
		 *
		 * 1.0.0	10000
		 * 1.1.0	10100
		 * 1.2.2	10202
		 *
		 * @var		integer
		 */
		const VERSION_ID		= 10000;

		/**
		 * Engine version string, this is the full version string, which 
		 * includes the pre-release name, version and the version number 
		 * of the upcoming version if pre-release. For example:
		 *
		 * 1.0.0 Alpha 1
		 * 1.0.3 Release Candidate 2
		 * 1.0.4
		 *
		 * @var		string
		 */
		const VERSION_STRING		= '1.0.0 (development preview)';
	}
?>