<?php
session_start();

// Quem já tem sessão iniciada não precisa de fazer login
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include __DIR__ . "/../includes/config.php";
include __DIR__ . "/../includes/csrf.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    validarCsrf();

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {

        $stmt = $conn->prepare("SELECT id, username, password FROM utilizadores WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $utilizador = $stmt->get_result()->fetch_assoc();

        // A mesma mensagem nos dois casos, para não revelar se o username existe
        if ($utilizador && password_verify($password, $utilizador['password'])) {
            // Novo id de sessão depois do login (evita session fixation)
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']); // a nova sessão recebe um token CSRF novo
            $_SESSION['user_id'] = $utilizador['id'];
            $_SESSION['username'] = $utilizador['username'];
            header("Location: ../index.php");
            exit;
        } else {
            $erro = "Username ou senha incorretos.";
        }

    } else {
        $erro = "Preenche todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - KOOBLI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="auth-container">

        <h2>Entrar</h2>

        <?php if ($erro): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form method="POST">
            <?php echo campoCsrf(); ?>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <p>Não tens conta? <a href="registo.php">Criar conta</a></p>

    </div>

</body>
</html>