<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/livros.php";

$apiId = $_GET['api_id'] ?? '';
$titulo = $_GET['titulo'] ?? '';
$autor = $_GET['autor'] ?? '';
$isbn = $_GET['isbn'] ?? '';
$capa = $_GET['capa'] ?? '';

if (!$apiId || !$titulo) {
    header("Location: ../index.php");
    exit;
}

$livroId = obterOuCriarLivro($conn, $apiId, $titulo, $autor, $isbn, $capa);

header("Location: livro.php?id=" . $livroId);
exit;