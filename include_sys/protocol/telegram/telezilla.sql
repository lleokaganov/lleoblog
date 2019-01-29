-- --------------------------------------------------------
--
-- Структура таблицы `telezil_users`
--
CREATE TABLE IF NOT EXISTS `telezil_users` (
    `user` int(10) unsigned NOT NULL auto_increment COMMENT 'порядковый номер юзера',
    `id` bigint(20) unsigned COMMENT 'id telegram-юзера',
    `bot` smallint(20) unsigned COMMENT 'id бота, в котором появился юзер',
    `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время первого появления',
    `nick` varchar(128) NOT NULL COMMENT 'никнейм юзера',
    `name` varchar(255) NOT NULL COMMENT 'имя и фамилия юзера',
PRIMARY KEY (`user`),
KEY `user` (`id`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база телеграм-юзеров' ;

-- --------------------------------------------------------
--
-- Структура таблицы `telezil_messages`
--
--     `type` enum('in','out') NOT NULL COMMENT 'входящие/исходящие',
--
CREATE TABLE IF NOT EXISTS `telezil_messages` (
    `n` int(10) unsigned NOT NULL auto_increment COMMENT 'порядковый номер',
    `user` int(10) unsigned COMMENT 'номер юзера в базе юзеров',
    `bot` smallint(5) unsigned COMMENT 'id бота',
    `chat` int(11) unsigned COMMENT 'id чата',
    `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время сообщения',
    `text` text NOT NULL COMMENT 'Текст сообщения',
PRIMARY KEY (`n`),
KEY `time` (`time`),
KEY last (`user`,`bot`,`chat`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база сообщений' ;


