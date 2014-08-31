INSERT INTO 'options' ('option', 'value', 'defaultvalue', 'type', 'category') VALUES ('language_autodetect', '0', '0', 'b', 'language');

ALTER TABLE 'sessions' ADD 'rehash' BOOL NOT NULL DEFAULT '0';

INSERT INTO `tuxxedo`.`phrases` (`id`, `title`, `translation`, `defaulttranslation`, `changed`, `languageid`, `phrasegroup`) VALUES (NULL, 'dm_phrase_changed', 'Phrase customization status', 'Phrase customization status', '0', '1', 'datamanagers'), (NULL, 'dm_phrase_defaulttranslation', 'Phrase default translation', 'Phrase default translation', '0', '1', 'datamanagers');