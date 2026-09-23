<?php
session_start();
include __DIR__ . "/../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $livroId = $_POST['livro_id'] ?? 0;
    $comentario = trim($_POST['comentario'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($livroId && $comentario) {
        $stmt = $conn->prepare("INSERT INTO comentarios (utilizador_id, livro_id, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $livroId, $comentario);
        $stmt->execute();
    }
}

header("Location: ../paginas/livro.php?id=" . $livroId . "#comentarios");
exit;