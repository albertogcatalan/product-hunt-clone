SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ph-clone`
--

-- --------------------------------------------------------

--
-- Table structure for table `lang`
--

DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `slug` varchar(3) NOT NULL DEFAULT '',
  `tld` varchar(8) DEFAULT NULL,
  `locale` varchar(5) NOT NULL DEFAULT '',
  `customerLanguage` varchar(3) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL,
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `lang`
--

INSERT INTO `lang` (`id`, `name`, `slug`, `tld`, `locale`, `customerLanguage`, `active`, `priority`) VALUES
(1, 'English', 'eng', '.com', 'en_EN', '001', 1, 1),
(2, 'Español', 'esp', '.es', 'es_ES', '002', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `lang_strings`
--

DROP TABLE IF EXISTS `lang_strings`;
CREATE TABLE `lang_strings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `language` int(11) NOT NULL,
  `label` varchar(64) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL DEFAULT 'basic',
  `slug` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(128) NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `added` int(11) unsigned NOT NULL,
  `started` datetime NOT NULL,
  `active` int(1) unsigned NOT NULL DEFAULT '0',
  `cancelled` int(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_comments`
--

DROP TABLE IF EXISTS `project_comments`;
CREATE TABLE `project_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `language` int(11) NOT NULL,
  `replyTo` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_points`
--

DROP TABLE IF EXISTS `project_points`;
CREATE TABLE `project_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`id`,`project`,`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_strings`
--

DROP TABLE IF EXISTS `project_strings`;
CREATE TABLE `project_strings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(11) NOT NULL,
  `language` int(11) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `token` text NOT NULL,
  `secret` varchar(255) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `level` varchar(8) NOT NULL DEFAULT 'user',
  `email` varchar(255) DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `account` varchar(100) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `biography` text,
  `location` varchar(255) DEFAULT NULL,
  `web_link` varchar(255) DEFAULT NULL,
  `registered` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `language` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
