<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/header.php";

include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT nome, username, foto_perfil, biografia, data_registo FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$utilizador = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT estado, COUNT(*) AS total FROM biblioteca WHERE utilizador_id = ? GROUP BY estado");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultado = $stmt->get_result();

$totais = [];
while ($linha = $resultado->fetch_assoc()) {
    $totais[$linha['estado']] = $linha['total'];
}

$rotulos = [
    'quero_ler' => 'Quero Ler',
    'lendo' => 'Lendo',
    'lido' => 'Lido',
    'abandonado' => 'Abandonado'
];

$erro = $_SESSION['erro_perfil'] ?? "";
unset($_SESSION['erro_perfil']);
?>

<div class="app-layout">

    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="dashboard-content">

        <h2 class="titulo-pagina">Meu Perfil</h2>

        <?php if ($erro): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <div class="perfil-topo">
            <?php if ($utilizador['foto_perfil']): ?>
                <img src="../uploads/<?php echo htmlspecialchars($utilizador['foto_perfil']); ?>" alt="Foto de perfil" class="perfil-foto">
            <?php else: ?>
                <img src="../imgs/raposa.png" alt="Raposa do Koobli" class="perfil-foto perfil-foto-raposa">
            <?php endif; ?>

            <div class="perfil-info">
                <h3><?php echo htmlspecialchars($utilizador['nome']); ?></h3>
                <p class="perfil-detalhe">@<?php echo htmlspecialchars($utilizador['username']); ?></p>
                <p class="perfil-detalhe">Membro desde <?php echo date("d/m/Y", strtotime($utilizador['data_registo'])); ?></p>
                <?php if ($utilizador['biografia']): ?>
                    <p class="perfil-bio"><?php echo nl2br(htmlspecialchars($utilizador['biografia'])); ?></p>
                <?php endif; ?>
            </div>

            <button type="button" class="btn-adicionar" id="btn-editar">Editar perfil</button>
        </div>

        <form method="POST" action="../acoes/atualizar_perfil.php" enctype="multipart/form-data" class="form-perfil" id="form-perfil" style="display:none;">
            <?php echo campoCsrf(); ?>

            <label>Nome
                <input type="text" name="nome" value="<?php echo htmlspecialchars($utilizador['nome']); ?>" maxlength="100" required>
            </label>

            <label>Biografia
                <textarea name="biografia"><?php echo htmlspecialchars($utilizador['biografia'] ?? ""); ?></textarea>
            </label>

            <label>Foto (JPG, PNG ou WebP, até 2 MB)
                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
            </label>

            <?php if ($utilizador['foto_perfil']): ?>
                <label><input type="checkbox" name="remover_foto"> Remover foto</label>
            <?php endif; ?>

            <button type="submit" class="btn-adicionar">Guardar</button>
        </form>

        <div class="perfil-estados">
            <?php foreach ($rotulos as $chave => $rotulo): ?>
                <a href="biblioteca.php" class="perfil-estado" data-estante="<?php echo $chave; ?>">
                    <strong><?php echo $totais[$chave] ?? 0; ?></strong>
                    <span><?php echo $rotulo; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

    </main>

</div>

<script>
document.getElementById('btn-editar').addEventListener('click', () => {
    const form = document.getElementById('form-perfil');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
