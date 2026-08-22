<?php

function obterOuCriarLivro($conn, $apiId, $titulo, $autor, $isbn, $capa) {

    
    $stmt = $conn->prepare("SELECT id FROM livros WHERE api_id = ?");
    $stmt->bind_param("s", $apiId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $livro = $resultado->fetch_assoc();
        return $livro['id'];
    }

    
    $stmt = $conn->prepare("INSERT INTO livros (api_id, titulo, autor_principal, isbn, capa) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $apiId, $titulo, $autor, $isbn, $capa);
    $stmt->execute();

    return $stmt->insert_id;
}