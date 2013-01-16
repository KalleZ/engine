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
	 * Core Tuxxedo library namespace. This namespace contains all the main 
	 * foundation components of Tuxxedo Engine, plus additional utilities 
	 * thats provided by default. Some of these default components have 
	 * sub namespaces if they provide child objects.
	 *
	 * @author		Kalle Sommer Nielsen	<kalle@tuxxedo.net>
	 * @author		Ross Masters 		<ross@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	namespace Tuxxedo;


	/**
	 * Include check
	 */
	\defined('\TUXXEDO_LIBRARY') or exit;


	/**
	 * Versioning class, contains all of the Core versioning
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class Version
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
		const SIMPLE			= '1.2.0';

		/**
		 * Major version number
		 *
		 * @var		integer
		 */
		const MAJOR			= 1;

		/**
		 * Minor version number
		 *
		 * @var		integer
		 */
		const MINOR			= 2;

		/**
		 * Release version number
		 *
		 * @var		integer
		 */
		const RELEASE			= 0;

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
		const ID			= 10200;

		/**
		 * Development preview mode, this is set to true if this is a development 
		 * release, like a Alpha, Beta or Release Candidate
		 *
		 * @var		boolean
		 */
		const PREVIEW			= true;

		/**
		 * Development preview type, this is set to the preview type, like 'Alpha', 
		 * 'Beta' or 'Release Candidate' if this is a preview release
		 *
		 * @var		string
		 */
		const PREVIEW_TYPE		= 'Alpha';

		/**
		 * Development preview number, this is set to the preview number for the 
		 * current preview type. This is only set if this is a preview release
		 *
		 * @var		integer
		 */
		const PREVIEW_NUMBER		= 1;

		/**
		 * Development codename, this value is always the same for each 
		 * pre-release of a new branch and only changes upon major or minor 
		 * version changes. Each release codename is based on influence that 
		 * caused a new version to branch out.
		 *
		 * @var		string
		 */
		const CODENAME			= 'Invincible';

		/**
		 * Current version control system revision number
		 *
		 * @var		string
		 */
		const VCS_REVISION		= '$Rev$';

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
		const FULL			= '1.2.0 "Invincible" Alpha 1 (experimental)';
	}
?>