CREATE TABLE `customshortenedurls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_code` varchar(8) COLLATE utf8_bin NOT NULL,
  `long_url` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `short_code_UNIQUE` (`short_code`) USING BTREE,
  UNIQUE KEY `long_url_UNIQUE` (`long_url`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `shortenedurls` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `long_url` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `creator` char(15) COLLATE utf8_bin NOT NULL,
  `short_code` varchar(6) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `long` (`long_url`),
  UNIQUE KEY `shortenedurls_short_code_uindex` (`short_code`),
  KEY `short_code` (`short_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `track` (
  `short_code` varchar(255) COLLATE utf8_bin NOT NULL,
  `visits` int(11) DEFAULT '0',
  PRIMARY KEY (`short_code`),
  UNIQUE KEY `short_code_UNIQUE` (`short_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

