<?php
session_start();
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

header("Location: biblioteca.php");
exit;