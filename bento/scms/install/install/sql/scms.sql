SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `account`
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL default '1',
  `gender_id` int(10) unsigned NOT NULL default '1',
  `facebook_id` int(10) unsigned NOT NULL,
  `facebook_token` varchar(255) NOT NULL,
  `facebook_secret` varchar(255) NOT NULL,
  `twitter_id` int(10) unsigned NOT NULL,
  `twitter_token` varchar(255) NOT NULL,
  `twitter_secret` varchar(255) NOT NULL,
  `openid_id` int(10) NOT NULL,
  `openid_token` varchar(255) NOT NULL,
  `openid_secret` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(10) NOT NULL,
  `confirm` tinyint(1) unsigned NOT NULL default '0',
  `state` tinyint(4) NOT NULL default '1',
  `name_first` varchar(255) NOT NULL,
  `name_last` varchar(255) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `birthday` int(10) unsigned NOT NULL,
  `extension` varchar(4) NOT NULL,
  `date_create` int(10) unsigned NOT NULL default '0',
  `date_login` int(10) NOT NULL,
  `date_reminder` int(10) NOT NULL,
  `date_edit` int(10) unsigned NOT NULL default '0',
  `zone` varchar(255) NOT NULL default 'America/Edmonton',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id` (`id`),
  KEY `gender_id` (`gender_id`),
  KEY `account_id` (`account_id`),
  KEY `account_id_2` (`account_id`),
  KEY `account_id_3` (`account_id`),
  CONSTRAINT `account_ibfk_1` FOREIGN KEY (`gender_id`) REFERENCES `gender` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='InnoDB free: 32768 kB; (`gender_id`) REFER `mywavu/gender`(`';

-- ----------------------------
--  Records of `account`
-- ----------------------------
BEGIN;
INSERT INTO `account` VALUES ('1', '1', '1', '0', '', '', '0', '', '', '0', '', '', '', '', '', '0', '1', '', '', '', '0', '', '0', '0', '0', '0', 'America/Edmonton'), ('2', '1', '1', '0', '', '', '0', '', '', '0', '', '', '', '', '', '1', '1', '', '', '', '0', '', '0', '0', '0', '0', 'America/Edmonton');
COMMIT;

-- ----------------------------
--  Table structure for `account_mail_x`
-- ----------------------------
DROP TABLE IF EXISTS `account_mail_x`;
CREATE TABLE `account_mail_x` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `mail_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`),
  KEY `mail_id` (`mail_id`),
  CONSTRAINT `account_mail_x_ibfk_1` FOREIGN KEY (`mail_id`) REFERENCES `mail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `account_mail_x_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `account_meta`
