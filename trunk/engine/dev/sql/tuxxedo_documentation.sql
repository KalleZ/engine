CREATE TABLE `namespaces`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`name` VARCHAR(255) NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE `classes`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL, 
	`constants` TINYTEXT NOT NULL, 
	`properties` TINYTEXT NOT NULL, 
	`methods` TINYTEXT NOT NULL, 
	`extends` VARCHAR(128) NOT NULL, 
	`implements` TINYTEXT NOT NULL, 
	`namespace` INTEGER NOT NULL, 
	`docblock` TEXT NOT NULL, 
	`final` TINYINT(1) NOT NULL, 
	`abstract` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE `classes`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL, 
	`constants` TINYTEXT NOT NULL, 
	`properties` TINYTEXT NOT NULL, 
	`methods` TINYTEXT NOT NULL, 
	`extends` VARCHAR(128) NOT NULL, 
	`implements` TINYTEXT NOT NULL, 
	`namespace` INTEGER NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE `aliases`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`alias` MEDIUMTEXT NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL
);

CREATE TABLE `packages`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE `file`
(
	`id` INTEGER NOT NULL AUTOINCREMENT, 
	`path` VARCHAR(255) NOT NULL, 
	`namespaces` TINYTEXT NOT NULL, 
	`constants` TINYTEXT NOT NULL, 
	`aliases` TINYTEXT NOT NULL, 
	`classes` TINYTEXT NOT NULL, 
	`interfaces` TINYTEXT NOT NULL, 
	`functions` TINYTEXT NOT NULL, 
	`package` TINYINT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE `functions`
(
);

CREATE TABLE `constants`
(
);

CREATE TABLE `class_constants`
(
);

CREATE TABLE `interface_constants`
(
);

CREATE TABLE `class_methods`
(
);

CREATE TABLE `interface_methods`
(
);

CREATE TABLE `class_properties`
(
);