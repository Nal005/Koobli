<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";
include __DIR__ . "/../includes/livros.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $apiId = $_POST['api_id'] ?? '';

    $livroId = obterOuCriarLivro($conn, $apiId);

    if ($livroId) {

        $userId = $_SESSION['user_id'];

        // IGNORE: se o livro já estiver na biblioteca, o UNIQUE (utilizador_id, livro_id)
        // impede o duplicado e o pedido segue sem erro
        $stmt = $conn->prepare("INSERT IGNORE INTO biblioteca (utilizador_id, livro_id, estado) VALUES (?, ?, 'quero_ler')");
        $stmt->bind_param("ii", $userId, $livroId);
        $stmt->execute();
    }
}

header("Location: ../index.php");
exit;