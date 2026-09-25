<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/header.php";

include __DIR__ . "/../includes/config.php";

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT b.id AS biblioteca_id, b.estado, l.id AS livro_id, l.titulo, l.autor_principal, l.capa
    FROM biblioteca b
    JOIN livros l ON b.livro_id = l.id
    WHERE b.utilizador_id = ?
    ORDER BY b.data_adicao DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultado = $stmt->get_result();

$estantes = [
    'quero_ler' => [],
    'lendo' => [],
    'lido' => [],
    'abandonado' => []
];

while ($linha = $resultado->fetch_assoc()) {
    $estantes[$linha['estado']][] = $linha;
}

$rotulos = [
    'quero_ler' => 'Quero Ler',
    'lendo' => 'Lendo',
    'lido' => 'Lido',
    'abandonado' => 'Abandonado'
];
?>

<div class="app-layout">

    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="dashboard-content">

        <h2 class="titulo-pagina">Minha Biblioteca</h2>

        <div class="abas">
            <?php foreach ($rotulos as $chave => $rotulo): ?>
                <button class="aba-btn <?php echo $chave === 'quero_ler' ? 'ativa' : ''; ?>" data-estante="<?php echo $chave; ?>">
                    <?php echo $rotulo; ?> (<?php echo count($estantes[$chave]); ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($rotulos as $chave => $rotulo): ?>
        <div class="estante" id="estante-<?php echo $chave; ?>" style="<?php echo $chave === 'quero_ler' ? '' : 'display:none;'; ?>">

            <?php if (empty($estantes[$chave])): ?>
                <p class="vazio">Nenhum livro por aqui ainda.</p>
            <?php else: ?>
                <div class="livros-grid">
                    <?php foreach ($estantes[$chave] as $livro): ?>
                    <div class="card">
                        <a href="livro.php?id=<?php echo $livro['livro_id']; ?>">
                            <img src="<?php echo htmlspecialchars($livro['capa'] ?: 'https://via.placeholder.com/150x220'); ?>" alt="<?php echo htmlspecialchars($livro['titulo']); ?>">
                        </a>
                        <a href="livro.php?id=<?php echo $livro['livro_id']; ?>" class="link-titulo">
                            <h3><?php echo htmlspecialchars($livro['titulo']); ?></h3>
                        </a>
                        <p><?php echo htmlspecialchars($livro['autor_principal']); ?></p>

                        <form method="POST" action="../acoes/atualizar_estado.php" class="form-estado">
                            <input type="hidden" name="biblioteca_id" value="<?php echo $livro['biblioteca_id']; ?>">
                            <select name="estado" onchange="this.form.submit()">
                                <?php foreach ($rotulos as $valor => $texto): ?>
                                    <option value="<?php echo $valor; ?>" <?php echo $valor === $chave ? 'selected' : ''; ?>>
                                        <?php echo $texto; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>

    </main>

</div>

<script>
document.querySelectorAll('.aba-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.aba-btn').forEach(b => b.classList.remove('ativa'));
        document.querySelectorAll('.estante').forEach(e => e.style.display = 'none');

        btn.classList.add('ativa');
        document.getElementById('estante-' + btn.dataset.estante).style.display = 'block';
    });
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>