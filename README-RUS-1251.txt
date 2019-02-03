# lleoblog

ИНСТАЛЛЯЦИЯ

1. ПЕРЕД

    Скачайте движок в корень сайта или выделенную папку, переименуйте config.sys.tmpl в config.sys and и отредактируйте. Самый важный параметр - установки MySQL:

        $msq_host = "localhost";
        $msq_login = "root";
        $msq_pass = "MySIC1234";
        $msq_basa = "blogs";
        $msq_charset = "cp1251";

    Если движок установлен не в корень, а выделенную папку (например blog/):
    -- Пропишите $blogdir = "blog/"; в config.sys
    -- Если используется apache, исправьте в .htaccess "RewriteBase /dnevnik/" вместо "RewriteBase /"

    Если используется nginx, вы должны:
	-- Запретить доступ к папке hidden
	-- Переадресовать вызовы любых несуществующих страниц на index.php
	-- Пример конфигурации nginx.cong в конце

2. ЗАПУСК

    Попробуйте открыть http://мой.сайт/install, перегрузите страницу и следуйте инструкциям. Первым делом понажимайте все кнопки создания таблиц.

3. ПОСЛЕ УСТАНОВКИ

    ВНИМАНИЕ!!! Любой посетитель во время установки имеет админские права! После установки следует прописать админа:

    -- Пропишите $admin_unics="99999999"; (несуществующий номер) в config.sys
    -- Перегрузите http://мой.сайт/install нажмите на клавиатуре "U" или кликните на иконку в правом верхнем углу чтобы открыть свою Карточку и прочесть в заголовке номер (например 1)
    -- Впшите его $admin_unics="1"; в config.sys
    -- Перегрузите страницу чтобы увидеть желтый шарик слева вверху - это админское меню.


Пример nginx.conf:

upstream home {
  server unix:/var/run/home-fpm.sock;
}

server {
  listen 80 default_server;
  listen [::]:80 default_server ipv6only=on;

  root /var/www/home;
  index index.php index.html index.htm index.shtml;

  server_name lleo.me;

  client_max_body_size 500M;

  location /hidden {
    deny all;
    return 404;
  }

  location / {
    try_files $uri /index.php?$args;

    access_log /var/www/home/hidden/nginx/access.log;
    error_log /var/www/home/hidden/nginx/error.log;

    location ~ \.php$ {
      fastcgi_split_path_info ^(.+\.php)(/.+)$;
      fastcgi_pass unix:/var/run/home-fpm.sock;
      fastcgi_index index.php;
      include fastcgi_params;
      proxy_set_header Host $host;
      proxy_set_header X-Real-IP $remote_addr;
      proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
      client_max_body_size       500m;
      client_body_buffer_size    128k;
      proxy_connect_timeout      90;
      proxy_send_timeout         90;
      proxy_read_timeout         90;
      proxy_buffer_size          4k;
      proxy_buffers              4 32k;
      #proxy_buffers           32 4k;
      proxy_busy_buffers_size    64k;
      proxy_temp_file_write_size 64k;
    }
  }
}