-- ----------------------------
DROP TABLE IF EXISTS `account_meta`;
CREATE TABLE `account_meta` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `account_meta_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
--  Table structure for `account_permission_x`
-- ----------------------------
DROP TABLE IF EXISTS `account_permission_x`;
CREATE TABLE `account_permission_x` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `client_id` (`permission_id`),
  KEY `user_id` (`account_id`),
  CONSTRAINT `account_permission_x_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `account_permission_x_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `account_permission_x`
-- ----------------------------
BEGIN;
INSERT INTO `account_permission_x` VALUES ('127', '2', '1');
COMMIT;

-- ----------------------------
--  Table structure for `content`
-- ----------------------------
DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `table` varchar(255) NOT NULL,
  `fields` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `content`
-- ----------------------------
BEGIN;
INSERT INTO `content` VALUES ('1', 'Page', 'page', 'name,title,description');
COMMIT;

-- ----------------------------
--  Table structure for `event`
-- ----------------------------
DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `session_id` varchar(255) NOT NULL,
  `parent_id` varchar(255) NOT NULL,
  `plugin` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `state` int(11) NOT NULL default '0' COMMENT '0 queue, 1 ready for firing',
  `date_insert` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `session_id` (`session_id`,`parent_id`,`plugin`,`method`)
) ENGINE=InnoDB AUTO_INCREMENT=2251 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `gender`
-- ----------------------------
DROP TABLE IF EXISTS `gender`;
CREATE TABLE `gender` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `noun` varchar(255) NOT NULL,
  `pronoun` varchar(255) NOT NULL,
  `singular` varchar(255) NOT NULL default 'a',
  `slang` varchar(255) NOT NULL,
  `objective` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `gender`
-- ----------------------------
BEGIN;
INSERT INTO `gender` VALUES ('1', 'them', 'E.T.', 'their', 'a', 'person', 'their'), ('2', 'he', 'Male', 'his', 'a', 'brother', 'him'), ('3', 'she', 'Female', 'her', 'a', 'sister', 'her');
COMMIT;

-- ----------------------------
--  Table structure for `mail`
-- ----------------------------
DROP TABLE IF EXISTS `mail`;
CREATE TABLE `mail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mail` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `mail`
-- ----------------------------
BEGIN;
INSERT INTO `mail` VALUES ('1', 'Forgot You Password', 'forgot', 'main', 'We\'ve Found Your Password!', '', ''), ('2', 'Register Your Account', 'register', 'test', 'Welcome!', '', ''), ('3', 'Setup', 'setup', 'main', 'Setup', '', '');
COMMIT;

-- ----------------------------
--  Table structure for `notification`
-- ----------------------------
DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL default '1',
  `plugin` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `state` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `page`
-- ----------------------------
DROP TABLE IF EXISTS `page`;
CREATE TABLE `page` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '3',
  `priority` int(10) unsigned NOT NULL default '0',
  `plugin` varchar(255) NOT NULL default 'scms',
  `theme` varchar(255) NOT NULL default 'blank',
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `anchor` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `web` tinyint(4) unsigned NOT NULL default '1',
  `mobile` tinyint(4) unsigned NOT NULL default '1',
  `app` tinyint(4) unsigned NOT NULL default '1',
  `facebook` tinyint(4) unsigned NOT NULL default '1',
  `kiosk` tinyint(4) unsigned NOT NULL default '1',
  `narrowcast` tinyint(4) unsigned NOT NULL default '1',
  `home_web` tinyint(1) unsigned NOT NULL default '0',
  `home_mobile` tinyint(1) unsigned NOT NULL default '0',
  `home_app` tinyint(1) NOT NULL default '0',
  `home_facebook` tinyint(1) unsigned NOT NULL default '0',
  `home_narrowcast` tinyint(1) NOT NULL default '0',
  `home_kiosk` tinyint(1) NOT NULL default '0',
  `template_web` varchar(255) NOT NULL default 'main',
  `template_mobile` varchar(255) NOT NULL default 'main',
  `template_app` varchar(255) NOT NULL default 'main',
  `template_facebook` varchar(255) NOT NULL default 'main',
  `template_narrowcast` varchar(255) NOT NULL default 'main',
  `template_kiosk` varchar(255) NOT NULL default 'main',
  `admin` tinyint(1) unsigned NOT NULL default '0',
  `error` tinyint(1) unsigned NOT NULL default '0',
  `help` tinyint(1) unsigned NOT NULL default '0',
  `secure` tinyint(1) unsigned NOT NULL default '0',
  `forward` varchar(255) NOT NULL default 'home' COMMENT 'Forwards when not authenticated',
  `feed` tinyint(1) unsigned NOT NULL default '0',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  `modal` tinyint(3) unsigned NOT NULL default '0',
  `modal_width` int(10) NOT NULL default '0',
  `modal_height` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `page`
-- ----------------------------
BEGIN;
INSERT INTO `page` VALUES ('1', '0', '0', 'scms', 'blank', 'Admin', 'admin', 'admin', 'Admin', '', '', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'admin', 'admin', 'admin', 'admin', 'admin', 'admin', '1', '0', '0', '0', 'home', '0', '1', '1', '900', '820'), ('2', '3', '1', 'scms', 'blank', 'Error', 'error', 'error', 'Error', 'Error', 'Error - The page you\'re looking for is missing or is experiencing an techincal problem.', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '1', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '1', '0', '0', 'home', '0', '1', '0', '0', '0'), ('3', '0', '0', 'scms', 'blank', 'Home', 'home', 'home', 'Home', 'Build an Empire', 'Welcome to your Bento install', '1', '1', '1', '1', '0', '0', '1', '1', '0', '1', '1', '1', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '1', '0', '0', '0', '0'), ('4', '3', '2', 'scms', 'blank', 'Help', 'help', 'help', 'Help', 'Help', 'Get the help you need about Bento.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'modal-medium', 'main', 'main', 'modal-medium', 'main', 'modal-medium', '0', '0', '1', '0', 'home', '0', '0', '1', '500', '420'), ('5', '3', '3', 'scms', 'blank', 'Register', 'register', 'register1', 'Register', 'Register', 'Register your account no.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'modal-medium', 'main', 'main', 'modal-medium', 'main', 'modal-medium', '0', '0', '0', '0', 'home', '0', '0', '1', '500', '420'), ('6', '3', '4', 'scms', 'blank', 'Forgot Your Password?', 'forgot', 'forgot', 'Forgot Your Password?', 'Forgot Your Password?', 'Lost your password? No problem, we can find it for you.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'modal-medium', 'main', 'main', 'modal-small', 'main', 'modal-small', '0', '0', '0', '0', 'home', '0', '0', '1', '330', '230'), ('7', '3', '0', 'scms', 'blank', 'Confrim Your Account', 'confirm', 'confirm1', 'Confirm Your Account', 'Confirm Your Account', 'One more step to confirm your account.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '0', '1', '0', '0', '0'), ('8', '3', '0', 'scms', 'blank', 'Your Account', 'account', 'account', 'Your Account', 'Your Account', 'You account and settings.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '0', '0', '0', '0', '500'), ('9', '3', '0', 'scms', 'blank', 'Search Results', 'search', 'search', 'Search Results', 'Search Results', 'Search results for you.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '0', '1', '0', '0', '0'), ('10', '3', '0', 'scms', 'blank', 'Login', 'login', 'login1', 'Login', 'Login', 'Login now!', '1', '1', '1', '1', '1', '1', '0', '0', '1', '0', '0', '1', 'modal-medium', 'main', 'main', 'modal-medium', 'main', 'modal-medium', '0', '0', '0', '0', 'home', '0', '0', '1', '500', '286'), ('11', '3', '0', 'scms', 'blank', 'Logout', 'logout', 'logout', 'Logout', 'Logout', '', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'modal-small', 'main', 'main', 'modal-small', 'main', 'modal-small', '0', '0', '0', '0', 'home', '0', '0', '1', '330', '150'), ('12', '3', '0', 'scms', 'blank', 'Reset', 'reset', 'reset', 'Reset Your Password', 'Reset Your Password', 'Reset your lost password.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '0', '1', '0', '0', '0'), ('13', '3', '0', 'scms', 'blank', 'Sitemap', 'sitemap', 'sitemap', 'Sitemap', 'Sitemap', 'Sitemap for this site.', '1', '1', '1', '1', '1', '1', '0', '0', '0', '0', '0', '0', 'main', 'main', 'main', 'main', 'main', 'main', '0', '0', '0', '0', 'home', '0', '0', '0', '0', '0');
COMMIT;

-- ----------------------------
--  Table structure for `page_permission_x`
-- ----------------------------
DROP TABLE IF EXISTS `page_permission_x`;
CREATE TABLE `page_permission_x` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `page_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `page_permission_x_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `page_permission_x_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `page_permission_x`
-- ----------------------------
BEGIN;
INSERT INTO `page_permission_x` VALUES ('1', '1', '1');
COMMIT;

