-- Create syntax for TABLE 'deletedids'
CREATE TABLE `deletedids` (
  `md5` binary(16) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  PRIMARY KEY (`md5`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'followers'
CREATE TABLE `followers` (
  `id` int(10) unsigned NOT NULL,
  `followid` int(10) unsigned NOT NULL,
  `followidraw` binary(8) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `expires` timestamp NULL DEFAULT NULL,
  `delay` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'issuedids'
CREATE TABLE `issuedids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `md5` binary(16) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `type` enum('share','follow','deleted') NOT NULL DEFAULT 'deleted',
  `config` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5` (`md5`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'locations'
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `location` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`id`) REFERENCES `issuedids` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;