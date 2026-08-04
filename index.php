<?php
session_start();
include "includes/header.php";

include "includes/apikey.php";
$apiKey = GOOGLE_BOOKS_API_KEY;

$pesquisa = $_GET['q'] ?? "";

$resultados = [];

$url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($pesquisa) . "&maxResults=8&key=" . $apiKey;

$resposta = @file_get_contents($url);

if($resposta){
    $dados = json_decode($resposta,true);
    $resultados = $dados['items'] ?? [];
}
?>


<div class="dashboard">

    <aside class="sidebar">

        <h2>KOOBLI</h2>

        <a href="#">Home</a>
        <a href="#">Biblioteca</a>
        <a href="#">favoritos</a>
        <a href="#">perfil</a>

    </aside>

    <main class="content">

        <form class="search" method="GET">

            <input
                type="text"
                name="q"
                placeholder="Search books..."
                value="<?php echo htmlspecialchars($pesquisa); ?>">

            <button>Search</button>

        </form>

        <h2>Popular Books</h2>

        <div class="livros">

            <?php foreach($resultados as $livro):

                $info = $livro['volumeInfo'];

                $titulo = $info['title'] ?? "Sem título";

                $autor = $info['authors'][0] ?? "Autor";

                $imagem = $info['imageLinks']['thumbnail']
                ?? "https://via.placeholder.com/150x220";

            ?>

            <div class="card">

                <img src="<?php echo $imagem; ?>">

                <h3><?php echo $titulo; ?></h3>

                <p><?php echo $autor; ?></p>

            </div>

            <?php endforeach; ?>

        </div>

    </main>

</div>

<?php include "includes/footer.php"; ?>