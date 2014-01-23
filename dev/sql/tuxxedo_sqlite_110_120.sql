INSERT INTO 'options' ('option', 'value', 'defaultvalue', 'type', 'category') VALUES ('language_autodetect', '0', '0', 'b', 'language');

ALTER TABLE 'sessions' ADD 'rehash' BOOL NOT NULL DEFAULT '0';