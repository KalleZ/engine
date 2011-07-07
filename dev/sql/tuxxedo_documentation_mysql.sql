CREATE TABLE IF NOT EXISTS `namespaces`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(255) NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `classes`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
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

CREATE TABLE IF NOT EXISTS  `interfaces`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
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

CREATE TABLE IF NOT EXISTS `aliases`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`alias` MEDIUMTEXT NOT NULL, 
	`file` TINYINT NOT NULL, 
	`package` TINYINT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `packages`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `file`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`path` VARCHAR(255) NOT NULL, 
	`namespaces` TINYTEXT NOT NULL, 
	`constants` TINYTEXT NOT NULL, 
	`aliases` TINYTEXT NOT NULL, 
	`classes` TINYTEXT NOT NULL, 
	`interfaces` TINYTEXT NOT NULL, 
	`functions` TINYTEXT NOT NULL, 
	`package` TINYINT NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `functions`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`namespace` TINYINT(1) NOT NULL, 
	`file` TINYTEXT NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `constants`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`namespace` TINYINT(1) NOT NULL, 
	`file` TINYTEXT NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `class_constants`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`class` TINYINT(1) NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `interface_constants`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`class` TINYINT(1) NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	`docblock` TEXT NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `class_methods`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`class` TINYINT(1) NOT NULL, 
	`file` TINYTEXT NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	`docblock` TEXT NOT NULL, 
	`final` TINYINT(1) NOT NULL, 
	`abstract` TINYINT(1) NOT NULL, 
	`public` TINYINT(1) NOT NULL, 
	`protected` TINYINT(1) NOT NULL, 
	`private` TINYINT(1) NOT NULL, 
	`static` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `interface_methods`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`interface` TINYINT(1) NOT NULL, 
	`file` TINYTEXT NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	`docblock` TEXT NOT NULL, 
	`final` TINYINT(1) NOT NULL, 
	`abstract` TINYINT(1) NOT NULL, 
	`public` TINYINT(1) NOT NULL, 
	`protected` TINYINT(1) NOT NULL, 
	`private` TINYINT(1) NOT NULL, 
	`static` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);

CREATE TABLE IF NOT EXISTS `class_properties`
(
	`id` INTEGER NOT NULL AUTO_INCREMENT, 
	`name` VARCHAR(128) NOT NULL, 
	`class` TINYINT(1) NOT NULL, 
	`file` TINYTEXT NOT NULL, 
	`package` TINYINT(1) NOT NULL, 
	`docblock` TEXT NOT NULL, 
	`final` TINYINT(1) NOT NULL, 
	`abstract` TINYINT(1) NOT NULL, 
	`public` TINYINT(1) NOT NULL, 
	`protected` TINYINT(1) NOT NULL, 
	`private` TINYINT(1) NOT NULL, 
	`static` TINYINT(1) NOT NULL, 
	PRIMARY KEY(`id`)
);