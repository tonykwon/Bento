SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `language`
-- ----------------------------
DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(2) NOT NULL COMMENT 'ISO 639-1 Code',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `2letter` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `language`
-- ----------------------------
BEGIN;
INSERT INTO `language` VALUES ('142', 'aa', 'afar'), ('143', 'ab', 'abkhazian'), ('144', 'af', 'afrikaans'), ('145', 'am', 'amharic'), ('146', 'ar', 'arabic'), ('147', 'as', 'assamese'), ('148', 'ay', 'aymara'), ('149', 'az', 'azerbaijani'), ('150', 'ba', 'bashkir'), ('151', 'be', 'byelorussian'), ('152', 'bg', 'bulgarian'), ('153', 'bh', 'bihari'), ('154', 'bi', 'bislama'), ('155', 'bn', 'bengali'), ('156', 'bo', 'tibetan'), ('157', 'br', 'breton'), ('158', 'ca', 'catalan'), ('159', 'co', 'corsican'), ('160', 'cs', 'czech'), ('161', 'cy', 'welsh'), ('162', 'da', 'danish'), ('163', 'de', 'german'), ('164', 'dz', 'bhutani'), ('165', 'el', 'greek'), ('166', 'en', 'english'), ('167', 'eo', 'esperanto'), ('168', 'es', 'spanish'), ('169', 'et', 'estonian'), ('170', 'eu', 'basque'), ('171', 'fa', 'persian'), ('172', 'fi', 'finnish'), ('173', 'fj', 'fiji'), ('174', 'fo', 'faroese'), ('175', 'fr', 'french'), ('176', 'fy', 'frisian'), ('177', 'ga', 'irish'), ('178', 'gd', 'scots'), ('179', 'gl', 'galician'), ('180', 'gn', 'guarani'), ('181', 'gu', 'gujarati'), ('182', 'ha', 'hausa'), ('183', 'he', 'hebrew'), ('184', 'hi', 'hindi'), ('185', 'hr', 'croatian'), ('186', 'hu', 'hungarian'), ('187', 'hy', 'armenian'), ('188', 'ia', 'interlingua'), ('189', 'id', 'indonesian'), ('190', 'ie', 'interlingue'), ('191', 'ik', 'inupiak'), ('192', 'is', 'icelandic'), ('193', 'it', 'italian'), ('194', 'iu', 'inuktitut'), ('195', 'ja', 'japanese'), ('196', 'jw', 'javanese'), ('197', 'ka', 'georgian'), ('198', 'kk', 'kazakh'), ('199', 'kl', 'greenlandic'), ('200', 'km', 'cambodian'), ('201', 'kn', 'kannada'), ('202', 'ko', 'korean'), ('203', 'ks', 'kashmiri'), ('204', 'ku', 'kurdish'), ('205', 'ky', 'kirghiz'), ('206', 'la', 'latin'), ('207', 'ln', 'lingala'), ('208', 'lo', 'laothian'), ('209', 'lt', 'lithuanian'), ('210', 'lv', 'latvian'), ('211', 'mg', 'malagasy'), ('212', 'mi', 'maori'), ('213', 'mk', 'macedonian'), ('214', 'ml', 'malayalam'), ('215', 'mn', 'mongolian'), ('216', 'mo', 'moldavian'), ('217', 'mr', 'marathi'), ('218', 'ms', 'malay'), ('219', 'mt', 'maltese'), ('220', 'my', 'burmese'), ('221', 'na', 'nauru'), ('222', 'ne', 'nepali'), ('223', 'nl', 'dutch'), ('224', 'no', 'norwegian'), ('225', 'oc', 'occitan'), ('226', 'om', 'oromo'), ('227', 'or', 'oriya'), ('228', 'pa', 'punjabi'), ('229', 'pl', 'polish'), ('230', 'ps', 'pashto'), ('231', 'pt', 'portuguese'), ('232', 'qu', 'quechua'), ('233', 'rm', 'rhaeto-romance'), ('234', 'rn', 'kirundi'), ('235', 'ro', 'romanian'), ('236', 'ru', 'russian'), ('237', 'rw', 'kinyarwanda'), ('238', 'sa', 'sanskrit'), ('239', 'sd', 'sindhi'), ('240', 'sg', 'sangho'), ('241', 'sh', 'serbo-croatian'), ('242', 'si', 'sinhalese'), ('243', 'sk', 'slovak'), ('244', 'sl', 'slovenian'), ('245', 'sm', 'samoan'), ('246', 'sn', 'shona'), ('247', 'so', 'somali'), ('248', 'sq', 'albanian'), ('249', 'sr', 'serbian'), ('250', 'ss', 'siswati'), ('251', 'st', 'sesotho'), ('252', 'su', 'sundanese'), ('253', 'sv', 'swedish'), ('254', 'sw', 'swahili'), ('255', 'ta', 'tamil'), ('256', 'te', 'telugu'), ('257', 'tg', 'tajik'), ('258', 'th', 'thai'), ('259', 'ti', 'tigrinya'), ('260', 'tk', 'turkmen'), ('261', 'tl', 'tagalog'), ('262', 'tn', 'setswana'), ('263', 'to', 'tonga'), ('264', 'tr', 'turkish'), ('265', 'ts', 'tsonga'), ('266', 'tt', 'tatar'), ('267', 'tw', 'twi'), ('268', 'ug', 'uighur'), ('269', 'uk', 'ukrainian'), ('270', 'ur', 'urdu'), ('271', 'uz', 'uzbek'), ('272', 'vi', 'vietnamese'), ('273', 'vo', 'volapuk'), ('274', 'wo', 'wolof'), ('275', 'xh', 'xhosa'), ('276', 'yi', 'yiddish'), ('277', 'yo', 'yoruba'), ('278', 'za', 'zhuang'), ('279', 'zh', 'chinese'), ('280', 'zu', 'zulu');
COMMIT;

-- ----------------------------
--  Table structure for `translations`
-- ----------------------------
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `language_id` int(10) unsigned NOT NULL,
  `original` varchar(255) NOT NULL,
  `translation` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `language_id` (`language_id`),
  CONSTRAINT `translations_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

SET FOREIGN_KEY_CHECKS = 1;
