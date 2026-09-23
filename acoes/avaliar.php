<?php
session_start();
include __DIR__ . "/../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $livroId = $_POST['livro_id'] ?? 0;
    $nota = $_POST['nota'] ?? 0;
    $texto = trim($_POST['texto'] ?? '');
    $userId = $_SESSION['user_id'];

    $nota = (int) $nota;

    if ($livroId && $nota >= 1 && $nota <= 5) {

        $stmt = $conn->prepare("
            INSERT INTO avaliacoes (utilizador_id, livro_id, nota, texto)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nota = VALUES(nota), texto = VALUES(texto), data_avaliacao = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("iiis", $userId, $livroId, $nota, $texto);
        $stmt->execute();
    }
}

header("Location: ../paginas/livro.php?id=" . $livroId);
exit;