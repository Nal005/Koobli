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
    $descricao = limparSinopse($info['description'] ?? "");

    // Se outro pedido já inseriu este livro (ex.: duplo clique), o UNIQUE do api_id
    // não deixa duplicar e LAST_INSERT_ID(id) faz o insert_id devolver o id existente
    $stmt = $conn->prepare("
        INSERT INTO livros (api_id, titulo, autor_principal, isbn, capa, descricao) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
    ");
    $stmt->bind_param("ssssss", $apiId, $titulo, $autor, $isbn, $capa, $descricao);
    $stmt->execute();

    return $stmt->insert_id;
}

// Pede um livro à Google Books e devolve o volumeInfo (null se o pedido falhar)
function pedirVolume($apiId) {
    $url = "https://www.googleapis.com/books/v1/volumes/" . $apiId . "?key=" . GOOGLE_BOOKS_API_KEY;

    // Espera no máximo 5 segundos: se a Google Books não responder, a página abre na mesma
    $contexto = stream_context_create(['http' => ['timeout' => 5]]);
    $resposta = @file_get_contents($url, false, $contexto);

    if (!$resposta) {
        return null;
    }

    $dados = json_decode($resposta, true);
    return $dados['volumeInfo'] ?? null;
}

// A descrição da Google Books pode trazer HTML (<p>, <b>, <br>): fica só o texto,
// com cada <br> numa linha nova e uma linha em branco entre parágrafos
function limparSinopse($descricao) {
    $texto = preg_replace('/<br\s*\/?>|<\/li>/i', "\n", $descricao);
    $texto = str_ireplace("</p>", "\n\n", $texto);
    $texto = html_entity_decode(strip_tags($texto), ENT_QUOTES | ENT_HTML5, "UTF-8");

    // O &nbsp; vira um espaço especial (U+00A0) que o trim() não tira: passa a espaço normal
    $texto = str_replace("\u{A0}", " ", $texto);

    // Tira os espaços à volta de cada linha e junta várias linhas em branco numa só
    $texto = preg_replace('/ *\n */', "\n", $texto);
    $texto = preg_replace('/\n{3,}/', "\n\n", $texto);

    return trim($texto);
}

// Devolve a sinopse do livro ("" se não houver). Os livros guardados antes da coluna
// descricao existir têm-na a NULL: vai buscá-la uma vez à Google Books e guarda-a
// ("" quer dizer que a Google Books não tem sinopse, por isso não volta a pedir)
function obterOuGuardarSinopse($conn, $livro) {
    if ($livro['descricao'] !== null) {
        return $livro['descricao'];
    }

    $info = pedirVolume($livro['api_id']);

    // O pedido falhou (sem internet, quota esgotada...): fica NULL e tenta na próxima visita
    if ($info === null) {
        return "";
    }

    $descricao = limparSinopse($info['description'] ?? "");

    $stmt = $conn->prepare("UPDATE livros SET descricao = ? WHERE id = ?");
    $stmt->bind_param("si", $descricao, $livro['id']);
    $stmt->execute();

    return $descricao;
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
