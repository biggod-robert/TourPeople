<?php
class Connection
{
    public static function Conectar()
    {
        define('server', 'localhost');
        define('name_db', 'tour_people');
        define('user', 'root');
        define('password', '');
        $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        try {
            $connection = new PDO("mysql:host=" . server . "; dbname=" . name_db, user, password, $options);
            return $connection;
        } catch (Exception $e) {
            die("El error de connection es: " . $e->getMessage());
        }
    }
}
