<?php
session_start();
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/livros.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $apiId = $_POST['api_id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $capa = $_POST['capa'] ?? '';

    if ($apiId && $titulo) {

        $livroId = obterOuCriarLivro($conn, $apiId, $titulo, $autor, $isbn, $capa);
        $userId = $_SESSION['user_id'];

        
        $check = $conn->prepare("SELECT id FROM biblioteca WHERE utilizador_id = ? AND livro_id = ?");
        $check->bind_param("ii", $userId, $livroId);
        $check->execute();
        $existe = $check->get_result();

        if ($existe->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO biblioteca (utilizador_id, livro_id, estado) VALUES (?, ?, 'quero_ler')");
            $stmt->bind_param("ii", $userId, $livroId);
            $stmt->execute();
        }
    }
}

header("Location: ../index.php");
exit;