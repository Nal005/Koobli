<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/livros.php";

$apiId = $_GET['api_id'] ?? '';

$livroId = obterOuCriarLivro($conn, $apiId);

if (!$livroId) {
    header("Location: ../index.php");
    exit;
}

header("Location: livro.php?id=" . $livroId);
exit;