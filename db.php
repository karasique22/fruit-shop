<?php

$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'shop_database';

function connect_to_db()
{
    global $db_host, $db_user, $db_password, $db_name;

    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($mysqli->connect_error) {
        die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
    }

    $mysqli->set_charset('utf8');

    return $mysqli;
}
