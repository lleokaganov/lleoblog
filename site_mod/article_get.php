<?php // Если строка в браузере - выдает один аргумент, если нет - второй

// {_article_get:num_} - вывести htmlspecialchars($article['num']);

function article_get($e) { return h($GLOBALS['article'][$e]); }

?>