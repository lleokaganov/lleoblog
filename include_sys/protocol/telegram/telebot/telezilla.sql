-- --------------------------------------------------------
--
-- Структура таблицы `telezil_projects`
--
CREATE TABLE IF NOT EXISTS `telezil_projects` (
    `project_id` smallint(10) unsigned NOT NULL auto_increment COMMENT 'Ид проекта',
    `project_name` varchar(256) COMMENT 'Имя проекта',
    `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время создания',
    `mail` varchar(128) COMMENT 'для уведомлений',
    `comment` text COMMENT 'поясняющие комментарии',
PRIMARY KEY (`project_id`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база телеграм-юзеров' ;


-- --------------------------------------------------------
--
-- Структура таблицы `telezil_scenary`
--
CREATE TABLE IF NOT EXISTS `telezil_scenary` (
    `i` smallint(10) unsigned NOT NULL auto_increment COMMENT 'id сценария',
    `project_id` smallint(10) unsigned NOT NULL COMMENT 'id проекта, к которому он относится',
    `scenary_name` varchar(256) COMMENT 'Имя сценария',
    `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время создания',
        `lz_url` varchar(256) COMMENT 'URL сервера',
        `lz_login` varchar(256) COMMENT 'login',
        `lz_pass` char(32) COMMENT 'Позорище',
        `lz_group` varchar(128) COMMENT 'Группа',
        `lz_user` varchar(128) COMMENT 'Пользователь',
        `lz_lang` varchar(5) COMMENT 'Язык по умолчанию',
        `lz_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',
    `tg_API_id` bigint(20) unsigned COMMENT 'ИД бота telegram_API_myid',
    `tg_API_key` varchar(45) COMMENT 'Ключ API бота telegram_API_key',
    `tg_name` varchar(32) COMMENT 'Имя бота',
    `tg_info` varchar(512) COMMENT 'Инфо бота',
    `tg_image` varchar(128) COMMENT 'УРЛ картинки бота',
    `tg_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',
    `tg_wait_message` varchar(512) COMMENT 'Текст на ожидании',
        `command_list` text COMMENT 'Список команд',
        `keywords` text COMMENT 'текст (действие)',
        `name_template` varchar(128) COMMENT 'Настройка формирования имени пользователя',
        `banlist` text COMMENT 'Бан-листы абонентов На основании user_id',
PRIMARY KEY (`i`),
KEY `project_id` (`project_id`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база телеграм-юзеров' ;

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
    `tel` bigint(12) unsigned NOT NULL COMMENT 'телефон юзера 1844674407-370-955-16-15',
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


