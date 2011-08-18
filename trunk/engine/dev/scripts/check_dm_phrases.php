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
	 * @subpackage		Dev
	 *
	 * =============================================================================
	 */


	/**
	 * Aliasing rules
	 */
	use DevTools\Utilities\IO;


	/**
	 * Bootstraper
	 */
	require(__DIR__ . '/includes/bootstrap.php');


	/**
	 * Datamanager datapters directory
	 *
	 * @var		string
	 */
	define('ADAPTERS_DIR', realpath(__DIR__ . '/../..') . '/library/Tuxxedo/Datamanager/Adapter/');

	/**
	 * Compatibility constant - self
	 *
	 * @var		void
	 */
	const TUXXEDO_SELF	= NULL;

	/**
	 * Compatibility constant - user agent
	 *
	 * @var		void
	 */
	const TUXXEDO_USERAGENT	= NULL;


	/**
	 * Function to fetch the field names for a specific adapter
	 *
	 * @param	string			The file where the adapter is located
	 * @param	string			The name of the adapter
	 * @return	array			Returns an array with the field names
	 */
	function fetch_field_names($file, $adapter)
	{
		global $registry;

		static $class_code;
		static $ticks;

		if(!$class_code)
		{
			$class_code = 	'Class Temp%d extends \Tuxxedo\Datamanager\Adapter\%s ' . 
					'{' . 
					'public function getFields()' . 
					'{' . 
					'$fields = Array();' . 
					'foreach(array_keys($this->fields) as $field)' . 
					'{' . 
					'if($this->fields[$field][\'type\'] != self::FIELD_VIRTUAL)' . 
					'{' . 
					'$fields[] = $field;' . 
					'}' . 
					'}' . 
					'return($fields);' . 
					'}' . 
					'}';
		}

		require($file);

		++$ticks;

		eval(sprintf($class_code, $ticks, $adapter));
		eval(sprintf('$temp = new Temp%d($registry);', $ticks));

		return($temp->getFields());
	}

	/**
	 * Converts a field name into a field phrase
	 *
	 * @param	string			The adapter name
	 * @param	string			The field name
	 * @return	string			Returns the phrase name for the field
	 */
	function field_phrase($adapter, $field)
	{
		return('dm_' . strtolower($adapter . '_' . $field));
	}


	$registry->register('db', '\Tuxxedo\Database');
	$registry->register('cache', '\Tuxxedo\Datastore')->cache(Array('languages', 'options', 'phrasegroups'));
	$registry->register('intl', '\Tuxxedo\Intl')->cache(Array('datamanagers'));

	$intldm = $intl->getPhrasegroup('datamanagers');

	foreach($cache->languages as $id => $languagedata)
	{
		IO::headline('Checking datamanager phrases for \'' . $languagedata['title'] . '\'');

		foreach(glob(ADAPTERS_DIR . '*.php') as $file)
		{
			$file 	= realpath($file);
			$last 	= explode(DIRECTORY_SEPARATOR, $file);
			$last 	= substr($last[sizeof($last) - 1], 0, -4);

			$fields = fetch_field_names($file, $last);

			IO::ul();
			IO::li($last);
			IO::ul();

			foreach($fields as $field)
			{
				if(!$intldm->getPhrase(field_phrase($last, $field)))
				{
					if(!isset($missing[$languagedata['title']]))
					{
						$missing[$languagedata['title']] = Array();
					}

					$missing[$languagedata['title']][] = field_phrase($last, $field);

					IO::li($field . '... MISSING');
				}
			}

			IO::ul(IO::TAG_END);
			IO::ul(IO::TAG_END);
		}
	}

	if(isset($missing))
	{
		IO::headline('Missing phrases');
		IO::text('One or more phrases are missing, this will produce less verbose errors');
		IO::ul();

		foreach($missing as $language => $phrases)
		{
			IO::li($language);
			IO::ul();

			foreach($phrases as $phrase)
			{
				IO::li($phrase);
			}

			IO::ul(IO::TAG_END);
		}

		IO::ul(IO::TAG_END);
	}
	else
	{
		IO::text('Perfect! There is no missing datamanger phrases');
	}
?>