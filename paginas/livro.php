<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";

include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/livros.php";
include __DIR__ . "/../includes/favoritos_helper.php";
include __DIR__ . "/../includes/csrf.php";

$livroId = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM livros WHERE id = ?");
$stmt->bind_param("i", $livroId);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . "/../includes/header.php";

$livro = $resultado->fetch_assoc();

$userId = $_SESSION['user_id'];
$favoritosApiIds = obterApiIdsFavoritos($conn, $userId);
$ehFavorito = in_array($livro['api_id'], $favoritosApiIds);
$mediaAvaliacoes = obterMediaAvaliacoes($conn, $livro['api_id']);

// Linha do livro na biblioteca do utilizador (null se ainda não o adicionou)
$stmtBib = $conn->prepare("SELECT id, estado, data_inicio, data_fim FROM biblioteca WHERE utilizador_id = ? AND livro_id = ?");
$stmtBib->bind_param("ii", $userId, $livroId);
$stmtBib->execute();
$livroNaBiblioteca = $stmtBib->get_result()->fetch_assoc();

// Avaliação que o utilizador já deu a este livro (null se ainda não avaliou)
$stmtMinhaAval = $conn->prepare("SELECT nota, texto FROM avaliacoes WHERE utilizador_id = ? AND livro_id = ?");
$stmtMinhaAval->bind_param("ii", $userId, $livroId);
$stmtMinhaAval->execute();
$minhaAvaliacao = $stmtMinhaAval->get_result()->fetch_assoc();

$rotulos = [
    'quero_ler' => 'Quero Ler',
    'lendo' => 'Lendo',
    'lido' => 'Lido',
    'abandonado' => 'Abandonado'
];

// Só mostra as datas que fazem sentido para o estado atual
// (a BD guarda-as ao passar a Lendo ou Lido e não as apaga ao mudar de estado)
$datasLeitura = [];
if ($livroNaBiblioteca) {
    if ($livroNaBiblioteca['estado'] !== 'quero_ler' && $livroNaBiblioteca['data_inicio']) {
        $datasLeitura[] = "Começaste a ler a " . date('d/m/Y', strtotime($livroNaBiblioteca['data_inicio']));
    }
    if ($livroNaBiblioteca['estado'] === 'lido' && $livroNaBiblioteca['data_fim']) {
        $datasLeitura[] = "Acabaste a " . date('d/m/Y', strtotime($livroNaBiblioteca['data_fim']));
    }
}

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
    <?php include __DIR__ . "/../includes/sidebar.php"; ?>
    <main class="dashboard-content">

        <div class="livro-detalhe">

            <img src="<?php echo htmlspecialchars(urlCapa($livro['capa'], $raiz)); ?>" alt="<?php echo htmlspecialchars($livro['titulo']); ?>">

            <div class="livro-detalhe-info">
                <h2><?php echo htmlspecialchars($livro['titulo']); ?></h2>
                <p class="hero-autor"><?php echo htmlspecialchars($livro['autor_principal']); ?></p>

                <div class="hero-estrelas">
                    <?php if ($mediaAvaliacoes['total'] > 0): ?>
                        <?php echo iconesEstrelas($mediaAvaliacoes['media']); ?>
                        <span><?php echo number_format($mediaAvaliacoes['media'], 1, ",", ""); ?> (<?php echo $mediaAvaliacoes['total'] == 1 ? "1 avaliação" : $mediaAvaliacoes['total'] . " avaliações"; ?>)</span>
                    <?php else: ?>
                        <span>Ainda sem avaliações</span>
                    <?php endif; ?>
                </div>

                <form method="POST" action="../acoes/favoritar.php" class="form-favorito">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['api_id']); ?>">
                    <button type="submit" class="btn-coracao" title="<?php echo $ehFavorito ? 'Remover dos favoritos' : 'Adicionar aos favoritos'; ?>"><i class="fi <?php echo $ehFavorito ? 'fi-ss-heart' : 'fi-rs-heart'; ?>"></i></button>
                </form>

                <?php if ($livroNaBiblioteca): ?>
                    <div class="livro-biblioteca">
                        <span class="livro-estado" data-estante="<?php echo $livroNaBiblioteca['estado']; ?>"><?php echo $rotulos[$livroNaBiblioteca['estado']]; ?></span>

                        <form method="POST" action="../acoes/atualizar_estado.php" class="form-estado">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="biblioteca_id" value="<?php echo $livroNaBiblioteca['id']; ?>">
                            <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">
                            <select name="estado" onchange="this.form.submit()">
                                <option value="" disabled selected>Mudar estado…</option>
                                <?php foreach ($rotulos as $valor => $texto): ?>
                                    <?php // O estado atual fica desativado: escolhê-lo outra vez voltaria a gravar a data de hoje ?>
                                    <option value="<?php echo $valor; ?>" <?php echo $valor === $livroNaBiblioteca['estado'] ? 'disabled' : ''; ?>><?php echo $texto; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <form method="POST" action="../acoes/remover_biblioteca.php" onsubmit="return confirm('Remover este livro da biblioteca?');">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="biblioteca_id" value="<?php echo $livroNaBiblioteca['id']; ?>">
                            <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">
                            <button type="submit" class="btn-mini">Remover</button>
                        </form>
                    </div>

                    <?php if ($datasLeitura): ?>
                        <p class="livro-datas"><?php echo implode(" · ", $datasLeitura); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <form method="POST" action="../acoes/adicionar_biblioteca.php">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['api_id']); ?>">
                        <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">
                        <button type="submit" class="btn-adicionar">+ Adicionar à biblioteca</button>
                    </form>
                <?php endif; ?>
            </div>

        </div>

        <section class="avaliacoes-section">
            <div class="form-avaliar">
                <h3><?php echo $minhaAvaliacao ? 'A tua avaliação' : 'Deixe sua avaliação'; ?></h3>
                <form method="POST" action="../acoes/avaliar.php">
                    <?php echo campoCsrf(); ?>
                    <input type="hidden" name="livro_id" value="<?php echo $livro['id']; ?>">

                    <div class="estrelas-input">
                        <?php // De 5 para 1, como antes: o CSS inverte a ordem para pintar as estrelas anteriores ?>
                        <?php for ($n = 5; $n >= 1; $n--): ?>
                            <input type="radio" name="nota" id="nota<?php echo $n; ?>" value="<?php echo $n; ?>" <?php echo ($minhaAvaliacao['nota'] ?? 0) == $n ? 'checked' : ''; ?>><label for="nota<?php echo $n; ?>"><i class="fi fi-rs-star"></i><i class="fi fi-ss-star"></i></label>
                        <?php endfor; ?>
                    </div>

                    <textarea name="texto" placeholder="Escreve o que achaste do livro (opcional)..."><?php echo htmlspecialchars($minhaAvaliacao['texto'] ?? ''); ?></textarea>

                    <button type="submit" class="btn-adicionar"><?php echo $minhaAvaliacao ? 'Atualizar avaliação' : 'Enviar avaliação'; ?></button>
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
                        <span class="avaliacao-estrelas"><?php echo iconesEstrelas($aval['nota']); ?></span>
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
                <?php echo campoCsrf(); ?>
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