<?php
$raiz = $raiz ?? "";
$paginaAtual = basename($_SERVER['PHP_SELF']);

function classeAtiva($pagina, $paginaAtual) {
    return $pagina === $paginaAtual ? ' class="active"' : '';
}
?>
<div class="sidebar-wrapper">

    <div class="brand-bar">
        <img src="<?php echo $raiz; ?>imgs/noback.png" alt="Koobli" class="logo-icon">
    </div>

    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="<?php echo $raiz; ?>index.php" title="Home"<?php echo classeAtiva("index.php", $paginaAtual); ?>><i class="fi fi-rs-home"></i></a>
            <a href="<?php echo $raiz; ?>paginas/biblioteca.php" title="Biblioteca"<?php echo classeAtiva("biblioteca.php", $paginaAtual); ?>><i class="fi fi-rs-books"></i></a>
            <a href="<?php echo $raiz; ?>paginas/favoritos.php" title="Favoritos"<?php echo classeAtiva("favoritos.php", $paginaAtual); ?>><i class="fi fi-rs-bookmark"></i></a>
            <?php // Notificações e Mensagens escondidas até as páginas existirem ?>
        </nav>

        <div class="sidebar-bottom">
            <?php // Perfil escondido até existir a página (issue #12) ?>
            <a href="<?php echo $raiz; ?>paginas/logout.php" title="Sair" class="sidebar-logout"><i class="fi fi-rs-exit"></i></a>
        </div>
    </aside>

</div>
