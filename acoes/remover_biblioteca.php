<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $bibliotecaId = $_POST['biblioteca_id'] ?? 0;
    $userId = $_SESSION['user_id'];

    if ($bibliotecaId) {
        // O utilizador_id na condição garante que só apaga livros do próprio utilizador
        $stmt = $conn->prepare("DELETE FROM biblioteca WHERE id = ? AND utilizador_id = ?");
        $stmt->bind_param("ii", $bibliotecaId, $userId);
        $stmt->execute();
    }
}

header("Location: ../paginas/biblioteca.php");
exit;
