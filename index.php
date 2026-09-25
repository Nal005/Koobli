<?php
include "includes/auth.php";
include "includes/header.php";
include "includes/config.php";
include "includes/favoritos_helper.php";

$favoritosApiIds = obterApiIdsFavoritos($conn, $_SESSION['user_id']);

include "includes/apikey.php";
$apiKey = GOOGLE_BOOKS_API_KEY;


$pesquisa = $_GET['q'] ?? "";
$resultados = [];

if ($pesquisa) {
    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($pesquisa) . "&maxResults=8&key=" . $apiKey;
    $resposta = @file_get_contents($url);

    if ($resposta) {
        $dados = json_decode($resposta, true);
        $resultados = $dados['items'] ?? [];
    }
}

// Carrossel
$titulosBestsellers = [
    "1984|George Orwell",
    "Fourth Wing|Rebecca Yarros",
    "The Silent Patient|Alex Michaelides",
    "Six of Crows|Leigh Bardugo",
    "Throne of Glass|Sarah J. Maas",
    "Pride and Prejudice|Jane Austen",
    "The Cruel Prince|Holly Black",
    "Verity|Colleen Hoover",
    "A Court of Thorns and Roses|Sarah J. Maas",
    "The Housemaid|Freida McFadden"
];

$bestsellers = [];

foreach ($titulosBestsellers as $entrada) {
    list($tituloBusca, $autorBusca) = explode("|", $entrada);

    $query = 'intitle:"' . $tituloBusca . '"+inauthor:"' . $autorBusca . '"';
    $urlBusca = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query) . "&maxResults=1&key=" . $apiKey;

    $resposta = @file_get_contents($urlBusca);

    if ($resposta) {
        $dados = json_decode($resposta, true);
        if (!empty($dados['items'][0])) {
            $bestsellers[] = $dados['items'][0];
        }
    }
}

$destaque = $bestsellers[0] ?? null;
?>

