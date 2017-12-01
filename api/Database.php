<?php

class Database
{

    private static $serverName = "at285851.mysql.tools:3306";

    private static $userName = "at285851_db";

    private static $password = "HLULlUPj";

    const DB_NAME = "at285851_db";

    private static $conn;

    public static function getNewDatabaseConnection(){
        self::$conn = new mysqli(self::$serverName, self::$userName, self::$password, self::DB_NAME);
        echo self::$conn->connect_error;
        return self::$conn;
    }

    public static function getExistingDatabaseConnection()
    {
        if (! self::$conn) {
            Database::getNewDatabaseConnection();
        }
        return self::$conn;
    }
}