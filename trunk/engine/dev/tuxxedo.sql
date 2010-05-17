-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 17. 05 2010 kl. 03:48:13
-- Serverversion: 5.1.36
-- PHP-version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tuxxedo`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `datastore`
--

CREATE TABLE IF NOT EXISTS `datastore` (
  `name` varchar(128) NOT NULL,
  `data` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Data dump for tabellen `datastore`
--

INSERT INTO `datastore` (`name`, `data`) VALUES
('options', 'a:10:{s:13:"cookie_domain";s:0:"";s:14:"cookie_expires";i:1800;s:11:"cookie_path";s:0:"";s:13:"cookie_prefix";s:8:"tuxxedo_";s:11:"date_format";s:14:"H:i:s, j/n - Y";s:13:"date_timezone";s:3:"UTC";s:20:"date_timezone_offset";i:0;s:11:"language_id";i:1;s:8:"style_id";i:1;s:13:"style_storage";s:8:"database";}'),
('styleinfo', 'a:1:{i:1;a:5:{s:2:"id";s:1:"1";s:4:"name";s:7:"Default";s:9:"developer";s:28:"Tuxxedo Software Development";s:8:"styledir";s:7:"default";s:7:"default";s:1:"1";}}'),
('usergroups', 'a:2:{i:1;a:4:{s:2:"id";s:1:"1";s:5:"title";s:14:"Administrators";s:4:"type";s:1:"1";s:11:"permissions";s:1:"0";}i:2;a:4:{s:2:"id";s:1:"2";s:5:"title";s:13:"Regular users";s:4:"type";s:1:"2";s:11:"permissions";s:1:"0";}}'),
('timezones', 'a:403:{s:17:"Pacific/Pago Pago";s:3:"-11";s:14:"Pacific/Midway";s:3:"-11";s:12:"Pacific/Apia";s:3:"-11";s:12:"Pacific/Niue";s:3:"-11";s:16:"Pacific/Johnston";s:3:"-10";s:16:"Pacific/Honolulu";s:3:"-10";s:15:"Pacific/Fakaofo";s:3:"-10";s:17:"Pacific/Rarotonga";s:3:"-10";s:14:"Pacific/Tahiti";s:3:"-10";s:17:"Pacific/Marquesas";s:4:"-9.5";s:12:"America/Adak";s:2:"-9";s:15:"Pacific/Gambier";s:2:"-9";s:12:"America/Nome";s:2:"-8";s:15:"America/Yakutat";s:2:"-8";s:17:"America/Anchorage";s:2:"-8";s:16:"Pacific/Pitcairn";s:2:"-8";s:14:"America/Juneau";s:2:"-8";s:15:"America/Phoenix";s:2:"-7";s:20:"America/Dawson Creek";s:2:"-7";s:14:"America/Dawson";s:2:"-7";s:15:"America/Tijuana";s:2:"-7";s:17:"America/Vancouver";s:2:"-7";s:19:"America/Los Angeles";s:2:"-7";s:18:"America/Hermosillo";s:2:"-7";s:18:"America/Whitehorse";s:2:"-7";s:16:"America/Mazatlan";s:2:"-6";s:17:"America/Chihuahua";s:2:"-6";s:15:"America/Managua";s:2:"-6";s:14:"America/Regina";s:2:"-6";s:13:"America/Boise";s:2:"-6";s:14:"America/Belize";s:2:"-6";s:14:"America/Inuvik";s:2:"-6";s:21:"America/Cambridge Bay";s:2:"-6";s:16:"America/Shiprock";s:2:"-6";s:14:"Pacific/Easter";s:2:"-6";s:19:"America/Tegucigalpa";s:2:"-6";s:16:"America/Edmonton";s:2:"-6";s:18:"America/Costa Rica";s:2:"-6";s:19:"America/El Salvador";s:2:"-6";s:17:"America/Guatemala";s:2:"-6";s:21:"America/Swift Current";s:2:"-6";s:19:"America/Yellowknife";s:2:"-6";s:17:"Pacific/Galapagos";s:2:"-6";s:14:"America/Denver";s:2:"-6";s:17:"America/Guayaquil";s:2:"-5";s:19:"America/Mexico City";s:2:"-5";s:17:"America/Menominee";s:2:"-5";s:14:"America/Merida";s:2:"-5";s:15:"America/Jamaica";s:2:"-5";s:14:"America/Cancun";s:2:"-5";s:14:"America/Cayman";s:2:"-5";s:15:"America/Chicago";s:2:"-5";s:25:"America/Indiana/Tell City";s:2:"-5";s:14:"America/Bogota";s:2:"-5";s:16:"America/Atikokan";s:2:"-5";s:20:"America/Indiana/Knox";s:2:"-5";s:12:"America/Lima";s:2:"-5";s:17:"America/Monterrey";s:2:"-5";s:19:"America/Rainy River";s:2:"-5";s:27:"America/North Dakota/Center";s:2:"-5";s:30:"America/North Dakota/New Salem";s:2:"-5";s:14:"America/Panama";s:2:"-5";s:22:"America/Port-au-Prince";s:2:"-5";s:20:"America/Rankin Inlet";s:2:"-5";s:16:"America/Winnipeg";s:2:"-5";s:16:"America/Resolute";s:2:"-5";s:15:"America/Caracas";s:4:"-4.5";s:16:"America/St Lucia";s:2:"-4";s:21:"America/Santo Domingo";s:2:"-4";s:21:"America/St Barthelemy";s:2:"-4";s:16:"America/St Kitts";s:2:"-4";s:21:"America/Indiana/Vevay";s:2:"-4";s:26:"America/Indiana/Petersburg";s:2:"-4";s:18:"America/St Vincent";s:2:"-4";s:14:"America/Havana";s:2:"-4";s:14:"America/Guyana";s:2:"-4";s:16:"Atlantic/Stanley";s:2:"-4";s:17:"Antarctica/Palmer";s:2:"-4";s:15:"America/Tortola";s:2:"-4";s:28:"America/Indiana/Indianapolis";s:2:"-4";s:16:"America/Santiago";s:2:"-4";s:23:"America/Indiana/Marengo";s:2:"-4";s:19:"America/Thunder Bay";s:2:"-4";s:15:"America/Toronto";s:2:"-4";s:17:"America/St Thomas";s:2:"-4";s:23:"America/Indiana/Winamac";s:2:"-4";s:14:"America/Manaus";s:2:"-4";s:18:"America/Guadeloupe";s:2:"-4";s:15:"America/Nipigon";s:2:"-4";s:15:"America/Marigot";s:2:"-4";s:18:"America/Martinique";s:2:"-4";s:18:"America/Montserrat";s:2:"-4";s:14:"America/Nassau";s:2:"-4";s:16:"America/New York";s:2:"-4";s:14:"America/La Paz";s:2:"-4";s:27:"America/Kentucky/Monticello";s:2:"-4";s:19:"America/Puerto Rico";s:2:"-4";s:16:"America/Montreal";s:2:"-4";s:25:"America/Indiana/Vincennes";s:2:"-4";s:19:"America/Porto Velho";s:2:"-4";s:15:"America/Iqaluit";s:2:"-4";s:27:"America/Kentucky/Louisville";s:2:"-4";s:19:"America/Pangnirtung";s:2:"-4";s:18:"America/Rio Branco";s:2:"-4";s:21:"America/Port of Spain";s:2:"-4";s:20:"America/Blanc-Sablon";s:2:"-4";s:17:"America/Boa Vista";s:2:"-4";s:20:"America/Campo Grande";s:2:"-4";s:14:"America/Cuiaba";s:2:"-4";s:16:"America/Barbados";s:2:"-4";s:16:"America/Asuncion";s:2:"-4";s:15:"America/Grenada";s:2:"-4";s:15:"America/Antigua";s:2:"-4";s:26:"America/Argentina/San Luis";s:2:"-4";s:13:"America/Aruba";s:2:"-4";s:15:"America/Curacao";s:2:"-4";s:16:"America/Anguilla";s:2:"-4";s:18:"America/Grand Turk";s:2:"-4";s:16:"America/Eirunepe";s:2:"-4";s:16:"America/Dominica";s:2:"-4";s:15:"America/Detroit";s:2:"-4";s:17:"America/Araguaina";s:2:"-3";s:30:"America/Argentina/Buenos Aires";s:2:"-3";s:18:"America/Montevideo";s:2:"-3";s:15:"America/Moncton";s:2:"-3";s:25:"America/Argentina/Cordoba";s:2:"-3";s:17:"America/Goose Bay";s:2:"-3";s:27:"America/Argentina/Catamarca";s:2:"-3";s:16:"Atlantic/Bermuda";s:2:"-3";s:18:"America/Paramaribo";s:2:"-3";s:14:"America/Recife";s:2:"-3";s:16:"America/Santarem";s:2:"-3";s:17:"America/Sao Paulo";s:2:"-3";s:13:"America/Thule";s:2:"-3";s:23:"America/Argentina/Jujuy";s:2:"-3";s:18:"Antarctica/Rothera";s:2:"-3";s:26:"America/Argentina/La Rioja";s:2:"-3";s:17:"America/Glace Bay";s:2:"-3";s:15:"America/Halifax";s:2:"-3";s:13:"America/Bahia";s:2:"-3";s:17:"America/Fortaleza";s:2:"-3";s:15:"America/Cayenne";s:2:"-3";s:13:"America/Belem";s:2:"-3";s:25:"America/Argentina/Ushuaia";s:2:"-3";s:25:"America/Argentina/Tucuman";s:2:"-3";s:30:"America/Argentina/Rio Gallegos";s:2:"-3";s:25:"America/Argentina/Mendoza";s:2:"-3";s:23:"America/Argentina/Salta";s:2:"-3";s:26:"America/Argentina/San Juan";s:2:"-3";s:14:"America/Maceio";s:2:"-3";s:16:"America/St Johns";s:4:"-2.5";s:16:"America/Miquelon";s:2:"-2";s:15:"America/Noronha";s:2:"-2";s:22:"Atlantic/South Georgia";s:2:"-2";s:15:"America/Godthab";s:2:"-2";s:19:"Atlantic/Cape Verde";s:2:"-1";s:13:"Africa/Banjul";s:1:"0";s:3:"UTC";s:1:"0";s:20:"America/Scoresbysund";s:1:"0";s:20:"America/Danmarkshavn";s:1:"0";s:15:"Africa/Sao Tome";s:1:"0";s:11:"Africa/Lome";s:1:"0";s:15:"Atlantic/Azores";s:1:"0";s:17:"Africa/Casablanca";s:1:"0";s:13:"Africa/Bissau";s:1:"0";s:14:"Africa/Conakry";s:1:"0";s:13:"Africa/Bamako";s:1:"0";s:18:"Atlantic/St Helena";s:1:"0";s:15:"Africa/Monrovia";s:1:"0";s:15:"Africa/El Aaiun";s:1:"0";s:18:"Atlantic/Reykjavik";s:1:"0";s:17:"Africa/Nouakchott";s:1:"0";s:12:"Africa/Accra";s:1:"0";s:18:"Africa/Ouagadougou";s:1:"0";s:12:"Africa/Dakar";s:1:"0";s:15:"Africa/Freetown";s:1:"0";s:14:"Africa/Abidjan";s:1:"0";s:13:"Africa/Bangui";s:1:"1";s:13:"Europe/London";s:1:"1";s:14:"Africa/Algiers";s:1:"1";s:14:"Atlantic/Faroe";s:1:"1";s:13:"Europe/Lisbon";s:1:"1";s:16:"Atlantic/Madeira";s:1:"1";s:13:"Europe/Jersey";s:1:"1";s:18:"Africa/Brazzaville";s:1:"1";s:15:"Atlantic/Canary";s:1:"1";s:13:"Europe/Dublin";s:1:"1";s:13:"Africa/Niamey";s:1:"1";s:17:"Africa/Libreville";s:1:"1";s:17:"Africa/Porto-Novo";s:1:"1";s:12:"Africa/Lagos";s:1:"1";s:15:"Africa/Windhoek";s:1:"1";s:15:"Africa/Kinshasa";s:1:"1";s:13:"Africa/Luanda";s:1:"1";s:18:"Europe/Isle of Man";s:1:"1";s:13:"Africa/Douala";s:1:"1";s:15:"Europe/Guernsey";s:1:"1";s:13:"Africa/Malabo";s:1:"1";s:15:"Africa/Ndjamena";s:1:"1";s:12:"Africa/Tunis";s:1:"2";s:13:"Africa/Maseru";s:1:"2";s:14:"Africa/Tripoli";s:1:"2";s:14:"Africa/Mbabane";s:1:"2";s:13:"Europe/Monaco";s:1:"2";s:12:"Europe/Malta";s:1:"2";s:13:"Europe/Zurich";s:1:"2";s:17:"Europe/San Marino";s:1:"2";s:12:"Europe/Vaduz";s:1:"2";s:14:"Europe/Vatican";s:1:"2";s:15:"Europe/Sarajevo";s:1:"2";s:13:"Europe/Tirane";s:1:"2";s:16:"Europe/Stockholm";s:1:"2";s:13:"Europe/Skopje";s:1:"2";s:13:"Europe/Vienna";s:1:"2";s:11:"Europe/Rome";s:1:"2";s:12:"Europe/Paris";s:1:"2";s:11:"Europe/Oslo";s:1:"2";s:13:"Europe/Zagreb";s:1:"2";s:13:"Europe/Warsaw";s:1:"2";s:13:"Europe/Prague";s:1:"2";s:16:"Europe/Podgorica";s:1:"2";s:13:"Europe/Madrid";s:1:"2";s:17:"Europe/Luxembourg";s:1:"2";s:13:"Europe/Berlin";s:1:"2";s:19:"Arctic/Longyearbyen";s:1:"2";s:16:"Europe/Gibraltar";s:1:"2";s:15:"Europe/Belgrade";s:1:"2";s:17:"Europe/Copenhagen";s:1:"2";s:17:"Europe/Bratislava";s:1:"2";s:15:"Europe/Brussels";s:1:"2";s:15:"Africa/Gaborone";s:1:"2";s:15:"Europe/Budapest";s:1:"2";s:13:"Africa/Harare";s:1:"2";s:12:"Africa/Ceuta";s:1:"2";s:19:"Africa/Johannesburg";s:1:"2";s:14:"Europe/Andorra";s:1:"2";s:16:"Africa/Bujumbura";s:1:"2";s:17:"Africa/Lubumbashi";s:1:"2";s:16:"Europe/Amsterdam";s:1:"2";s:13:"Africa/Lusaka";s:1:"2";s:16:"Europe/Ljubljana";s:1:"2";s:15:"Africa/Blantyre";s:1:"2";s:13:"Africa/Maputo";s:1:"2";s:13:"Africa/Kigali";s:1:"2";s:12:"Europe/Minsk";s:1:"3";s:15:"Europe/Chisinau";s:1:"3";s:16:"Europe/Bucharest";s:1:"3";s:16:"Europe/Mariehamn";s:1:"3";s:17:"Europe/Simferopol";s:1:"3";s:15:"Europe/Helsinki";s:1:"3";s:15:"Europe/Istanbul";s:1:"3";s:13:"Europe/Athens";s:1:"3";s:18:"Europe/Kaliningrad";s:1:"3";s:11:"Europe/Kiev";s:1:"3";s:11:"Europe/Riga";s:1:"3";s:11:"Asia/Riyadh";s:1:"3";s:12:"Asia/Bahrain";s:1:"3";s:16:"Antarctica/Syowa";s:1:"3";s:14:"Asia/Jerusalem";s:1:"3";s:12:"Europe/Sofia";s:1:"3";s:13:"Indian/Comoro";s:1:"3";s:12:"Asia/Baghdad";s:1:"3";s:15:"Africa/Djibouti";s:1:"3";s:10:"Asia/Amman";s:1:"3";s:14:"Indian/Mayotte";s:1:"3";s:18:"Africa/Addis Ababa";s:1:"3";s:9:"Asia/Aden";s:1:"3";s:11:"Asia/Beirut";s:1:"3";s:13:"Asia/Damascus";s:1:"3";s:13:"Africa/Asmara";s:1:"3";s:9:"Asia/Gaza";s:1:"3";s:12:"Africa/Cairo";s:1:"3";s:19:"Indian/Antananarivo";s:1:"3";s:20:"Africa/Dar es Salaam";s:1:"3";s:14:"Africa/Nairobi";s:1:"3";s:12:"Asia/Nicosia";s:1:"3";s:14:"Europe/Tallinn";s:1:"3";s:16:"Africa/Mogadishu";s:1:"3";s:14:"Africa/Kampala";s:1:"3";s:14:"Europe/Vilnius";s:1:"3";s:17:"Europe/Zaporozhye";s:1:"3";s:11:"Asia/Kuwait";s:1:"3";s:10:"Asia/Qatar";s:1:"3";s:15:"Europe/Uzhgorod";s:1:"3";s:15:"Africa/Khartoum";s:1:"3";s:10:"Asia/Dubai";s:1:"4";s:14:"Indian/Reunion";s:1:"4";s:11:"Indian/Mahe";s:1:"4";s:16:"Europe/Volgograd";s:1:"4";s:11:"Asia/Muscat";s:1:"4";s:13:"Europe/Moscow";s:1:"4";s:16:"Indian/Mauritius";s:1:"4";s:12:"Asia/Tbilisi";s:1:"4";s:11:"Asia/Tehran";s:3:"4.5";s:10:"Asia/Kabul";s:3:"4.5";s:10:"Asia/Aqtau";s:1:"5";s:16:"Antarctica/Davis";s:1:"5";s:11:"Asia/Aqtobe";s:1:"5";s:17:"Antarctica/Mawson";s:1:"5";s:15:"Indian/Maldives";s:1:"5";s:13:"Asia/Ashgabat";s:1:"5";s:12:"Asia/Yerevan";s:1:"5";s:13:"Asia/Tashkent";s:1:"5";s:13:"Europe/Samara";s:1:"5";s:9:"Asia/Oral";s:1:"5";s:14:"Asia/Samarkand";s:1:"5";s:9:"Asia/Baku";s:1:"5";s:16:"Indian/Kerguelen";s:1:"5";s:13:"Asia/Dushanbe";s:1:"5";s:12:"Asia/Kolkata";s:3:"5.5";s:12:"Asia/Colombo";s:3:"5.5";s:14:"Asia/Kathmandu";s:4:"5.75";s:13:"Indian/Chagos";s:1:"6";s:11:"Asia/Almaty";s:1:"6";s:18:"Asia/Yekaterinburg";s:1:"6";s:14:"Asia/Qyzylorda";s:1:"6";s:12:"Asia/Thimphu";s:1:"6";s:12:"Asia/Karachi";s:1:"6";s:12:"Asia/Bishkek";s:1:"6";s:17:"Antarctica/Vostok";s:1:"6";s:12:"Indian/Cocos";s:3:"6.5";s:12:"Asia/Rangoon";s:3:"6.5";s:9:"Asia/Omsk";s:1:"7";s:15:"Asia/Phnom Penh";s:1:"7";s:14:"Asia/Pontianak";s:1:"7";s:12:"Asia/Bangkok";s:1:"7";s:17:"Asia/Novokuznetsk";s:1:"7";s:16:"Asia/Ho Chi Minh";s:1:"7";s:9:"Asia/Hovd";s:1:"7";s:12:"Asia/Jakarta";s:1:"7";s:10:"Asia/Dhaka";s:1:"7";s:16:"Asia/Novosibirsk";s:1:"7";s:16:"Indian/Christmas";s:1:"7";s:14:"Asia/Vientiane";s:1:"7";s:10:"Asia/Macau";s:1:"8";s:13:"Asia/Makassar";s:1:"8";s:12:"Asia/Kuching";s:1:"8";s:11:"Asia/Manila";s:1:"8";s:17:"Asia/Kuala Lumpur";s:1:"8";s:14:"Asia/Hong Kong";s:1:"8";s:11:"Asia/Harbin";s:1:"8";s:12:"Asia/Kashgar";s:1:"8";s:16:"Asia/Krasnoyarsk";s:1:"8";s:15:"Asia/Choibalsan";s:1:"8";s:14:"Asia/Chongqing";s:1:"8";s:11:"Asia/Brunei";s:1:"8";s:13:"Asia/Shanghai";s:1:"8";s:14:"Asia/Singapore";s:1:"8";s:11:"Asia/Taipei";s:1:"8";s:16:"Asia/Ulaanbaatar";s:1:"8";s:15:"Australia/Perth";s:1:"8";s:11:"Asia/Urumqi";s:1:"8";s:15:"Australia/Eucla";s:4:"8.75";s:13:"Asia/Jayapura";s:1:"9";s:12:"Asia/Irkutsk";s:1:"9";s:10:"Asia/Tokyo";s:1:"9";s:13:"Pacific/Palau";s:1:"9";s:9:"Asia/Dili";s:1:"9";s:10:"Asia/Seoul";s:1:"9";s:14:"Asia/Pyongyang";s:1:"9";s:16:"Australia/Darwin";s:3:"9.5";s:21:"Australia/Broken Hill";s:3:"9.5";s:18:"Australia/Adelaide";s:3:"9.5";s:18:"Australia/Lindeman";s:2:"10";s:12:"Pacific/Guam";s:2:"10";s:12:"Asia/Yakutsk";s:2:"10";s:19:"Australia/Melbourne";s:2:"10";s:20:"Pacific/Port Moresby";s:2:"10";s:14:"Pacific/Saipan";s:2:"10";s:18:"Australia/Brisbane";s:2:"10";s:25:"Antarctica/DumontDUrville";s:2:"10";s:16:"Australia/Currie";s:2:"10";s:16:"Australia/Sydney";s:2:"10";s:12:"Pacific/Truk";s:2:"10";s:16:"Australia/Hobart";s:2:"10";s:19:"Australia/Lord Howe";s:4:"10.5";s:16:"Antarctica/Casey";s:2:"11";s:16:"Asia/Vladivostok";s:2:"11";s:13:"Asia/Sakhalin";s:2:"11";s:19:"Pacific/Guadalcanal";s:2:"11";s:14:"Pacific/Ponape";s:2:"11";s:14:"Pacific/Noumea";s:2:"11";s:13:"Pacific/Efate";s:2:"11";s:14:"Pacific/Kosrae";s:2:"11";s:15:"Pacific/Norfolk";s:4:"11.5";s:14:"Pacific/Tarawa";s:2:"12";s:21:"Antarctica/South Pole";s:2:"12";s:18:"Antarctica/McMurdo";s:2:"12";s:16:"Pacific/Auckland";s:2:"12";s:16:"Pacific/Funafuti";s:2:"12";s:12:"Pacific/Fiji";s:2:"12";s:12:"Asia/Magadan";s:2:"12";s:12:"Pacific/Wake";s:2:"12";s:14:"Pacific/Wallis";s:2:"12";s:13:"Pacific/Nauru";s:2:"12";s:14:"Pacific/Majuro";s:2:"12";s:17:"Pacific/Kwajalein";s:2:"12";s:15:"Pacific/Chatham";s:5:"12.75";s:14:"Asia/Kamchatka";s:2:"13";s:17:"Pacific/Enderbury";s:2:"13";s:11:"Asia/Anadyr";s:2:"13";s:17:"Pacific/Tongatapu";s:2:"13";s:18:"Pacific/Kiritimati";s:2:"14";}'),
('languages', 'a:1:{i:1;a:5:{s:2:"id";s:1:"1";s:5:"title";s:7:"English";s:8:"isotitle";s:2:"en";s:7:"default";s:1:"1";s:7:"charset";s:5:"UTF-8";}}'),
('phrasegroups', 'a:1:{i:1;a:1:{i:0;s:6:"global";}}');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `isotitle` varchar(5) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `charset` enum('UTF-8','ISO-8859-1') NOT NULL DEFAULT 'UTF-8',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Data dump for tabellen `languages`
--

INSERT INTO `languages` (`id`, `title`, `isotitle`, `default`, `charset`) VALUES
(1, 'English', 'en', 1, 'UTF-8');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `options`
--

CREATE TABLE IF NOT EXISTS `options` (
  `option` varchar(128) NOT NULL,
  `value` mediumtext NOT NULL,
  `type` enum('s','i','b') NOT NULL,
  UNIQUE KEY `option` (`option`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Data dump for tabellen `options`
--

INSERT INTO `options` (`option`, `value`, `type`) VALUES
('style_id', '1', 'i'),
('cookie_domain', '', 's'),
('cookie_path', '', 's'),
('cookie_expires', '1800', 'i'),
('cookie_prefix', 'tuxxedo_', 's'),
('date_format', 'H:i:s, j/n - Y', 's'),
('date_timezone', 'UTC', 's'),
('date_timezone_offset', '0', 'i'),
('language_id', '1', 'i'),
('style_storage', 'database', 's');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `phrasegroups`
--

CREATE TABLE IF NOT EXISTS `phrasegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `language` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Data dump for tabellen `phrasegroups`
--

