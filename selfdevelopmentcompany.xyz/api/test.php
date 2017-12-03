<?php
/**
 * Created by PhpStorm.
 * User: notbl
 * Date: 12/3/2017
 * Time: 10:39 PM
 */
$d = getdate(); // использовано текущее время
foreach ( $d as $key => $val )
    echo "$key = $val<br>";
echo "<hr>Сегодня: $d[mday].$d[mon].$d[year]";
?>