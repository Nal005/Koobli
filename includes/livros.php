<?php
require_once __DIR__ . "/apikey.php";

// Devolve o id do livro na BD. Se ainda não existir, os dados vêm da Google Books API
// (e não do browser). Devolve null se o api_id não corresponder a nenhum livro.
function obterOuCriarLivro($conn, $apiId) {

    // Os ids da Google Books só têm letras, números, "_" e "-" (\z = fim do texto)
    if (!is_string($apiId) || !preg_match('/^[A-Za-z0-9_-]+\z/', $apiId)) {
        return null;
    }

    $stmt = $conn->prepare("SELECT id FROM livros WHERE api_id = ?");
    $stmt->bind_param("s", $apiId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $livro = $resultado->fetch_assoc();
        return $livro['id'];
    }

    $url = "https://www.googleapis.com/books/v1/volumes/" . $apiId . "?key=" . GOOGLE_BOOKS_API_KEY;
    $resposta = @file_get_contents($url);

    if (!$resposta) {
        return null;
    }

    $dados = json_decode($resposta, true);
    $info = $dados['volumeInfo'] ?? [];

    if (empty($info['title'])) {
        return null;
    }

    $titulo = $info['title'];
    $autor = $info['authors'][0] ?? "Autor desconhecido";
    $isbn = $info['industryIdentifiers'][0]['identifier'] ?? "";
    $capa = $info['imageLinks']['thumbnail'] ?? "";

    // Se outro pedido já inseriu este livro (ex.: duplo clique), o UNIQUE do api_id
    // não deixa duplicar e LAST_INSERT_ID(id) faz o insert_id devolver o id existente
    $stmt = $conn->prepare("
        INSERT INTO livros (api_id, titulo, autor_principal, isbn, capa) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
    ");
    $stmt->bind_param("sssss", $apiId, $titulo, $autor, $isbn, $capa);
    $stmt->execute();

    return $stmt->insert_id;
}

function urlCapa($capa, $raiz = "") {
    if (empty($capa) || str_contains($capa, "via.placeholder.com")) {
        return $raiz . "imgs/sem-capa.png";
    }
    return $capa;
}
