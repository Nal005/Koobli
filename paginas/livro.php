<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/header.php";

include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/favoritos_helper.php";

$livroId = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM livros WHERE id = ?");
$stmt->bind_param("i", $livroId);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ../index.php");
    exit;
}

$livro = $resultado->fetch_assoc();

$userId = $_SESSION['user_id'];
$favoritosApiIds = obterApiIdsFavoritos($conn, $userId);
$ehFavorito = in_array($livro['api_id'], $favoritosApiIds);


$stmtAval = $conn->prepare("
    SELECT a.nota, a.texto, a.data_avaliacao, u.nome, u.username
    FROM avaliacoes a
    JOIN utilizadores u ON a.utilizador_id = u.id
    WHERE a.livro_id = ?
    ORDER BY a.data_avaliacao DESC
");
$stmtAval->bind_param("i", $livroId);
$stmtAval->execute();
$avaliacoes = $stmtAval->get_result();
?>

<div class="app-layout">
    <div class="sidebar-wrapper">
        <div class="brand-bar">
            <img src="../imgs/noback.png" alt="Koobli" class="logo-icon">
        </div>  
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="../index.php" title="Home" class="active"><i class="fi fi-rs-home"></i></a>
                <a href="biblioteca.php" title="Biblioteca"><i class="fi fi-rs-books"></i></a>
                <a href="favoritos.php" title="Favoritos"><i class="fi fi-rs-bookmark"></i></a>
                <a href="notificacoes.php" title="Notificações"><i class="fi fi-rs-bell"></i></a>
                <a href="mensagens.php" title="Mensagens"><i class="fi fi-rs-comment"></i></a>
            </nav>

            <div class="sidebar-bottom">
                <a href="perfil.php" title="Perfil" class="sidebar-perfil"><i class="fi fi-rs-user"></i></a>
                <a href="logout.php" title="Sair" class="sidebar-logout"><i class="fi fi-rs-exit"></i></a>
            </div>
        </aside>
    </div>
    <main class="dashboard-content">

        <div class="livro-detalhe">

            <img src="<?php echo htmlspecialchars($livro['capa'] ?: 'https://via.placeholder.com/200x300'); ?>" alt="<?php echo htmlspecialchars($livro['titulo']); ?>">

            <div class="livro-detalhe-info">
                <h2><?php echo htmlspecialchars($livro['titulo']); ?></h2>
                <p class="hero-autor"><?php echo htmlspecialchars($livro['autor_principal']); ?></p>

                <form method="POST" action="../acoes/favoritar.php" class="form-favorito">
                    <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['api_id']); ?>">
                    <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>">
                    <input type="hidden" name="autor" value="<?php echo htmlspecialchars($livro['autor_principal']); ?>">
                    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($livro['isbn']); ?>">
                    <input type="hidden" name="capa" value="<?php echo htmlspecialchars($livro['capa']); ?>">
                    <button type="submit" class="btn-coracao"><?php echo $ehFavorito ? '❤️' : '🤍'; ?></button>
                </form>

                <form method="POST" action="../acoes/adicionar_biblioteca.php">
                    <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['api_id']); ?>">
                    <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>">
                    <input type="hidden" name="autor" value="<?php echo htmlspecialchars($livro['autor_principal']); ?>">
                    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($livro['isbn']); ?>">
                    <input type="hidden" name="capa" value="<?php echo htmlspecialchars($livro['capa']); ?>">
                    <button type="submit" class="btn-adicionar">+ Adicionar à biblioteca</button>
                </form>
            </div>

        </div>

        <section class="avaliacoes-section">
            <div class="form-avaliar">
                <h3>Deixe sua avaliação</h3>
                <form method="POST" action="../acoes/avaliar.php">
                    <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">

                    <div class="estrelas-input">
                        <input type="radio" name="nota" id="nota5" value="5"><label for="nota5">★</label>
                        <input type="radio" name="nota" id="nota4" value="4"><label for="nota4">★</label>
                        <input type="radio" name="nota" id="nota3" value="3"><label for="nota3">★</label>
                        <input type="radio" name="nota" id="nota2" value="2"><label for="nota2">★</label>
                        <input type="radio" name="nota" id="nota1" value="1"><label for="nota1">★</label>
                    </div>

                    <textarea name="texto" placeholder="Escreve o que achaste do livro (opcional)..."></textarea>

                    <button type="submit" class="btn-adicionar">Enviar avaliação</button>
                </form>
            </div>

            <h3>Avaliações</h3>

            <?php if ($avaliacoes->num_rows === 0): ?>
                <p class="vazio">Ainda não há avaliações para este livro.</p>
            <?php else: ?>
                <?php while ($aval = $avaliacoes->fetch_assoc()): ?>
                <div class="avaliacao-card">
                    <div class="avaliacao-topo">
                        <strong><?php echo htmlspecialchars($aval['nome']); ?></strong>
                        <span class="avaliacao-estrelas"><?php echo str_repeat('★', $aval['nota']) . str_repeat('☆', 5 - $aval['nota']); ?></span>
                    </div>
                    <?php if ($aval['texto']): ?>
                        <p class="avaliacao-texto"><?php echo htmlspecialchars($aval['texto']); ?></p>
                    <?php endif; ?>
                    <span class="avaliacao-data"><?php echo date('d/m/Y', strtotime($aval['data_avaliacao'])); ?></span>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>

        </section>

        <section class="comentarios-section" id="comentarios">

            <h3>Comentários</h3>

            <form method="POST" action="../acoes/comentar.php" class="form-comentar">
                <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">
                <textarea name="comentario" placeholder="Escreve um comentário..." required></textarea>
                <button type="submit" class="btn-adicionar">Comentar</button>
            </form>

            <?php
            $stmtComentarios = $conn->prepare("
                SELECT c.comentario, c.data_comentario, u.nome
                FROM comentarios c
                JOIN utilizadores u ON c.utilizador_id = u.id
                WHERE c.livro_id = ?
                ORDER BY c.data_comentario DESC
            ");
            $stmtComentarios->bind_param("i", $livroId);
            $stmtComentarios->execute();
            $comentarios = $stmtComentarios->get_result();
            ?>

            <?php if ($comentarios->num_rows === 0): ?>
                <p class="vazio">Ainda não há comentários. Sê o primeiro!</p>
            <?php else: ?>
                <?php while ($com = $comentarios->fetch_assoc()): ?>
                <div class="comentario-card">
                    <strong><?php echo htmlspecialchars($com['nome']); ?></strong>
                    <p><?php echo nl2br(htmlspecialchars($com['comentario'])); ?></p>
                    <span class="comentario-data"><?php echo date('d/m/Y H:i', strtotime($com['data_comentario'])); ?></span>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>

        </section>
    </main>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>