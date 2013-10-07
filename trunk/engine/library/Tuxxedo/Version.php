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
	 * 1010110 1001000 1010110 0110100 1100101 1000111 1010110 1101011 1100010 1111001
	 * 1000010 1110000 1100011 1111001 1000010 1101011 1011010 1010111 1010010 1110000
	 * 1011001 0110010 1000110 0110000 1011010 1010111 1010001 1100111 1100100 1000111
	 * 0111000 1100111 1100100 1000111 1101000 1101100 1001001 1000111 1111000 1110110
	 * 1100100 1101101 1010101 1100111 1100010 0110010 1011001 1100111 1100010 1011000
	 * 1101011 1100111 1100010 1000111 1101100 1101101 1011010 1010011 1110111 1100111
	 * 1010100 1010111 1000110 1101011 1011010 1010111 1111000 1101100 1100001 1010111
	 * 0110101 1101100 1001100 1000011 1000010 0110011 1100001 1011000 1010010 1101111
	 * 1100010 0110011 1010110 0110000 1001001 1001000 1101100 1110110 1100100 1010011
	 * 1000010 0110000 1100001 1000111 1010110 1111001 1011010 1010011 1000010 0110011
	 * 1100010 0110011 1010110 1110011 1011010 1000011 1000010 1101001 1011010 1010011
	 * 1000010 1110101 1100010 1111001 1000010 1110011 1100001 1010111 1100100 1101111
	 * 1100100 1000011 1000010 1110000 1100010 1101001 1000010 0110000 1100001 1000111
	 * 1010101 1100111 1011010 1000111 1000110 1111001 1100001 1110111 0111101 0111101
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
		const PREVIEW_TYPE		= 'Beta';

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
		const CODENAME			= 'Beloved';

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
		const FULL			= '1.2.0 "Beloved" Beta 1';
	}
?>