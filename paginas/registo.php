<?php
session_start();
include __DIR__ . "/../includes/config.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($nome && $username && $email && $password) {

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
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                header("Location: ../index.php");
                exit;
            } else {
                $erro = "Erro ao criar conta. Tenta novamente.";
            }
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
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Registar</button>
        </form>

        <p>Já tens conta? <a href="login.php">Entrar</a></p>

    </div>

</body>
</html>