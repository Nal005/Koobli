<?php
$raiz = "../";
include __DIR__ . "/../includes/auth.php";
include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $userId = $_SESSION['user_id'];
    $nome = trim($_POST['nome'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $foto = $_FILES['foto'] ?? null;
    $pasta = __DIR__ . "/../uploads/";
    $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $erro = "";

    $stmt = $conn->prepare("SELECT foto_perfil FROM utilizadores WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $fotoAtual = $stmt->get_result()->fetch_assoc()['foto_perfil'];
    $fotoNova = isset($_POST['remover_foto']) ? null : $fotoAtual;

    if ($nome === '' || mb_strlen($nome) > 100) {
        $erro = "O nome tem de ter entre 1 e 100 caracteres.";
    } elseif ($foto && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
        $imagem = $foto['error'] === UPLOAD_ERR_OK ? getimagesize($foto['tmp_name']) : false;

        if ($foto['error'] === UPLOAD_ERR_INI_SIZE || $foto['size'] > 2 * 1024 * 1024) {
            $erro = "A foto tem de ter no máximo 2 MB.";
        } elseif (!$imagem || !isset($extensoes[$imagem['mime']])) {
            $erro = "A foto tem de ser JPG, PNG ou WebP.";
        } else {
            if (!is_dir($pasta)) {
                mkdir($pasta);
            }
            $fotoNova = "perfil_" . $userId . "_" . time() . "." . $extensoes[$imagem['mime']];

            if (!move_uploaded_file($foto['tmp_name'], $pasta . $fotoNova)) {
                $erro = "Não foi possível guardar a foto. Tenta novamente.";
            }
        }
    }

    if ($erro) {
        $_SESSION['erro_perfil'] = $erro;
    } else {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, biografia = ?, foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $biografia, $fotoNova, $userId);
        $stmt->execute();

        if ($fotoAtual && $fotoAtual !== $fotoNova && is_file($pasta . $fotoAtual)) {
            unlink($pasta . $fotoAtual);
        }
    }
}

header("Location: ../paginas/perfil.php");
exit;
