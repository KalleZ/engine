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
	use Tuxxedo\Exception;
	use Tuxxedo\Registry;


	/**
	 * Include check
	 */
	defined('TUXXEDO_LIBRARY') or exit;


	/**
	 * Datamanager for users
	 *
	 * @author		Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version		1.0
	 * @package		Engine
	 * @subpackage		Library
	 */
	class User extends Adapter
	{
		/**
		 * Fields for validation of users
		 *
		 * @var		array
		 */
		protected $fields		= Array(
							'id'			=> Array(
												'type'		=> self::FIELD_PROTECTED
												), 
							'username'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isValidUsername')
												), 
							'email'			=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_EMAIL
												), 
							'name'			=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING
												), 
							'password'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING
												), 
							'usergroupid'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isValidUsergroup')
												), 
							'salt'			=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_STRING
												), 
							'styleid'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isStyleId')
												), 
							'languageid'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isLanguageId')
												), 
							'timezone'		=> Array(
												'type'		=> self::FIELD_REQUIRED, 
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isValidTimezone')
												), 
							'timezone_offset'	=> Array(
												'type'		=> self::FIELD_PROTECTED, 
												'callback'	=> Array(__CLASS__, 'getTimezoneOffset'), 
												'parameters'	=> Array()
												), 

							'permissions'		=> Array(
												'type'		=> self::FIELD_OPTIONAL, 
												'validation'	=> self::VALIDATE_NUMERIC, 
												'default'	=> 0
												)
							);



		/**
		 * Constructor, fetches a new user based on its id if set
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The user id
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the user id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL)
		{
			$this->dmname		= 'user';
			$this->tablename	= \TUXXEDO_PREFIX . 'users';
			$this->idname		= 'id';

			if($identifier !== NULL)
			{
				$user = $registry->db->query('
								SELECT 
									* 
								FROM 
									`' . \TUXXEDO_PREFIX . 'users` 
								WHERE 
									`id` = %d', $identifier);

				if(!$user || !$user->getNumRows())
				{
					throw new Exception\Exception('Invalid user id');
				}

				$this->data 					= $user->fetchAssoc();
				$this->identifier 				= $identifier;
				$this->fields['timezone_offset']['parameters']	= Array($this->data['timezone']);

				$user->free();
			}

			parent::init($registry);
		}

		/**
		 * Overloads the set method, so we can catch timezones 
		 * if updated so the validator passes
		 *
		 * @param	string				The field to update
		 * @param	mixed				The field value
		 * @return	void				No value is returned
		 */
		public function set($field, $value)
		{
			$field = \strtolower($field);

			if($field == 'timezone_offset')
			{
				$this->fields['timezone']['parameters'] = Array($value);
			}

			$this->userdata->{$field} = $value;
		}

		/**
		 * Checks whether a usergroup is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The usergroup id to check for validity
		 * @return	boolean				Returns true if the usergroup is loaded and exists in the datastore cache, otherwise false
		 */
		public static function isValidUsergroup(Adapter $dm, Registry $registry, $id)
		{
			return(isset($registry->cache->usergroups[$id]));
		}

		/**
		 * Checks whether a timezone based by its name is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The timezone name to check for validity
		 * @return	boolean				Returns true if the timezone is loaded and exists in the datastore cache, otherwise false
		 */
		public static function isValidTimezone(Adapter $dm, Registry $registry, $timezone)
		{
			return(isset($registry->cache->timezones[$timezone]));
		}

		/**
		 * Gets a timezone offset based on its timezone name
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The timezone name
		 * @return	string				Returns the timezone offset, or 0 if the timezone name was invalid
		 */
		public static function getTimezoneOffset(Adapter $dm, Registry $registry, $timezone)
		{
			if(!self::isValidTimezone($registry, $timezone))
			{
				return(0);
			}

			return($registry->cache->timezones[$timezone]);
		}

		/**
		 * Checks whether a user name is taken or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The username to check
		 * @return	boolean				Returns true if the username is free to be taken, otherwise false
		 */
		public static function isValidUsername(Adapter $dm, Registry $registry, $username)
		{
			$query = $registry->db->equery('
							SELECT 
								* 
							FROM 
								`' . \TUXXEDO_PREFIX . 'users` 
							WHERE 
								`username` = \'%s\' 
							LIMIT 1', $username);

			return($query && $query->getNumRows() == 0);
		}

		/**
		 * Checks whether a style id is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The style id
		 * @return	boolean				True if the style exists, otherwise false
		 */
		public static function isValidStyleId(Adapter $dm, Registry $registry, $styleid)
		{
			return($registry->cache->styleinfo && isset($registry->cache->styleinfo[$styleid]));
		}

		/**
		 * Checks whether a language id is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The language id
		 * @return	boolean				True if the language exists, otherwise false
		 */
		public static function isValidLanguageId(Adapter $dm, Registry $registry, $languageid)
		{
			return($registry->cache->languages && isset($registry->cache->languages[$languageid]));
		}
	}
?>