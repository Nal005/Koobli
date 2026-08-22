<?php
session_start();
include "includes/config.php";
include "includes/livros.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$apiId = $_GET['api_id'] ?? '';
$titulo = $_GET['titulo'] ?? '';
$autor = $_GET['autor'] ?? '';
$isbn = $_GET['isbn'] ?? '';
$capa = $_GET['capa'] ?? '';

if (!$apiId || !$titulo) {
    header("Location: index.php");
    exit;
}

$livroId = obterOuCriarLivro($conn, $apiId, $titulo, $autor, $isbn, $capa);

header("Location: livro.php?id=" . $livroId);
exit;