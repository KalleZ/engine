# SQLiteManager Dump
# Version: 1.2.4
# http://www.sqlitemanager.org/
# 
# Host: localhost
# Generation Time: Thursday 07th 2011f July 2011 11:57 pm
# SQLite Version: 3.6.15
# PHP Version: 5.3.0
# Database: tuxxedo_documentation.sqlite3
# --------------------------------------------------------

#
# Table structure for table: aliases
#
CREATE TABLE `aliases` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`alias` TEXT  NOT NULL ,`file` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL );

#
# Dumping data for table: aliases
#
# --------------------------------------------------------


#
# Table structure for table: class_constants
#
CREATE TABLE `class_constants` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`class` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL );

#
# Dumping data for table: class_constants
#
# --------------------------------------------------------


#
# Table structure for table: class_methods
#
CREATE TABLE `class_methods` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`class` SMALLINT  NOT NULL ,`file` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL ,`final` SMALLINT  NOT NULL ,`abstract` SMALLINT  NOT NULL ,`public` SMALLINT  NOT NULL ,`protected` SMALLINT  NOT NULL ,`private` SMALLINT  NOT NULL ,`static` SMALLINT  NOT NULL );

#
# Dumping data for table: class_methods
#
# --------------------------------------------------------


#
# Table structure for table: class_properties
#
CREATE TABLE `class_properties` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`class` SMALLINT  NOT NULL ,`file` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL ,`final` SMALLINT  NOT NULL ,`abstract` SMALLINT  NOT NULL ,`public` SMALLINT  NOT NULL ,`protected` SMALLINT  NOT NULL ,`private` SMALLINT  NOT NULL ,`static` SMALLINT  NOT NULL );

#
# Dumping data for table: class_properties
#
# --------------------------------------------------------


#
# Table structure for table: classes
#
CREATE TABLE `classes` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`file` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL ,`constants` TEXT  NOT NULL ,`properties` TEXT  NOT NULL ,`methods` TEXT  NOT NULL ,`extends` VARCHAR(128)  NOT NULL ,`implements` TEXT  NOT NULL ,`namespace` INT  NOT NULL ,`docblock` TEXT  NOT NULL ,`final` SMALLINT  NOT NULL ,`abstract` SMALLINT  NOT NULL );

#
# Dumping data for table: classes
#
# --------------------------------------------------------


#
# Table structure for table: constants
#
CREATE TABLE `constants` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`namespace` SMALLINT  NOT NULL ,`file` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL );

#
# Dumping data for table: constants
#
# --------------------------------------------------------


#
# Table structure for table: file
#
CREATE TABLE `file` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`path` VARCHAR(255)  NOT NULL ,`namespaces` TEXT  NOT NULL ,`constants` TEXT  NOT NULL ,`aliases` TEXT  NOT NULL ,`classes` TEXT  NOT NULL ,`interfaces` TEXT  NOT NULL ,`functions` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL );

#
# Dumping data for table: file
#
# --------------------------------------------------------


#
# Table structure for table: functions
#
CREATE TABLE `functions` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`namespace` SMALLINT  NOT NULL ,`file` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL );

#
# Dumping data for table: functions
#
# --------------------------------------------------------


#
# Table structure for table: interface_constants
#
CREATE TABLE `interface_constants` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`class` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL );

#
# Dumping data for table: interface_constants
#
# --------------------------------------------------------


#
# Table structure for table: interface_methods
#
CREATE TABLE `interface_methods` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`interface` SMALLINT  NOT NULL ,`file` TEXT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL ,`final` SMALLINT  NOT NULL ,`abstract` SMALLINT  NOT NULL ,`public` SMALLINT  NOT NULL ,`protected` SMALLINT  NOT NULL ,`private` SMALLINT  NOT NULL ,`static` SMALLINT  NOT NULL );

#
# Dumping data for table: interface_methods
#
# --------------------------------------------------------


#
# Table structure for table: interfaces
#
CREATE TABLE `interfaces` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL ,`file` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL ,`constants` TEXT  NOT NULL ,`properties` TEXT  NOT NULL ,`methods` TEXT  NOT NULL ,`extends` VARCHAR(128)  NOT NULL ,`implements` TEXT  NOT NULL ,`namespace` INT  NOT NULL ,`docblock` TEXT  NOT NULL );

#
# Dumping data for table: interfaces
#
# --------------------------------------------------------


#
# Table structure for table: namespaces
#
CREATE TABLE `namespaces` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(255)  NOT NULL ,`file` SMALLINT  NOT NULL ,`package` SMALLINT  NOT NULL ,`docblock` TEXT  NOT NULL );

#
# Dumping data for table: namespaces
#
# --------------------------------------------------------


#
# Table structure for table: packages
#
CREATE TABLE `packages` (`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL ,`name` VARCHAR(128)  NOT NULL );

#
# Dumping data for table: packages
#
# --------------------------------------------------------


#
# Table structure for table: sqlite_sequence
#
CREATE TABLE sqlite_sequence(name,seq);

#
# Dumping data for table: sqlite_sequence
#
# --------------------------------------------------------


#
# User Defined Function properties: IF
#
/*
function sqliteIf($compare, $good, $bad){
    if ($compare) {
        return $good;
    } else { 
        return $bad;
    }
}
*/

#
# User Defined Function properties: md5rev
#
/*
function md5_and_reverse($string) { return strrev(md5($string)); }
*/
