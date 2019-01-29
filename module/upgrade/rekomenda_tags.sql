-- --------------------------------------------------------
--
-- Структура таблицы `rekomenda_tags`
--
-- ,`tag`(128)

CREATE TABLE IF NOT EXISTS `rekomenda_tags` (
  `num` int(10) unsigned NOT NULL COMMENT 'id ссылки',
  `tag` varchar(128) NOT NULL COMMENT 'имя тэга',
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  UNIQUE KEY `num` (`num`),
  KEY `acn` (`acn`),
  KEY `tag` (`tag`(128))
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

