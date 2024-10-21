<?php
include './../../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query de leitura para o número de depósitos nas últimas 24 horas com base no status
$sqlCountPaid = "SELECT COUNT(*) as depositCount FROM confirmar_deposito WHERE  status = 'PAID_OUT'";

// Adicionar cláusula WHERE se o parâmetro status estiver presente

$stmt = $conn->prepare($sqlCountPaid);

$stmt->execute();
$resultCountLastPaid = $stmt->get_result();
$stmt->close();

if ($resultCountLastPaid->num_rows > 0) {
    $rowCountLast24h = $resultCountLastPaid->fetch_assoc();
    echo $rowCountLast24h["depositCount"];
} else {
    echo "0";
}

// Fechar a conexão com o banco de dados
$conn->close();
?>
