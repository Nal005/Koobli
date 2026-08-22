<?php

function obterApiIdsFavoritos($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT l.api_id
        FROM favoritos f
        JOIN livros l ON f.livro_id = l.id
        WHERE f.utilizador_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $apiIds = [];
    while ($linha = $resultado->fetch_assoc()) {
        $apiIds[] = $linha['api_id'];
    }
    return $apiIds;
}