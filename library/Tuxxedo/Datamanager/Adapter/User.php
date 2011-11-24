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
	defined('\TUXXEDO_LIBRARY') or exit;


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
												'validation'	=> self::VALIDATE_CALLBACK, 
												'callback'	=> Array(__CLASS__, 'isValidEmail')
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
												'type'		=> self::FIELD_PROTECTED
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
		 * @param	integer				Additional options to apply on the datamanager
		 * @param	\Tuxxedo\Datamanager\Adapter	The parent datamanager if any
		 *
		 * @throws	\Tuxxedo\Exception\Basic	Throws an exception if the user id is set and it failed to load for some reason
		 * @throws	\Tuxxedo\Exception\SQL		Throws a SQL exception if a database call fails
		 */
		public function __construct(Registry $registry, $identifier = NULL, $options = self::OPT_DEFAULT, Adapter $parent = NULL)
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
					throw new Exception('Invalid user id');
				}

				$this->data 					= $user->fetchAssoc();
				$this->identifier 				= $identifier;
				$this->fields['timezone_offset']['parameters']	= Array($this->data['timezone']);

				$user->free();
			}

			parent::init($registry, $options, $parent);
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
		public static function isValidUsergroup(Adapter $dm, Registry $registry, $id = NULL)
		{
			return(isset($registry->datastore->usergroups[$id]));
		}

		/**
		 * Checks whether a timezone based by its name is valid
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The timezone name to check for validity
		 * @return	boolean				Returns true if the timezone is loaded and exists in the datastore cache, otherwise false
		 */
		public static function isValidTimezone(Adapter $dm, Registry $registry, $timezone = NULL)
		{
			if(!isset($registry->datastore->timezones[$timezone]))
			{
				return(false);
			}

			$this->data['timezone_offset'] = $registry->datastore->timezones[$timezone];

			return(true);
		}

		/**
		 * Checks whether a user name is taken or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The username to check
		 * @return	boolean				Returns true if the username is free to be taken, otherwise false
		 */
		public static function isValidUsername(Adapter $dm, Registry $registry, $username = NULL)
		{
			return(!self::isAvailableUserField($registry, 'username', $username));
		}

		/**
		 * Checks whether an email address is taken or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The username to check
		 * @return	boolean				Returns true if the email is free to be taken, otherwise false
		 */
		public static function isValidEmail(Adapter $dm, Registry $registry, $email = NULL)
		{
			return(\is_valid_email($email) && !self::isAvailableUserField($registry, 'email', $email));
		}

		/**
		 * Checks whether a style id is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The style id
		 * @return	boolean				True if the style exists, otherwise false
		 */
		public static function isValidStyleId(Adapter $dm, Registry $registry, $styleid = NULL)
		{
			return($registry->datastore->styleinfo && isset($registry->datastore->styleinfo[$styleid]));
		}

		/**
		 * Checks whether a language id is valid or not
		 *
		 * @param	\Tuxxedo\Datamanager\Adapter	The current datamanager adapter
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	integer				The language id
		 * @return	boolean				True if the language exists, otherwise false
		 */
		public static function isValidLanguageId(Adapter $dm, Registry $registry, $languageid = NULL)
		{
			return($registry->datastore->languages && isset($registry->datastore->languages[$languageid]));
		}

		/**
		 * Helper validation routine to check a single field in the database
		 *
		 * @param	\Tuxxedo\Registry		The Registry reference
		 * @param	string				The field to check
		 * @param	string				The value to check
		 * @return	boolean				Returns true if the value exists, otherwise false
		 */
		protected static function isAvailableUserField(Registry $registry, $field, $value)
		{
			$query = $registry->db->equery('
							SELECT 
								* 
							FROM 
								`' . \TUXXEDO_PREFIX . 'users` 
							WHERE 
								`%s` = \'%s\' 
							LIMIT 1', $field, $value);

			return($query && $query->getNumRows());
		}
	}
?>