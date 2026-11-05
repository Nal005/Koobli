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

    $info = pedirVolume($apiId);

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

// Pede um livro à Google Books e devolve o volumeInfo (null se o pedido falhar)
function pedirVolume($apiId) {
    $url = "https://www.googleapis.com/books/v1/volumes/" . $apiId . "?key=" . GOOGLE_BOOKS_API_KEY;
    $resposta = @file_get_contents($url);

    if (!$resposta) {
        return null;
    }

    $dados = json_decode($resposta, true);
    return $dados['volumeInfo'] ?? null;
}

function urlCapa($capa, $raiz = "") {
    if (empty($capa) || str_contains($capa, "via.placeholder.com")) {
        return $raiz . "imgs/sem-capa.png";
    }
    return $capa;
}

// Devolve a média (com uma casa decimal) e o número de avaliações de um livro.
// Recebe o api_id porque o destaque do index.php só tem o id da Google Books.
function obterMediaAvaliacoes($conn, $apiId) {
    $stmt = $conn->prepare("
        SELECT ROUND(AVG(a.nota), 1) AS media, COUNT(*) AS total
        FROM avaliacoes a
        JOIN livros l ON a.livro_id = l.id
        WHERE l.api_id = ?
    ");
    $stmt->bind_param("s", $apiId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Cinco estrelas: cheias até à nota (arredondada para baixo, 4,6 dá 4) e de contorno no resto
function iconesEstrelas($nota) {
    $cheias = (int) floor($nota);
    return str_repeat('<i class="fi fi-ss-star"></i>', $cheias)
        . str_repeat('<i class="fi fi-rs-star"></i>', 5 - $cheias);
}
