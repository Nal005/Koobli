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
    $userId = $_SESSION['user_id'];

    if ($apiId && $titulo) {

        $livroId = obterOuCriarLivro($conn, $apiId, $titulo, $autor, $isbn, $capa);

        $check = $conn->prepare("SELECT id FROM favoritos WHERE utilizador_id = ? AND livro_id = ?");
        $check->bind_param("ii", $userId, $livroId);
        $check->execute();
        $existe = $check->get_result();

        if ($existe->num_rows > 0) {
            // Já é favorito -> remove
            $linha = $existe->fetch_assoc();
            $del = $conn->prepare("DELETE FROM favoritos WHERE id = ?");
            $del->bind_param("i", $linha['id']);
            $del->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO favoritos (utilizador_id, livro_id) VALUES (?, ?)");
            $ins->bind_param("ii", $userId, $livroId);
            $ins->execute();
        }
    }
}

$voltar = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header("Location: " . $voltar);
exit;