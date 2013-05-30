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
	use Tuxxedo\Datamanager;


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


	$dm_fields = function($adapter)
	{
		$tempdm = Datamanager\Adapter::factory($adapter);

		return(array_filter($tempdm->getFields(), function($field) use($tempdm)
		{
			static $virtual;

			if(!$virtual)
			{
				$virtual = $tempdm->getVirtualFields(false);
			}

			return(!$virtual || !isset($virtual[$field]));
		}));
	};

	$phrase_name = function($adapter, $field)
	{
		return('dm_' . strtolower($adapter . '_' . $field));
	};


	$registry->register('db', '\Tuxxedo\Database');
	$registry->register('datastore', '\Tuxxedo\Datastore')->cache(Array('languages', 'options', 'phrasegroups'));
	$registry->register('options', '\Tuxxedo\Options');
	$registry->register('intl', '\Tuxxedo\Intl')->cache(Array('datamanagers'));

	$intldm = $intl->getPhrasegroup('datamanagers');

	$cli = IO::isCli();

	IO::signature();

	foreach($datastore->languages as $id => $languagedata)
	{
		IO::headline($languagedata['title']);
		IO::ul();

		foreach(glob(ADAPTERS_DIR . '*.php') as $file)
		{
			$ul	= false;
			$last 	= explode(DIRECTORY_SEPARATOR, realpath($file));
			$last 	= substr($last[sizeof($last) - 1], 0, -4);

			$fields = $dm_fields($last);

			IO::li($last);

			foreach($fields as $field)
			{
				$phrase = $phrase_name($last, $field);

				if(!isset($intldm[$phrase]))
				{
					if(!isset($missing[$languagedata['title']]))
					{
						$missing[$languagedata['title']] = Array();
					}

					if(!$ul)
					{
						$ul = true;

						IO::ul();
					}

					$missing[$languagedata['title']][] = $phrase;

					IO::li((!$cli ? $phrase . '... Missing' : str_pad($phrase . '... ', 40, ' ') . 'Missing'), IO::STYLE_BOLD);
				}
			}

			if($ul)
			{
				IO::ul(IO::TAG_END);
			}
		}

		IO::ul(IO::TAG_END);
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
		IO::text(IO::eol() . 'Perfect! There is no missing datamanger phrases');
	}
?>