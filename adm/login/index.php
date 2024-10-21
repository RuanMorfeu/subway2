<?php

$vida_sessao = 15 * 60;
session_set_cookie_params($vida_sessao);
session_start();

try {
    include './../../conectarbanco.php';

    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $sql = "SELECT * FROM appconfig WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();
        
        $pwd_hashed = password_verify($senha, $result['senha']);

        if ($pwd_hashed == true && $result['isAdm'] == 1) {
            $_SESSION['emailadm'] = $email;
            header("Location: ../");
            exit();
        } else {
            $erro = "E-mail ou senha incorretos.";
        }

        $stmt->close();
    }

    $conn->close();
} catch (Exception $e) {
    var_dump($e);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png"/>
</head>

<body>

    <div class="main-login">
        <div class="left-login">
            <img src="../assets/images/profitlogo.webp" class="left-login-image" alt="imagem animada">
        </div>
        <div class="right-login">
            <div class="card-login">
                <h1>Efetue seu login</h1>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="textfield">
                    <label for="email">E-mail</label>
                    <input type="text" name="email" placeholder="Digite seu e-mail">
                </div>
                <div class="textfield">
                    <label for="senha">Senha</label>
                    <input type="password" name="senha" placeholder="Digite sua senha">
                </div>
                <?php
                if (isset($erro)) {
                    echo '<div class="error-message">' . $erro . '</div>';
                }
                ?>
                <button class="btn-login">Acessar</button>
                </form>
            </div>
        </div>

</body>
</html>