<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$raiz = "../";
include __DIR__ . "/../includes/livros.php";
include __DIR__ . "/../includes/header.php";

// Usa as capas do carrossel da página inicial, se a cache já existir
$ficheiroCache = __DIR__ . "/../cache/carrossel.json";
$livrosDestaque = file_exists($ficheiroCache) ? json_decode(file_get_contents($ficheiroCache), true) ?? [] : [];
$livrosDestaque = array_slice($livrosDestaque, 0, 6);
?>

<main class="apresentacao">

    <img src="../imgs/kooblilogof.png" alt="Koobli" class="apresentacao-logo">

    <section class="apresentacao-topo">
        <div class="apresentacao-texto">
            <h1>A tua biblioteca de livros, sempre contigo</h1>
            <p>Pesquisa livros, organiza as tuas leituras e partilha o que achaste de cada história.</p>

            <div class="apresentacao-botoes">
                <a href="registo.php" class="btn-criar-conta">Criar conta</a>
                <a href="login.php" class="btn-entrar">Entrar</a>
            </div>
        </div>

        <div class="apresentacao-raposa">
            <img src="../imgs/raposa.png" alt="Raposa do Koobli">
        </div>
    </section>

    <section class="funcionalidades">
        <div class="funcionalidade">
            <i class="fi fi-rs-books"></i>
            <h3>Biblioteca com estantes</h3>
            <p>Arruma os teus livros em Quero Ler, Lendo, Lido e Abandonado.</p>
        </div>
        <div class="funcionalidade">
            <i class="fi fi-rs-heart"></i>
            <h3>Favoritos</h3>
            <p>Guarda com um coração os livros de que mais gostas.</p>
        </div>
        <div class="funcionalidade">
            <i class="fi fi-rs-star"></i>
            <h3>Avaliações</h3>
            <p>Dá de 1 a 5 estrelas a cada livro que leres.</p>
        </div>
        <div class="funcionalidade">
            <i class="fi fi-rs-comment"></i>
            <h3>Comentários</h3>
            <p>Partilha a tua opinião e lê a de outros leitores.</p>
        </div>
    </section>

    <?php if ($livrosDestaque): ?>
        <section class="apresentacao-secao">
            <h2>Livros em destaque</h2>
            <div class="destaque-capas">
                <?php foreach ($livrosDestaque as $livro):
                    $info = $livro['volumeInfo'] ?? [];
                    $capa = $info['imageLinks']['thumbnail'] ?? "";
                    $titulo = $info['title'] ?? "Sem título";
                ?>
                    <img src="<?php echo htmlspecialchars(urlCapa($capa, $raiz)); ?>" alt="<?php echo htmlspecialchars($titulo); ?>">
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="apresentacao-secao">
        <h2>Em breve</h2>
        <div class="funcionalidades">
            <div class="funcionalidade">
                <i class="fi fi-rs-users"></i>
                <h3>Salas de leitura coletiva</h3>
                <p>Lê e discute o mesmo livro com outros leitores.</p>
            </div>
            <div class="funcionalidade">
                <i class="fi fi-rs-hourglass"></i>
                <h3>Salas de foco</h3>
                <p>Sessões de leitura com temporizador, sozinho ou acompanhado.</p>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>
