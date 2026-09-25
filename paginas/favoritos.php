<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/header.php";

include __DIR__ . "/../includes/config.php";

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        f.id AS favorito_id,
        l.id AS livro_id,
        l.api_id,
        l.titulo,
        l.autor_principal,
        l.capa
    FROM favoritos f
    JOIN livros l ON f.livro_id = l.id
    WHERE f.utilizador_id = ?
    ORDER BY f.id DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultado = $stmt->get_result();

$favoritos = [];
while ($linha = $resultado->fetch_assoc()) {
    $favoritos[] = $linha;
}
?>

<div class="app-layout">

    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="dashboard-content">

        <h2 class="titulo-pagina">Meus Favoritos</h2>

        <?php if (empty($favoritos)): ?>
            <p class="vazio">Ainda não favoritaste nenhum livro.</p>
        <?php else: ?>
            <div class="livros-grid">
                <?php foreach ($favoritos as $livro): ?>
                <div class="card">
                    <a href="livro.php?id=<?php echo $livro['livro_id']; ?>">
                        <img src="<?php echo htmlspecialchars($livro['capa'] ?: 'https://via.placeholder.com/150x220'); ?>" alt="<?php echo htmlspecialchars($livro['titulo']); ?>">
                    </a>
                    <a href="livro.php?id=<?php echo $livro['livro_id']; ?>" class="link-titulo">
                        <h3><?php echo htmlspecialchars($livro['titulo']); ?></h3>
                    </a>
                    <p><?php echo htmlspecialchars($livro['autor_principal']); ?></p>

                    <form method="POST" action="../acoes/favoritar.php" class="form-favorito">
                        <input type="hidden" name="api_id" value="<?php echo htmlspecialchars($livro['api_id']); ?>">
                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>">
                        <input type="hidden" name="autor" value="<?php echo htmlspecialchars($livro['autor_principal']); ?>">
                        <input type="hidden" name="isbn" value="">
                        <input type="hidden" name="capa" value="<?php echo htmlspecialchars($livro['capa']); ?>">
                        <button type="submit" class="btn-coracao">❤️</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>