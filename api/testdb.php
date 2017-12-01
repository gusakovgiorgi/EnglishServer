<?php
require_once 'Database.php';
$conn=Database::getExistingDatabaseConnection();
echo ($conn->query("SELECT"));
