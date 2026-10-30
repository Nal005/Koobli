<?php
session_start();

// Quem já tem sessão iniciada não precisa de criar conta
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$nome || !$username || !$email || !$password) {
        $erro = "Preenche todos os campos.";
    } elseif (mb_strlen($password) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } else {

        $check = $conn->prepare("SELECT id FROM utilizadores WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $resultado = $check->get_result();

        if ($resultado->num_rows > 0) {
            $erro = "Username ou email já estão em uso.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO utilizadores (nome, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $username, $email, $hash);

            if ($stmt->execute()) {
                // Novo id de sessão depois do registo (evita session fixation)
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']); // a nova sessão recebe um token CSRF novo
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                header("Location: ../index.php");
                exit;
            } else {
                $erro = "Erro ao criar conta. Tenta novamente.";
            }
        }

    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registo - KOOBLI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="auth-container">

        <h2>Criar conta</h2>

        <?php if ($erro): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form method="POST">
            <?php echo campoCsrf(); ?>
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Senha (mínimo 8 caracteres)" minlength="8" required>
            <button type="submit">Registar</button>
        </form>

        <p>Já tens conta? <a href="login.php">Entrar</a></p>

    </div>

</body>
</html>