<?php

include './../../conectarbanco.php';

ini_set('display_errors', 1);
  ini_set('display_startup_erros', 1);
  error_reporting(E_ALL); 

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['emailadm'])) {
    header("Location: ../login");
    exit();
}

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$idSaque = intval($_GET['id']);

$sql = "SELECT id, email, nome, pix, valor, status FROM saque_afiliado where id = ?";
$withdraw = $conn->prepare($sql);
$withdraw->bind_param("i", $idSaque);
$withdraw->execute();
$withdraw = $withdraw->get_result();
$withdraw = $withdraw->fetch_assoc();

$sql2 = "SELECT * FROM app";
$app = $conn->prepare($sql2);
$app->execute();
$app  = $app->get_result();
$app = $app ->fetch_assoc();

$sql3 = "SELECT * FROM gateway";
$gateway = $conn->prepare($sql3);
$gateway->execute();
$gateway  = $gateway->get_result();
$gateway = $gateway ->fetch_assoc();

if (!$withdraw) {
    $msg = "Saque não encontrado";
    header("Location: ../saques-afiliados");
    exit;
}

if ($withdraw['status'] != 'Aguardando Aprovação') {
    $_SESSION['error'] = ["Saque já foi processado"];
    header("Location: ../saques-afiliados");
    exit;
}

$withdrawal_fee = $app['taxa_saque'];
$withdrawal_value = $withdraw['valor'] * (1 - $withdrawal_fee / 100);

$ci = $gateway['client_id'];
$cs = $gateway['client_secret'];

$type = 'POST';
$url = 'https://ws.suitpay.app/api/v1/gateway/pix-payment';
$headers = [
    'Content-Type: application/json',
    'ci: ' . $ci,
    'cs: ' . $cs
];
$payload = [
    'value' => $withdrawal_value,
    'typeKey' => 'document',
    'key' => $withdraw['pix'],
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$response = json_decode($response, true);

var_dump($response);

if ($response['response'] != 'OK') {
    $msg = "Saque não processado";
    header("Location: ../saques-afiliados");
    exit;
}

$sql4 = "UPDATE saque_afiliado SET status = 'Pago' WHERE id = ?";
$app = $conn->prepare($sql4);
$app->bind_param("i", $idSaque);
$app = $app->execute();

/* $sql5 = "UPDATE appconfig SET pix_gerado = 0 WHERE email = ?";
$app = $conn->prepare($sql5);
$app->bind_param("s", $withdraw['email']);
$app = $app->execute(); */


$_SESSION['success'] = ["Saque processado com sucesso"];
header("Location: " . "../saques-afiliados");
exit;
