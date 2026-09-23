<?php
session_start();
include __DIR__ . "/../includes/config.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {

        $stmt = $conn->prepare("SELECT id, username, password FROM utilizadores WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {

            $utilizador = $resultado->fetch_assoc();

            if (password_verify($password, $utilizador['password'])) {
                $_SESSION['user_id'] = $utilizador['id'];
                $_SESSION['username'] = $utilizador['username'];
                header("Location: ../index.php");
                exit;
            } else {
                $erro = "Senha incorreta.";
            }

        } else {
            $erro = "Utilizador não encontrado.";
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
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>

        <p>Não tens conta? <a href="registo.php">Criar conta</a></p>

    </div>

</body>
</html>