<div class="app-layout">

    <?php include "includes/sidebar.php"; ?>

    <main class="dashboard-content">

        <form class="search" method="GET">
            <input
                type="text"
                name="q"
                placeholder="Pesquisar livros..."
                value="<?php echo htmlspecialchars($pesquisa); ?>">
            <button>Pesquisar</button>
        </form>

        <section class="hero">

            <?php if ($destaque):
                $info = $destaque['volumeInfo'];
                $titulo = $info['title'] ?? "Sem título";
                $autor = $info['authors'][0] ?? "Autor desconhecido";
                $imagem = $info['imageLinks']['thumbnail'] ?? "https://via.placeholder.com/150x220";
                $descricao = $info['description'] ?? "Sem descrição disponível.";
                $descricaoResumida = mb_strlen($descricao) > 150 ? mb_substr($descricao, 0, 150) . "..." : $descricao;
            ?>
            <div class="hero-destaque">

                <?php
                $linkVerLivro = "paginas/ver_livro.php?" . http_build_query([
                    'api_id' => $destaque['id'] ?? '',
                    'titulo' => $titulo,
                    'autor' => $autor,
                    'isbn' => $info['industryIdentifiers'][0]['identifier'] ?? '',
                    'capa' => $imagem
                ]);
                ?>

                <a href="<?php echo $linkVerLivro; ?>">
                    <img src="<?php echo $imagem; ?>" alt="<?php echo htmlspecialchars($titulo); ?>">
                </a>
                <div class="hero-info">
                    <a href="<?php echo $linkVerLivro; ?>" class="link-titulo">
                        <h2><?php echo htmlspecialchars($titulo); ?></h2>
                    </a>
                    <p class="hero-autor"><?php echo htmlspecialchars($autor); ?></p>
                    <p class="hero-descricao"><?php echo htmlspecialchars($descricaoResumida); ?></p>
                    <div class="hero-estrelas">★★★★☆</div>

                    <?php
                    $ehFavorito = in_array($destaque['id'] ?? '', $favoritosApiIds);
                    ?>
                    <form method="POST" action="acoes/favoritar.php" class="form-favorito">
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($destaque['id'] ?? ''); ?>">
                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                        <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                        <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                        <button type="submit" class="btn-coracao"><?php echo $ehFavorito ? '❤️' : '🤍'; ?></button>
                    </form>

                    <form method="POST" action="acoes/adicionar_biblioteca.php">
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($destaque['id'] ?? ''); ?>">
                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                        <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                        <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                        <button type="submit" class="btn-adicionar">+ Adicionar à biblioteca</button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="hero-destaque">
                <p>Não foi possível carregar o livro em destaque.</p>
            </div>
            <?php endif; ?>

            <div class="hero-grafico">
                <h3>Frequência de leitura</h3>
                <div class="grafico-barras">
                    <div class="barra" style="height: 40%;"><span>Jan</span></div>
                    <div class="barra" style="height: 65%;"><span>Fev</span></div>
                    <div class="barra" style="height: 30%;"><span>Mar</span></div>
                    <div class="barra" style="height: 80%;"><span>Abr</span></div>
                    <div class="barra" style="height: 55%;"><span>Mai</span></div>
                    <div class="barra" style="height: 90%;"><span>Jun</span></div>
                </div>
            </div>

        </section>

        <section class="carrossel-section">

            <h2>Sugestões para ti</h2>

            <div class="carrossel-wrapper">
                <div class="carrossel-track">

                    <?php
                    $listaCarrossel = array_merge($bestsellers, $bestsellers);
                    foreach ($listaCarrossel as $livro):
                        $info = $livro['volumeInfo'];
                        $titulo = $info['title'] ?? "Sem título";
                        $autor = $info['authors'][0] ?? "Autor";
                        $imagem = $info['imageLinks']['thumbnail'] ?? "https://via.placeholder.com/150x220";

                        $linkVerLivro = "paginas/ver_livro.php?" . http_build_query([
                            'api_id' => $livro['id'] ?? '',
                            'titulo' => $titulo,
                            'autor' => $autor,
                            'isbn' => $info['industryIdentifiers'][0]['identifier'] ?? '',
                            'capa' => $imagem
                        ]);
                    ?>

                    <div class="card-carrossel">
                        <a href="<?php echo $linkVerLivro; ?>">
                            <img src="<?php echo $imagem; ?>" alt="<?php echo htmlspecialchars($titulo); ?>">
                        </a>
                        <a href="<?php echo $linkVerLivro; ?>" class="link-titulo">
                            <h4><?php echo htmlspecialchars($titulo); ?></h4>
                        </a>
                        <p><?php echo htmlspecialchars($autor); ?></p>


                        <?php $ehFavorito = in_array($livro['id'] ?? '', $favoritosApiIds); ?>
                        <form method="POST" action="acoes/favoritar.php" class="form-favorito">
                            <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['id'] ?? ''); ?>">
                            <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                            <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                            <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                            <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                            <button type="submit" class="btn-coracao"><?php echo $ehFavorito ? '❤️' : '🤍'; ?></button>
                        </form>

                        <form method="POST" action="acoes/adicionar_biblioteca.php">
                            <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['id'] ?? ''); ?>">
                            <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                            <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                            <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                            <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                            <button type="submit" class="btn-mini">+ Adicionar</button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

        </section>

        <?php if ($pesquisa): ?>
        <section class="resultados-busca">

            <h2>Resultados da busca</h2>

            <div class="livros">

                <?php foreach ($resultados as $livro):
                    $info = $livro['volumeInfo'];
                    $titulo = $info['title'] ?? "Sem título";
                    $autor = $info['authors'][0] ?? "Autor";
                    $imagem = $info['imageLinks']['thumbnail'] ?? "https://via.placeholder.com/150x220";

                    $linkVerLivro = "paginas/ver_livro.php?" . http_build_query([
                        'api_id' => $livro['id'] ?? '',
                        'titulo' => $titulo,
                        'autor' => $autor,
                        'isbn' => $info['industryIdentifiers'][0]['identifier'] ?? '',
                        'capa' => $imagem
                    ]);
                ?>
                <div class="card">
                    <a href="<?php echo $linkVerLivro; ?>">
                        <img src="<?php echo $imagem; ?>" alt="<?php echo htmlspecialchars($titulo); ?>">
                    </a>
                    <a href="<?php echo $linkVerLivro; ?>" class="link-titulo">
                        <h3><?php echo htmlspecialchars($titulo); ?></h3>
                    </a>
                    <p><?php echo htmlspecialchars($autor); ?></p>
                     
                    <?php $ehFavorito = in_array($livro['id'] ?? '', $favoritosApiIds); ?>
                    <form method="POST" action="acoes/favoritar.php" class="form-favorito">
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['id'] ?? ''); ?>">
                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                        <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                        <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                        <button type="submit" class="btn-coracao"><?php echo $ehFavorito ? '❤️' : '🤍'; ?></button>
                    </form>

                    <form method="POST" action="acoes/adicionar_biblioteca.php">
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['id'] ?? ''); ?>">
                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>">
                        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($autor); ?>">
                        <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($info['industryIdentifiers'][0]['identifier'] ?? ''); ?>">
                        <input type="hidden" name="capa" value="<?php echo htmlspecialchars($imagem); ?>">
                        <button type="submit" class="btn-mini">+ Adicionar</button>
                    </form>
                </div>
                <?php endforeach; ?>

            </div>

        </section>
        <?php endif; ?>

    </main>

</div>

<?php include "includes/footer.php"; ?>

