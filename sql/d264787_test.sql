-- Adminer 4.8.1 MySQL 8.0.33-0ubuntu0.22.04.2 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` smallint NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_main` smallint NOT NULL DEFAULT '1' COMMENT 'Vlastník zariadenia',
  `name` varchar(100) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Meno zariadenia',
  `device_name` varchar(20) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Komunikačné meno',
  `description` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Popis',
  `first_login` datetime DEFAULT NULL COMMENT 'Prvé prihlásenia',
  `last_login` datetime DEFAULT NULL COMMENT 'Posladné prihlásenie',
  `password` varchar(64) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Heslo komunikačné',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `device_name` (`device_name`),
  KEY `id_user_main` (`id_user_main`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='List of devices. Device has one or more Sensors.';

INSERT INTO `devices` (`id`, `id_user_main`, `name`, `device_name`, `description`, `first_login`, `last_login`, `password`) VALUES
(1,	1,	'Záhradka BMP 280',	'zahradka_bmp_280',	'Meteo na záhradke',	'2023-07-19 13:52:00',	'2023-07-19 14:25:00',	NULL),
(2,	1,	'DHT 11 doma',	'dht_11_doma',	'Testovací teplomer doma',	'2023-06-21 12:17:06',	'2023-07-19 14:25:01',	'KluKfn48d');

DROP TABLE IF EXISTS `main_menu`;
CREATE TABLE `main_menu` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `name` varchar(30) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Názov položky',
  `web_name` varchar(50) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Webový názov položky',
  `link` varchar(50) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Odkaz',
  `icon` varchar(70) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Ikonka z fontawesome (komplet class)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci;

INSERT INTO `main_menu` (`id`, `name`, `web_name`, `link`, `icon`) VALUES
(1,	'Domov',	'home',	'Home:',	'fa-solid fa-house'),
(2,	'Zariadenia',	'devices',	'Device:devices',	'fa-solid fa-walkie-talkie'),
(3,	'Merania',	'measures',	'Home:measures',	'fa-solid fa-scale-unbalanced-flip');

DROP TABLE IF EXISTS `measures`;
CREATE TABLE `measures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_sensor` smallint NOT NULL,
  `data_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'timestamp of data recording',
  `server_time` datetime DEFAULT NULL COMMENT 'timestamp where data has been received by server',
  `s_value` double NOT NULL COMMENT 'data measured (raw)',
  `out_value` double DEFAULT NULL COMMENT 'processed value',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0 = received, 1 = processed, 2 = exported',
  PRIMARY KEY (`id`),
  KEY `id_sensor` (`id_sensor`),
  CONSTRAINT `measures_ibfk_1` FOREIGN KEY (`id_sensor`) REFERENCES `sensors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Recorded data - raw. SUMDATA are created from recorded data, and old data are deleted from MEASURES.';


DROP TABLE IF EXISTS `sensors`;
CREATE TABLE `sensors` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `id_devices` smallint NOT NULL,
  `name` varchar(100) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL,
  `id_value_type` int NOT NULL,
  `description` varchar(256) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `last_data_time` datetime DEFAULT NULL,
  `last_out_value` double DEFAULT NULL,
  `imp_count` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_device_name` (`id_devices`,`name`),
  KEY `id_value_type` (`id_value_type`),
  CONSTRAINT `sensors_ibfk_2` FOREIGN KEY (`id_value_type`) REFERENCES `value_types` (`id`),
  CONSTRAINT `sensors_ibfk_3` FOREIGN KEY (`id_devices`) REFERENCES `devices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='List of sensors. Each sensor is a part of one DEVICE.';

INSERT INTO `sensors` (`id`, `id_devices`, `name`, `id_value_type`, `description`, `last_data_time`, `last_out_value`, `imp_count`) VALUES
(1,	1,	'Teplota',	1,	'Teplota vonku',	'2023-07-19 14:25:00',	28.159999847412,	NULL),
(2,	1,	'Vlhkosť',	2,	'Vlhkosť vonku',	'2023-07-19 14:25:00',	40.1162109375,	NULL),
(3,	1,	'Tlak',	3,	'Tlak relatívny',	'2023-07-19 14:25:00',	1014.9859008789,	NULL),
(4,	1,	'Osvetlenie',	4,	'Intenzita osvetlenia',	'2023-07-19 14:25:00',	0,	NULL),
(5,	2,	'Teplota',	1,	'Teplota dht 11',	'2023-07-19 14:25:01',	28.799999237061,	NULL),
(6,	2,	'Vlhkosť',	2,	'Vlhkosť dht 11',	'2023-07-19 14:25:01',	43,	NULL);

DROP TABLE IF EXISTS `user_main`;
CREATE TABLE `user_main` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `phash` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL,
  `id_user_profiles` int DEFAULT NULL COMMENT 'Profil užívateľa',
  `id_user_roles` int NOT NULL DEFAULT '1' COMMENT 'Rola užívateľa',
  `email` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL,
  `activated` tinyint NOT NULL DEFAULT '0' COMMENT 'Aktivácia',
  `banned` tinyint NOT NULL DEFAULT '0' COMMENT 'Blokovaný',
  `ban_reason` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Dôvod blokovania',
  `last_ip` varchar(40) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Posledná IP adresa',
  `bad_pwds_count` smallint NOT NULL DEFAULT '0',
  `locked_out_until` datetime DEFAULT NULL,
  `measures_retention` int NOT NULL DEFAULT '90' COMMENT 'jak dlouho se drží data v measures',
  `sumdata_retention` int NOT NULL DEFAULT '731' COMMENT 'jak dlouho se drží data v sumdata',
  `cur_login_time` datetime DEFAULT NULL,
  `cur_login_ip` varchar(32) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `cur_login_browser` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `prev_login_time` datetime DEFAULT NULL,
  `prev_login_ip` varchar(32) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `prev_login_browser` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `last_error_time` datetime DEFAULT NULL,
  `last_error_ip` varchar(32) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `last_error_browser` varchar(255) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `monitoring_token` varchar(100) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL,
  `new_password_key` varchar(100) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Kľúč nového hesla',
  `new_password_requested` datetime DEFAULT NULL COMMENT 'Čas požiadavky na nové heslo',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_user_profiles` (`id_user_profiles`),
  CONSTRAINT `user_main_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `user_main_ibfk_2` FOREIGN KEY (`id_user_profiles`) REFERENCES `user_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Hlavné údaje užívateľa';

INSERT INTO `user_main` (`id`, `phash`, `id_user_profiles`, `id_user_roles`, `email`, `activated`, `banned`, `ban_reason`, `last_ip`, `bad_pwds_count`, `locked_out_until`, `measures_retention`, `sumdata_retention`, `cur_login_time`, `cur_login_ip`, `cur_login_browser`, `prev_login_time`, `prev_login_ip`, `prev_login_browser`, `last_error_time`, `last_error_ip`, `last_error_browser`, `monitoring_token`, `new_password_key`, `new_password_requested`) VALUES
(1,	'$2y$10$zhXEnAGvOnDiYcTQVJvg..85n2XbnZzLM0Ef68qS.chxzT6U/P4tu',	1,	4,	'petak23@echo-msz.eu',	1,	0,	NULL,	'192.168.0.123',	0,	NULL,	90,	731,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `id_user_roles` int NOT NULL DEFAULT '0' COMMENT 'Užívateľská rola',
  `id_user_resource` int NOT NULL COMMENT 'Zdroj oprávnenia',
  `actions` varchar(100) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Povolenie na akciu. (Ak viac oddelené čiarkou, ak null tak všetko)',
  PRIMARY KEY (`id`),
  KEY `id_user_roles` (`id_user_roles`),
  KEY `id_user_resource` (`id_user_resource`),
  CONSTRAINT `user_permission_ibfk_1` FOREIGN KEY (`id_user_roles`) REFERENCES `user_roles` (`id`),
  CONSTRAINT `user_permission_ibfk_2` FOREIGN KEY (`id_user_resource`) REFERENCES `user_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Užívateľské oprávnenia';

INSERT INTO `user_permission` (`id`, `id_user_roles`, `id_user_resource`, `actions`) VALUES
(1,	1,	1,	NULL),
(2,	1,	2,	'default'),
(3,	3,	2,	NULL),
(4,	4,	3,	NULL),
(5,	1,	4,	NULL),
(6,	3,	5,	NULL),
(7,	1,	6,	NULL),
(8,	1,	7,	NULL),
(9,	4,	8,	NULL),
(10,	3,	9,	NULL);

DROP TABLE IF EXISTS `user_prihlasenie`;
CREATE TABLE `user_prihlasenie` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '[A]Index',
  `id_user_main` smallint NOT NULL COMMENT 'Id užívateľa',
  `log_in_datetime` datetime DEFAULT NULL COMMENT 'Dátum a čas prihlásenia',
  PRIMARY KEY (`id`),
  KEY `id_user_main` (`id_user_main`),
  CONSTRAINT `user_prihlasenie_ibfk_1` FOREIGN KEY (`id_user_main`) REFERENCES `user_main` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Evidencia prihlásenia užívateľov';

INSERT INTO `user_prihlasenie` (`id`, `id_user_main`, `log_in_datetime`) VALUES
(1,	1,	'2023-06-26 13:10:35'),
(2,	1,	'2023-06-26 13:10:35'),
(3,	1,	'2023-06-26 13:15:23'),
(4,	1,	'2023-06-27 16:24:33'),
(5,	1,	'2023-06-27 16:26:43'),
(6,	1,	'2023-06-27 16:30:42'),
(7,	1,	'2023-06-29 15:49:05'),
(8,	1,	'2023-06-29 16:44:08'),
(9,	1,	'2023-06-30 17:55:17'),
(10,	1,	'2023-07-01 20:16:45'),
(11,	1,	'2023-07-04 06:29:32'),
(12,	1,	'2023-07-04 13:11:01'),
(13,	1,	'2023-07-07 13:52:17'),
(14,	1,	'2023-07-07 19:19:27'),
(15,	1,	'2023-07-17 13:23:49'),
(16,	1,	'2023-07-17 15:36:40'),
(17,	1,	'2023-07-19 13:41:02');

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rok` int DEFAULT NULL COMMENT 'Rok narodenia',
  `telefon` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Telefón',
  `poznamka` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Poznámka',
  `pocet_pr` int NOT NULL DEFAULT '0' COMMENT 'Počet prihlásení',
  `pohl` enum('Z','M') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'M' COMMENT 'Pohlavie',
  `prihlas_teraz` datetime DEFAULT NULL COMMENT 'Posledné prihlásenie',
  `avatar` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Cesta k avatarovi veľkosti 75x75',
  `news` enum('A','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'A' COMMENT 'Posielanie info emailou',
  `news_key` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL COMMENT 'Kľúč pre odhlásenie noviniek',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

INSERT INTO `user_profiles` (`id`, `rok`, `telefon`, `poznamka`, `pocet_pr`, `pohl`, `prihlas_teraz`, `avatar`, `news`, `news_key`) VALUES
(1,	NULL,	NULL,	NULL,	18,	'M',	'2023-07-19 13:41:02',	NULL,	'N',	NULL);

DROP TABLE IF EXISTS `user_resource`;
CREATE TABLE `user_resource` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Index',
  `name` varchar(30) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Názov zdroja',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Zdroje oprávnení';

INSERT INTO `user_resource` (`id`, `name`) VALUES
(1,	'Sign'),
(2,	'Home'),
(3,	'User'),
(4,	'Crontask'),
(5,	'Device'),
(6,	'Error4xx'),
(7,	'Error'),
(8,	'UserAcl'),
(9,	'Units');

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `id` int NOT NULL COMMENT 'Index',
  `role` varchar(30) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL DEFAULT 'guest' COMMENT 'Rola pre ACL',
  `inherited` varchar(30) CHARACTER SET utf32 COLLATE utf32_slovak_ci DEFAULT NULL COMMENT 'Dedí od roli',
  `name` varchar(80) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL DEFAULT 'Registracia cez web' COMMENT 'Názov úrovne registrácie',
  `color` varchar(15) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL DEFAULT 'fff' COMMENT 'Farba pozadia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Úrovne registrácie a ich názvy';

INSERT INTO `user_roles` (`id`, `role`, `inherited`, `name`, `color`) VALUES
(1,	'guest',	NULL,	'Bez registrácie',	'fff'),
(2,	'register',	'guest',	'Registrovaný ale neaktivovaný užívateľ',	'fffc29'),
(3,	'active',	'register',	'Aktivovaný užívateľ',	'7ce300'),
(4,	'admin',	'active',	'Administrátor',	'ff6a6a');

DROP TABLE IF EXISTS `value_types`;
CREATE TABLE `value_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit` varchar(20) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Jednotka',
  `shortcut` varchar(5) CHARACTER SET utf32 COLLATE utf32_slovak_ci NOT NULL COMMENT 'Skratka',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_slovak_ci COMMENT='Units for any kind of recorder values.';

INSERT INTO `value_types` (`id`, `unit`, `shortcut`) VALUES
(1,	'°C',	'TE'),
(2,	'%',	'HU'),
(3,	'hPa',	'RP'),
(4,	'lx',	'LX');

-- 2023-07-19 12:25:53
