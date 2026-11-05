<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $bibliotecaId = $_POST['biblioteca_id'] ?? 0;
    $novoEstado = $_POST['estado'] ?? '';
    $userId = $_SESSION['user_id'];

    $estadosValidos = ['quero_ler', 'lendo', 'lido', 'abandonado'];

    if ($bibliotecaId && in_array($novoEstado, $estadosValidos)) {

        
        if ($novoEstado === 'lido') {
            $stmt = $conn->prepare("UPDATE biblioteca SET estado = ?, data_fim = CURDATE() WHERE id = ? AND utilizador_id = ?");
        } elseif ($novoEstado === 'lendo') {
            $stmt = $conn->prepare("UPDATE biblioteca SET estado = ?, data_inicio = CURDATE() WHERE id = ? AND utilizador_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE biblioteca SET estado = ? WHERE id = ? AND utilizador_id = ?");
        }

        $stmt->bind_param("sii", $novoEstado, $bibliotecaId, $userId);
        $stmt->execute();
    }
}

// Se o pedido veio da página do livro, volta para lá
if (!empty($_POST['livro_id'])) {
    header("Location: ../paginas/livro.php?id=" . (int) $_POST['livro_id']);
    exit;
}

header("Location: ../paginas/biblioteca.php");
exit;