-- ----------------------------
--  Table structure for `permission`
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `permission`
-- ----------------------------
BEGIN;
INSERT INTO `permission` VALUES ('1', 'Admin', 'Required to access administration.');
COMMIT;

-- ----------------------------
--  Table structure for `plugins`
-- ----------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `state` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `setup`
-- ----------------------------
DROP TABLE IF EXISTS `setup`;
CREATE TABLE `setup` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `language` varchar(255) NOT NULL default 'english',
  `domain_web` varchar(255) NOT NULL,
  `domain_facebook` varchar(255) NOT NULL,
  `domain_mobile` varchar(255) NOT NULL,
  `domain_app` varchar(255) NOT NULL,
  `domain_narrowcast` varchar(255) NOT NULL,
  `domain_kiosk` varchar(255) NOT NULL,
  `tagline` varchar(255) NOT NULL default 'Welcome to the future',
  `default_mode` varchar(255) NOT NULL default 'web',
  `mail_email` varchar(255) NOT NULL,
  `mail_from` varchar(255) NOT NULL,
  `feed_type` varchar(255) default NULL,
  `feed_time` int(10) unsigned NOT NULL default '20000',
  `feed_place` varchar(255) NOT NULL,
  `feed_load` tinyint(3) unsigned NOT NULL default '1',
  `feed_form` varchar(255) NOT NULL,
  `feed_div` varchar(255) NOT NULL,
  `feed_reload` tinyint(3) unsigned NOT NULL default '1',
  `notification_form` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `setup`
-- ----------------------------
BEGIN;
INSERT INTO `setup` VALUES ('1', 'english', '', '', '', '', '', '', '', 'web', '', '', 'pull', '10', 'top', '1', 'scms_feed_form', 'scms_feed_div', '1', 'scms_notification_form');
COMMIT;