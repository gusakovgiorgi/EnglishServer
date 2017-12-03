<?php

class Database
{
    const SERVER_NAME = "at285851.mysql.tools:3306";

    const USER_NAME = "at285851_db";

    const PASSWORD = "HLULlUPj";

    const DB_NAME = "at285851_db";

    private static $conn;

    public static function getNewDatabaseConnection(){
        self::$conn = new mysqli(self::SERVER_NAME, self::USER_NAME, self::PASSWORD, self::DB_NAME);
        return self::$conn;
    }

    public static function getExistingDatabaseConnection()
    {
        if (!self::$conn) {
            self::$conn=self::getNewDatabaseConnection();
        }
        return self::$conn;
    }
    public static function closeExistingConnection(){
        if (isset(self::$conn)){
            self::$conn->close();
        }
    }
}