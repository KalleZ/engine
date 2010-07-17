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
	 * Datamanager for users
	 *
	 * @author	Kalle Sommer Nielsen <kalle@tuxxedo.net>
	 * @version	1.0
	 * @package	Engine
	 */
	class Tuxxedo_Datamanager_User extends Tuxxedo_Datamanager implements Tuxxedo_Datamanager_API_Cache
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
		 * @param	Tuxxedo			The Tuxxedo object reference
		 * @param	integer			The user id
		 *
		 * @throws	Tuxxedo_Exception	Throws an exception if the user id is set and it failed to load for some reason
		 * @throws	Tuxxedo_Basic_Exception	Throws a basic exception if a database call fails
		 */
		public function __construct(Tuxxedo $tuxxedo, $identifier = NULL)
		{
			$this->tuxxedo 		= $tuxxedo;

			$this->dmname		= 'user';
			$this->tablename	= TUXXEDO_PREFIX . 'users';
			$this->idname		= 'id';
			$this->information	= &$this->userdata;

			if($identifier !== NULL)
			{
				$user = $tuxxedo->db->query('
								SELECT 
									* 
								FROM 
									`' . TUXXEDO_PREFIX . 'users` 
								WHERE 
									`id` = %d', $identifier);

				if(!$user)
				{
					throw new Tuxxedo_Exception('Invalid user id');
				}

				$this->data 					= $user->fetchAssoc();
				$this->identifier 				= $identifier;
				$this->fields['timezone_offset']['parameters']	= Array($this->data['timezone']);
			}
		}

		/**
		 * Overloads the set method, so we can catch timezones 
		 * if updated so the validator passes
		 *
		 * @param	string			The field to update
		 * @param	mixed			The field value
		 * @return	void			No value is returned
		 */
		public function set($field, $value)
		{
			$field = strtolower($field);

			if($field == 'timezone_offset')
			{
				$this->fields['timezone']['parameters'] = Array($value);
			}

			$this->userdata[$field] = $value;
		}

		/**
		 * Checks whether a usergroup is valid
		 *
		 * @param	Tuxxedo			Instance to the Tuxxedo registry
		 * @param	integer			The usergroup id to check for validity
		 * @return	boolean			Returns true if the usergroup is loaded and exists in the datastore cache, otherwise false
		 */
		public static function isValidUsergroup(Tuxxedo $tuxxedo, $id)
		{
			return(isset($tuxxedo->cache->usergroups[$id]));
		}

		/**
		 * Checks whether a timezone based by its name is valid
		 *
		 * @param	Tuxxedo			Instance to the Tuxxedo registry
		 * @param	string			The timezone name to check for validity
		 * @return	boolean			Returns true if the timezone is loaded and exists in the datastore cache, otherwise false
		 */
		public static function isValidTimezone(Tuxxedo $tuxxedo, $timezone)
		{
			return(isset($tuxxedo->cache->timezones[$timezone]));
		}

		/**
		 * Gets a timezone offset based on its timezone name
		 *
		 * @param	Tuxxedo			Instance to the Tuxxedo registry
		 * @param	string			The timezone name
		 * @return	string			Returns the timezone offset, or 0 if the timezone name was invalid
		 */
		public static function getTimezoneOffset(Tuxxedo $tuxxedo, $timezone)
		{
			if(!self::isValidTimezone($tuxxedo, $timezone))
			{
				return(0);
			}

			return($tuxxedo->cache->timezones[$timezone]);
		}

		/**
		 * Checks whether a user name is taken or not
		 *
		 * @param	Tuxxedo			Instance to the Tuxxedo registry
		 * @param	string			The username to check
		 * @return	boolean			Returns true if the username is free to be taken, otherwise false
		 */
		public static function isValidUsername(Tuxxedo $tuxxedo, $username)
		{
			$query = $tuxxedo->db->query('
							SELECT 
								* 
							FROM 
								`' . TUXXEDO_PREFIX . 'users` 
							WHERE 
								`username` = \'%s\' 
							LIMIT 1', $tuxxedo->db->escape($username))

			return($query && $query->getNumRows() == 0);
		}

		/**
		 * Checks whether a style id is valid or not
		 *
		 * @param	integer			The style id
		 * @return	boolean			True if the style exists, otherwise false
		 */
		public static function isValidStyleId(Tuxxedo $tuxxedo, $styleid)
		{
			return($tuxxedo->cache->styleinfo && isset($tuxxedo->cache->styleinfo[$styleid]));
		}

		/**
		 * Checks whether a language id is valid or not
		 *
		 * @param	integer			The language id
		 * @return	boolean			True if the language exists, otherwise false
		 */
		public static function isValidLanguageId(Tuxxedo $tuxxedo, $languageid)
		{
			return($tuxxedo->cache->languages && isset($tuxxedo->cache->languages[$languageid]));
		}
	}
?>