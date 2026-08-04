<?php

$host = "localhost";
$dbname = "bookhub_db";
$user = "root";
$password = "";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>