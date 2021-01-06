-- Create syntax for TABLE 'issuedids'
CREATE TABLE `issuedids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` binary(16) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `type` enum('share','follow','deleted','reserved') NOT NULL DEFAULT 'deleted',
  `config` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'followers'
CREATE TABLE `followers` (
  `shareid` int(10) unsigned NOT NULL,
  `followid` int(10) unsigned NOT NULL,
  `followidencrypted` binary(24) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `expires` timestamp NULL DEFAULT NULL,
  `delay` time DEFAULT NULL,
  UNIQUE KEY `followid` (`followid`),
  KEY `shareid` (`shareid`),
  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`shareid`) REFERENCES `issuedids` (`id`),
  CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`followid`) REFERENCES `issuedids` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'deletedids'
CREATE TABLE `deletedids` (
  `hash` binary(16) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'locations'
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `location` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`id`) REFERENCES `issuedids` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;