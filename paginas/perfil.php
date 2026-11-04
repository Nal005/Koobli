<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/header.php";

include __DIR__ . "/../includes/config.php";

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
?>

<div class="app-layout">

    <?php include __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="dashboard-content">

        <h2 class="titulo-pagina">Meu Perfil</h2>

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
        </div>

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

<?php include __DIR__ . "/../includes/footer.php"; ?>
