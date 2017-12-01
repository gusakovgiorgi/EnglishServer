<?php
require_once 'Database.php';
$conn=new mysqli("at285851.mysql.tools:3306", "at285851_db", "HLULlUPj", "at285851_db");
echo ($conn->query("SELECT"));
var_dump($conn);