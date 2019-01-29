-- phpMyAdmin SQL Dump
-- version 2.11.9.4
-- http://www.phpmyadmin.net
--
-- Хост: mysql.baze.lleo.aha.ru:64256
-- Время создания: Янв 19 2010 г., 11:06
-- Версия сервера: 5.0.87
-- Версия PHP: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- База данных: `lleoblog`
--

-- --------------------------------------------------------

--
-- Структура таблицы `mailbox`
--

CREATE TABLE IF NOT EXISTS `mailbox` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `answerid` int(10) unsigned NOT NULL COMMENT 'ответ на',
  `unicfrom` int(10) unsigned NOT NULL COMMENT 'id отправителя',
  `unicto` int(10) unsigned NOT NULL COMMENT 'id получателя',
  `timecreate` int(11) unsigned NOT NULL default '0' COMMENT 'Время создания',
  `timeview` int(11) unsigned NOT NULL default '0' COMMENT 'Время первого прочтения',
  `timeread` int(11) unsigned NOT NULL default '0' COMMENT 'Время подтверждения прочтения',
  `text` text NOT NULL COMMENT 'Текст письма',
  `IPN` int(10) unsigned NOT NULL COMMENT 'IP в цифре',
  `BRO` varchar(1024) NOT NULL COMMENT 'Браузер все-таки запишем?',
  `whois` varchar(128) NOT NULL COMMENT 'Определялка страны',
  PRIMARY KEY  (`id`),
  KEY `new` (`unicto`,`timeread`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Почта посетителей' AUTO_INCREMENT=0 ;

----------------------------------------------------------
--
-- Структура таблицы `socialmedias`
--
CREATE TABLE IF NOT EXISTS `socialmedias` (
  `i` int(10) unsigned NOT NULL auto_increment COMMENT 'Номер записи',
  `acn` int(10) unsigned NOT NULL COMMENT 'Номер журнала',
  `num` int(10) unsigned NOT NULL COMMENT 'Номер заметки',
  `net` varchar(64) NOT NULL COMMENT 'СОЦСЕТЬ:ЮЗЕР',
  `url` varchar(128) NOT NULL COMMENT 'url объекта, относящегося к заметке - url фотки, имя альбома, data заметки',
  `cap_sha1` varchar(40) NOT NULL COMMENT 'sha1-хэш объекта для отслеживания изменений',
  `id` varchar(256) NOT NULL COMMENT 'уникальный id',
  `type` enum('post','vk_album','vk_foto','vk_note','fb_album','fb_foto','ya_album','ya_foto','instagramm_foto') NOT NULL COMMENT 'вид материала',
  PRIMARY KEY (`i`),
    KEY `new` (`acn`,`num`,`net`(64)),
    KEY `url` (`url`),
    KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='база объектов внешних постингов';

--   KEY `id` (`id`),

----------------------------------------------------------
--
-- Структура таблицы `socialmedia`
--
--CREATE TABLE IF NOT EXISTS `socialmedia` (
--  `acn` int(10) unsigned NOT NULL COMMENT 'Номер журнала',
--  `num` int(10) unsigned NOT NULL COMMENT 'Номер заметки',
--  `net` varchar(64) NOT NULL COMMENT 'Название соцсети, куда был постинг',
--  `url` varchar(256) NOT NULL COMMENT 'Идентификатор поста в соцсети (url)',
--  PRIMARY KEY (`acn`,`num`,`net`(64))
--) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='база внешних постингов';
--
--    `from` int(10) unsigned NOT NULL COMMENT 'Начало IP-диапазона',
--    `to` int(10) unsigned NOT NULL COMMENT 'Конец IP-диапазона',
--
----------------------------------------------------------
--
-- Структура таблицы `geoip`
--
CREATE TABLE IF NOT EXISTS `geoip` (
    `from` VARBINARY(16) NOT NULL COMMENT 'Начало IP-диапазона',
    `to` VARBINARY(16) NOT NULL COMMENT 'Конец IP-диапазона',
    `i` int(10) unsigned NOT NULL COMMENT 'Идентификатор результата в geoipd',
  PRIMARY KEY (`from`),
  KEY `to` (`to`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='база IP-адресов мира';

----------------------------------------------------------
--
-- Структура таблицы `geoipd`
--
CREATE TABLE IF NOT EXISTS `geoipd` (
    `i` int(10) unsigned NOT NULL auto_increment COMMENT 'Номер записи',
    `country` char(5) NOT NULL COMMENT 'Код страны',
    `city` varchar(160) NOT NULL COMMENT 'Город',
    PRIMARY KEY (`i`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='результаты для geoip';

----------------------------------------------------------
--
-- Структура таблицы `golosovalka`
--
CREATE TABLE IF NOT EXISTS `golosovalka` (
  `unic` int(10) unsigned NOT NULL COMMENT 'id голосующего',
  `acn` int(10) unsigned NOT NULL COMMENT 'id журнала',
  `gid` int(10) unsigned NOT NULL default '0' COMMENT 'id опроса',
  `vid` tinyint(3) unsigned NOT NULL COMMENT 'номер вопроса',
  `vad` tinyint(3) unsigned NOT NULL COMMENT 'вариант ответа',
  PRIMARY KEY  (`unic`,`acn`,`gid`,`vid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Голосовалка';


----------------------------------------------------------
--
-- Структура таблицы `userdata`
--
CREATE TABLE IF NOT EXISTS `userdata` (
  `acn` int(10) unsigned NOT NULL auto_increment COMMENT 'Номер журнала',
  `basa` varchar(32) NOT NULL COMMENT 'база',
  `name` varchar(32) NOT NULL COMMENT 'имя',
  `data` text NOT NULL COMMENT 'данные',
  `dostup` enum('all','adm') NOT NULL default 'adm' COMMENT 'Доступ: админу или всем',
  PRIMARY KEY (`acn`,`basa`(32),`name`(32)),
  KEY `key` (`acn`,`basa`(32))
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Пользовательские данные';

----------------------------------------------------------
--
-- Структура таблицы `unic`
--
CREATE TABLE IF NOT EXISTS `unic` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Личный номер из куки',
  `realname` varchar(64) NOT NULL COMMENT 'имя/ник (предпочтительно имя-фамилия)',
  `openid` varchar(128) NOT NULL COMMENT 'inf-url',
  `login` varchar(32) NOT NULL,
    `teddyid` int(11) NOT NULL default '0' COMMENT 'мобильный логин https://teddyid.com',
  `password` varchar(32) NOT NULL,
  `mail` varchar(64) NOT NULL COMMENT 'mail при регистрации - нельзя сменить никогда',
  `mailw` varchar(64) NOT NULL COMMENT 'действующий mail (изначально совпадает)',
  `tel` varchar(16) NOT NULL COMMENT 'мобильник при регистрации - нельзя сменить никогда',
  `telw` varchar(16) NOT NULL COMMENT 'действующий мобильник (изначально совпадает)',
  `img` varchar(180) NOT NULL COMMENT 'ссылка на фотку.jpg',
  `mail_comment` enum('1','0') NOT NULL default '1' COMMENT 'личное: отправлять ли комментарии на email?',
  `site` varchar(128) NOT NULL,
  `birth` date NOT NULL COMMENT 'личное: дата рождения',
  `admin` enum('user','podzamok') NOT NULL,
  `ipn` int(10) unsigned NOT NULL COMMENT 'ip при последнем редактировании личной карточки',
  `time_reg` int(11) NOT NULL default '0' COMMENT 'время регистрации',
  `timelast` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время последнего обновления личной карточки',
  `capcha` enum('yes','no') NOT NULL default 'no',
  `capchakarma` tinyint(3) unsigned NOT NULL default '0' COMMENT 'Капча-карма нового формата',
  `opt` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Логины посетителей' AUTO_INCREMENT=0 ;
--   `login` varchar(64) NOT NULL COMMENT 'vasya либо vasya@openid.site',
--   `password` varchar(32) NOT NULL,
--   `realname` varchar(128) NOT NULL COMMENT 'личное: имя',
--   `aboutme` varchar(2048) NOT NULL COMMENT 'личное: О себе',

--
-- Структура таблицы `jur`
--
CREATE TABLE IF NOT EXISTS `jur` (
  `acn` int(10) unsigned NOT NULL COMMENT 'Номер журнала',
  `acc` varchar(32) NOT NULL COMMENT 'Имя журнала',
  `unic` int(10) unsigned NOT NULL COMMENT 'Владелец',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время последнего обновления админов',
   PRIMARY KEY (`acn`,`unic`),
   KEY `acc` (`acc`(32))
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='База журналов' AUTO_INCREMENT=0 ;


--
-- Структура таблицы `unijur`
--
-- CREATE TABLE IF NOT EXISTS `unijur` (
--   `jur` int(10) unsigned NOT NULL COMMENT 'Номер журнала',
--   `uni` int(10) unsigned NOT NULL COMMENT 'Номер unic пользователя',
--   `capchakarma` tinyint(3) unsigned NOT NULL default '0' COMMENT 'Капча-карма',
--   `dostup` enum('user','podzamok','mudak','writer','admin') NOT NULL,
--   `abouthim` varchar(2048) NOT NULL COMMENT 'О нем',
--    PRIMARY KEY (`jur`,`uni`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Настройки посетителей для своего журнала' AUTO_INCREMENT=0 ;


-- --------------------------------------------------------

--
-- Структура таблицы `dnevnik_comm`
--

CREATE TABLE IF NOT EXISTS `dnevnik_comm` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `unic` int(10) unsigned NOT NULL COMMENT 'id автора',
  `DateID` int(10) NOT NULL COMMENT 'К какой заметке относится',
  `Name` varchar(128) NOT NULL COMMENT 'Автор',
  `Mail` varchar(128) NOT NULL COMMENT 'email',
  `Text` text NOT NULL COMMENT 'Текст комментария',
  `Parent` int(10) unsigned NOT NULL default '0' COMMENT 'На что ответ',
  `Time` int(11) unsigned NOT NULL default '0' COMMENT 'Время комментария',
  `IPN` int(10) unsigned NOT NULL COMMENT 'IP в цифре',
  `BRO` varchar(1024) NOT NULL COMMENT 'Браузер все-таки запишем?',
  `whois` varchar(128) NOT NULL COMMENT 'Определялка страны',
  `scr` enum('1','0') NOT NULL default '0' COMMENT 'Открытый, скрытый',
  `rul` enum('1','0') NOT NULL default '0' COMMENT 'Особый',
  `ans` enum('1','0','u') NOT NULL default 'u' COMMENT 'Разрешено ли принимать комментарии к нему?',
  `group` tinyint(3) unsigned NOT NULL COMMENT 'Группа для выделения разным цветом. 0 - все, 1 - админ, 2... ну, допустим, Topbot',
  `golos_plu` int(10) unsigned NOT NULL default '0' COMMENT 'Голосование плюсики',
  `golos_min` int(10) unsigned NOT NULL default '0' COMMENT 'Голосование минусики',
  PRIMARY KEY  (`id`),
  KEY `DateID` (`DateID`),
  KEY `poset` (`unic`,`scr`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Комментарии посетителей' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------
-- Структура таблицы `yablogs_count`
--

CREATE TABLE IF NOT EXISTS `yablogs_count` (
  `num` int(10) NOT NULL,
  `count` int(10) NOT NULL,
  `time` int(11) unsigned NOT NULL default '0' COMMENT 'Время последнего обновления',
  PRIMARY KEY (`num`),
  KEY `timeupdate` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='ссылки по Яндексу на заметку' AUTO_INCREMENT=0 ;
-- --------------------------------------------------------

-- Структура таблицы `dnevnik_link`
--

CREATE TABLE IF NOT EXISTS `dnevnik_link` (
  `n` bigint(20) NOT NULL auto_increment,
  `link` varchar(2048) NOT NULL,
  `count` int(10) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DateID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`n`),
  KEY `DateID` (`DateID`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='заходы по ссылкам' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Структура таблицы `dnevnik_plusiki`
--

CREATE TABLE IF NOT EXISTS `dnevnik_plusiki` (
  `unic` int(10) unsigned NOT NULL,
  `commentID` int(10) unsigned NOT NULL,
  `var` enum('plus','minus') NOT NULL,
  PRIMARY KEY  (`unic`,`commentID`),
  KEY `url` (`commentID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

-- --------------------------------------------------------

--
-- Структура таблицы `dnevnik_posetil`
--

CREATE TABLE IF NOT EXISTS `dnevnik_posetil` (
  `unic` int(10) unsigned NOT NULL,
  `url` int(10) unsigned NOT NULL,
  `date` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`unic`,`url`),
  KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

-- --------------------------------------------------------

--
-- Структура таблицы `dnevnik_search`
--

CREATE TABLE IF NOT EXISTS `dnevnik_search` (
  `n` bigint(20) NOT NULL auto_increment,
  `poiskovik` varchar(32) NOT NULL,
  `link` varchar(2048) NOT NULL,
  `search` varchar(2048) NOT NULL,
  `count` int(10) NOT NULL,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DateID` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`n`),
  KEY `link` (`link`(1000))
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='поисковые заходы' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------
--
-- Структура таблицы `dnevnik_zapisi`

CREATE TABLE IF NOT EXISTS `dnevnik_zapisi` (
  `Date` varchar(128) NOT NULL,
  `Header` varchar(255) NOT NULL default '',
  `Body` mediumtext NOT NULL,
  `Access` enum('all','podzamok','admin') NOT NULL default 'admin',
  `visible` enum('1','0') NOT NULL default '1',
  `DateUpdate` int(10) unsigned NOT NULL default '0',
  `view_counter` int(10) unsigned NOT NULL default '0',
  `num` int(10) unsigned NOT NULL auto_increment,
  `DateDatetime` int(11) NOT NULL default '0',
  `DateDate` int(11) NOT NULL default '0',
  `opt` text NOT NULL,
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  UNIQUE KEY `num` (`num`),
  KEY `acn` (`acn`),
  KEY `Date` (`Date`(128)),
  KEY `Access` (`Access`),
  KEY `DateDatetime` (`DateDatetime`),
  KEY `DateDate` (`DateDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Заметки блога' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------
--
-- Структура таблицы `dnevnik_autopost`

CREATE TABLE IF NOT EXISTS `dnevnik_autopost` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `Header` varchar(255) NOT NULL default '',
  `Body` mediumtext NOT NULL,
  `tag` varchar(64) NOT NULL default '',
  `postmode` enum('is_date','silent','silent_priority','day','tag_interval') NOT NULL,
  `randmode` enum('num','random') NOT NULL,
  `dat` int(11) NOT NULL default '0',
  `opt` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `dat` (`dat`),
  KEY `postmode` (`postmode`),
  KEY `randmode` (`randmode`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Для автопостинга' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------
--
--  `sc` varchar(32) NOT NULL,
--  `login` varchar(128) NOT NULL,
--  `ipbro` varchar(255) NOT NULL,
--  `lju` varchar(128) NOT NULL,
--  `Name` varchar(255) NOT NULL,
--  `Mail` varchar(255) NOT NULL,
--
-- Структура таблицы `pravki`
--

CREATE TABLE IF NOT EXISTS `pravki` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `Date` varchar(255) NOT NULL,
  `DateTime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  `unic` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  `textnew` text NOT NULL,
  `stdprav` text NOT NULL,
  `Answer` text NOT NULL,
  `metka` enum('new','submit','discard') NOT NULL default 'new',
  PRIMARY KEY  (`id`),
  KEY `Date` (`Date`(255)),
  KEY `metka` (`metka`),
  KEY `unic` (`unic`),
  KEY `acn` (`acn`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Правки блога' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Структура таблицы `rekomenda`
--
--   `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала', ВСТАВИТЬ acn!!!

CREATE TABLE IF NOT EXISTS `rekomenda` (
  `n` int(10) NOT NULL auto_increment,
  `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `link` varchar(2048) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`n`),
  KEY `datetime` (`datetime`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Горящие ссылки дня' AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Структура таблицы `site`
--
-- `Access` enum('all','podzamok','admin') NOT NULL default 'admin' COMMENT 'Параметры доступа',
-- `type` enum('page','design','news','pageplain','photo') NOT NULL default 'page',
-- `id` int(10) unsigned NOT NULL auto_increment,
-- KEY `type` (`type`)

CREATE TABLE IF NOT EXISTS `site` (
  `name` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  PRIMARY KEY (`acn`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 COMMENT='Контент сайта' AUTO_INCREMENT=0 ;


-- --------------------------------------------------------
--
-- Структура таблицы `unictemp`
--

CREATE TABLE IF NOT EXISTS `unictemp` (
  `unic` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  `timelast` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`unic`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Временные данные пользователя';


-- --------------------------------------------------------
--
-- Структура таблицы `golosovanie_golosa`
--
CREATE TABLE IF NOT EXISTS `golosovanie_golosa` (
  `golosid` int(10) unsigned NOT NULL COMMENT 'id голосования',
  `unic` int(10) unsigned NOT NULL COMMENT 'id голосующего',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `value` text NOT NULL,
  PRIMARY KEY  (`golosid`,`unic`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Голосования: голоса';

-- --------------------------------------------------------
--
-- Структура таблицы `golosovanie_result`
--
CREATE TABLE IF NOT EXISTS `golosovanie_result` (
  `golosid` int(10) unsigned NOT NULL auto_increment COMMENT 'id голосования',
  `golosname` varchar(32) NOT NULL COMMENT 'имя голосования',
  `n` int(10) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`golosid`),
  KEY `golosname` (`golosname`(32))
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Голосования: результаты';


-- --------------------------------------------------------
--
-- Структура таблицы `dnevnik_tags`
--
-- ,`tag`(128)

CREATE TABLE IF NOT EXISTS `dnevnik_tags` (
  `num` int(10) unsigned NOT NULL COMMENT 'id заметки',
  `tag` varchar(128) NOT NULL COMMENT 'имя тэга',
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  KEY `num` (`num`),
  KEY `acn` (`acn`),
  KEY `tag` (`tag`(128))
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


-- --------------------------------------------------------
--
-- Структура таблицы `lastcomm`
--

CREATE TABLE IF NOT EXISTS `lastcomm` (
  `unic` int(10) unsigned NOT NULL,
  `acn` int(10) unsigned NOT NULL default '0' COMMENT 'Номер журнала',
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY (`unic`,`acn`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Дата последних прочитанных комментов';


------------------------------------------------------------