INSERT INTO `phrasegroups` (`id`, `title`, `language`) VALUES
(1, 'global', 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `phrases`
--

CREATE TABLE IF NOT EXISTS `phrases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `translation` text NOT NULL,
  `languageid` int(11) NOT NULL,
  `phrasegroup` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Data dump for tabellen `phrases`
--


-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `sessionid` varchar(32) NOT NULL,
  `userid` int(11) NOT NULL,
  `location` tinytext NOT NULL,
  `lastactivity` int(11) NOT NULL,
  UNIQUE KEY `session` (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Data dump for tabellen `sessions`
--

INSERT INTO `sessions` (`sessionid`, `userid`, `location`, `lastactivity`) VALUES
('kiaee8hlem60c9ukealp8abc95', 0, '/engine/index.php', 1274068055);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `styles`
--

CREATE TABLE IF NOT EXISTS `styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `developer` varchar(128) NOT NULL,
  `styledir` varchar(128) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Data dump for tabellen `styles`
--

INSERT INTO `styles` (`id`, `name`, `developer`, `styledir`, `default`) VALUES
(1, 'Default', 'Tuxxedo Software Development', 'default', 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `templates`
--

CREATE TABLE IF NOT EXISTS `templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `source` text NOT NULL,
  `compiledsource` text NOT NULL,
  `defaultsource` text NOT NULL,
  `styleid` int(11) NOT NULL,
  `changed` enum('0','1') NOT NULL,
  `revision` int(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Data dump for tabellen `templates`
--

INSERT INTO `templates` (`id`, `title`, `source`, `compiledsource`, `defaultsource`, `styleid`, `changed`, `revision`) VALUES
(1, 'header', '<?xml version="1.0" encoding="UTF-8"?>\r\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml">\r\n<head>\r\n	<title>Tuxxedo Software Engine</title>\r\n\r\n	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\r\n</head>\r\n<body>', '<?xml version=\\"1.0\\" encoding=\\"UTF-8\\"?>\r\n<!DOCTYPE html PUBLIC \\"-//W3C//DTD XHTML 1.0 Strict//EN\\" \\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\\">\r\n<html xmlns=\\"http://www.w3.org/1999/xhtml\\">\r\n<head>\r\n	<title>Tuxxedo Software Engine</title>\r\n\r\n	<meta http-equiv=\\"Content-Type\\" content=\\"text/html; charset=utf-8\\" />\r\n</head>\r\n<body>', '<?xml version="1.0" encoding="UTF-8"?>\r\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\r\n<html xmlns="http://www.w3.org/1999/xhtml">\r\n<head>\r\n	<title>Tuxxedo Software Engine</title>\r\n\r\n	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\r\n</head>\r\n<body>', 1, '0', 1),
(2, 'footer', '</body>\r\n</html>', '</body>\r\n</html>', '</body>\r\n</html>', 1, '0', 1),
(3, 'redirect', '{$header}\r\n\r\n<h2>Redirecting ...</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n<p>\r\n	<em>\r\n	If you are not redirected within a few seconds, then \r\n	click <a href="{$location}">here</a> to be redirected \r\n	manually.\r\n	</em>\r\n</p>\r\n\r\n<script type="text/javascript">\r\n<!--\r\nsetTimeout(''location.href="{$location}";'', {$timeout} * 1000);\r\n// -->\r\n</script>\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Redirecting ...</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n<p>\r\n	<em>\r\n	If you are not redirected within a few seconds, then \r\n	click <a href=\\"{$location}\\">here</a> to be redirected \r\n	manually.\r\n	</em>\r\n</p>\r\n\r\n<script type=\\"text/javascript\\">\r\n<!--\r\nsetTimeout(''location.href=\\"{$location}\\";'', {$timeout} * 1000);\r\n// -->\r\n</script>\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Redirecting ...</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n<p>\r\n	<em>\r\n	If you are not redirected within a few seconds, then \r\n	click <a href="{$location}">here</a> to be redirected \r\n	manually.\r\n	</em>\r\n</p>\r\n\r\n<script type="text/javascript">\r\n<!--\r\nsetTimeout(''location.href="{$location}";'', {$timeout} * 1000);\r\n// -->\r\n</script>\r\n\r\n{$footer}', 1, '0', 1),
(4, 'error', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n\r\n<input type="button" onclick="history.back(-1);" value="Go back" />\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n\r\n<input type=\\"button\\" onclick=\\"history.back(-1);\\" value=\\"Go back\\" />\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	{$message}\r\n</p>\r\n\r\n<input type="button" onclick="history.back(-1);" value="Go back" />\r\n\r\n{$footer}', 1, '0', 1),
(5, 'error_validation', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	The following fields failed to validate, please go back and fill in the correct values.\r\n</p>\r\n\r\n<ul>\r\n{$list}\r\n</ul>\r\n\r\n<input type="button" onclick="history.back(-1);" value="Go back" />\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	The following fields failed to validate, please go back and fill in the correct values.\r\n</p>\r\n\r\n<ul>\r\n{$list}\r\n</ul>\r\n\r\n<input type=\\"button\\" onclick=\\"history.back(-1);\\" value=\\"Go back\\" />\r\n\r\n{$footer}', '{$header}\r\n\r\n<h2>Error</h2>\r\n<p>\r\n	The following fields failed to validate, please go back and fill in the correct values.\r\n</p>\r\n\r\n<ul>\r\n{$list}\r\n</ul>\r\n\r\n<input type="button" onclick="history.back(-1);" value="Go back" />\r\n\r\n{$footer}', 1, '0', 1),
(6, 'error_validationbit', '<li>{$name}</li>', '<li>{$name}</li>', '<li>{$name}</li>', 1, '0', 1);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `usergroups`
--

CREATE TABLE IF NOT EXISTS `usergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(127) NOT NULL,
  `type` enum('1','2') NOT NULL,
  `permissions` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Data dump for tabellen `usergroups`
--

INSERT INTO `usergroups` (`id`, `title`, `type`, `permissions`) VALUES
(1, 'Administrators', '1', 0),
(2, 'Regular users', '2', 0);

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `usergroupid` int(11) NOT NULL,
  `salt` varchar(8) NOT NULL,
  `style_id` int(5) DEFAULT NULL,
  `language_id` int(5) DEFAULT NULL,
  `timezone` tinytext NOT NULL,
  `timezone_offset` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Data dump for tabellen `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `name`, `password`, `usergroupid`, `salt`, `style_id`, `language_id`, `timezone`, `timezone_offset`) VALUES
(1, 'Tuxxedo', 'blackhole@tuxxedo.net', 'Tuxxedo Test Account', 'cc95587719affd4460394d1a0311d1c11040fe69', 1, '%-C?;_wj', NULL, NULL, 'UTC', 0);
