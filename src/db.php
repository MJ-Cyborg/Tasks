<?php
$servername = "127.0.0.1";
$username = "root";
$password = "kcjam1234";
$database = "tasks_db";
$socket = "/data/data/com.termux/files/usr/var/run/mysqld.sock";

// Create connection
$db = new mysqli($servername, $username, $password, $database, null, $socket);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
