<?php
// euroxpcreate.php

include './../conectarbanco.php';

// Lê a notificação de entrada enviada pelo gateway de pagamento
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Verifica se a notificação possui os campos necessários
if (isset($data['idTransaction']) && isset($data['status'])) {
    $transactionId = $data['idTransaction'];
    $status = $data['status'];

    // Conectar ao banco de dados
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Atualiza o status da transação no banco de dados
    if ($status === 'PAID') { // Verifica se o pagamento foi concluído com sucesso
        $sql = "UPDATE confirmar_deposito SET status = 'APPROVED' WHERE externalreference = '$transactionId'";
        $conn->query($sql);

        // Aqui você pode liberar o valor ao usuário ou atualizar seu saldo
        $sql = "SELECT email, valor FROM confirmar_deposito WHERE externalreference = '$transactionId'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $valor = $row['valor'];

            // Atualiza o saldo do usuário
            $sqlUpdateSaldo = "UPDATE usuarios SET saldo = saldo + $valor WHERE email = '$email'";
            $conn->query($sqlUpdateSaldo);
        }
    } elseif ($status === 'FAILED') {
        // Se o pagamento falhar, atualize o status para "FAILED"
        $sql = "UPDATE confirmar_deposito SET status = 'FAILED' WHERE externalreference = '$transactionId'";
        $conn->query($sql);
    }

    $conn->close();
} else {
    // Caso a notificação não seja válida, registre um log para análise
    error_log("Notificação de pagamento inválida: " . $input);
}
?>
