<?php

/*
        $s="DELETE FROM $tb WHERE $a $u";
// if(msq_pole($table,$pole)===false) msq("ALTER TABLE `".$table."` ADD `".$pole."` ".$s
msq_pole($tb,$pole) // проверить, существует ли такое поле в таблице $tb
 // проверить, существует ли такая таблица
msq_index($tb,$index) // проверить, существует ли такой индекс
// изменить поле в таблице      function msq_change_pole($table,$pole,$s)
// добавить поле таблицы        function msq_add_pole($table,$pole,$s)
// удалить поле из таблицы      function msq_del_pole($table,$pole)
// добавить ИНДЕКС в таблицу    function msq_add_index($table,$pole,$s)
// удалить ИНДЕКС из таблицы    function msq_del_index($table,$pole)
// создать таблицу              function msq_add_table($table,$s)
// удалить таблицу              function msq_del_table($table,$text)
*/


function installmod_init() {
    if(!msq_table('jur')) return false;
    $pp=ms("SHOW INDEX FROM `jur`","_a",0); if(sizeof($pp)>2) return false;
    return "Изменить индекс `jur`";
}

function installmod_do() {

//   $pp=ms("SHOW INDEX FROM `jur`","_a",0); dier($pp);
/*

  `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'время последнего обновления',

    [0] => Array
        (
            [Table] => jur
            [Non_unique] => 0
            [Key_name] => PRIMARY
            [Seq_in_index] => 1
            [Column_name] => acn
            [Collation] => A
            [Cardinality] => 389
            [Sub_part] => 
            [Packed] => 
            [Null] => 
            [Index_type] => BTREE
            [Comment] => 
            [Index_comment] => 
        )

    [1] => Array
        (
            [Table] => jur
            [Non_unique] => 1
            [Key_name] => acc
            [Seq_in_index] => 1
            [Column_name] => acc
            [Collation] => A
            [Cardinality] => 389
            [Sub_part] => 
            [Packed] => 
            [Null] => 
            [Index_type] => BTREE
            [Comment] => 
            [Index_comment] => 
        )




Array
(
    [0] => Array
        (
            [Table] => jur
            [Non_unique] => 0
            [Key_name] => PRIMARY
            [Seq_in_index] => 1
            [Column_name] => acn
            [Collation] => A
            [Cardinality] => 
            [Sub_part] => 
            [Packed] => 
            [Null] => 
            [Index_type] => BTREE
            [Comment] => 
            [Index_comment] => 
        )

    [1] => Array
        (
            [Table] => jur
            [Non_unique] => 0
            [Key_name] => PRIMARY
            [Seq_in_index] => 2
            [Column_name] => unic
            [Collation] => A
            [Cardinality] => 389
            [Sub_part] => 
            [Packed] => 
            [Null] => 
            [Index_type] => BTREE
            [Comment] => 
            [Index_comment] => 
        )

    [2] => Array
        (
            [Table] => jur
            [Non_unique] => 1
            [Key_name] => acc
            [Seq_in_index] => 1
            [Column_name] => acc
            [Collation] => A
            [Cardinality] => 389
            [Sub_part] => 
            [Packed] => 
            [Null] => 
            [Index_type] => BTREE
            [Comment] => 
            [Index_comment] => 
        )

)

*/

    if(!msq_pole('jur','time')) msq_add_pole('jur','time',"timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время последнего обновления админов'");
    msq("ALTER TABLE `jur` CHANGE `acn` `acn` int(10) unsigned NOT NULL COMMENT 'Номер журнала'");
    msq("ALTER TABLE `jur` DROP PRIMARY KEY");
    msq("ALTER TABLE `jur` ADD PRIMARY KEY(`acn`,`unic`)");
    return "Изменена таблица `jur`, изменен индекс";
}

?>