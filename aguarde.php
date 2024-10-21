<?php

session_start();
if ( !isset( $_SESSION[ 'email' ] ) ) {
    header( 'Location: ../' );
    exit();
}


$usuarioaposta = $_SESSION[ 'email' ];

if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
    die( 'Acesso inválido.' );
}

$valorposta = $_POST[ 'valoraposta' ];

include '../conectarbanco.php';

$tokenaposta = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 36)), 0, 24);
$tokenaposta = base64_encode($tokenaposta);

$conn = new mysqli( $config[ 'db_host' ], $config[ 'db_user' ], $config[ 'db_pass' ], $config[ 'db_name' ] );

if ( $conn->connect_error ) {
    die( 'Conexão falhou: ' . $conn->connect_error );
}

$queryconsultausuario = "SELECT * FROM appconfig WHERE email = ?";
$stmt = $conn->prepare($queryconsultausuario);
$stmt->bind_param("s", $usuarioaposta);
$stmt->execute();
$resultadoconsultausuario = $stmt->get_result();
$rowusuario = $resultadoconsultausuario->fetch_assoc();

$saldoatual = $rowusuario['saldo'];

if ($saldoatual < $valorposta) {
    header('location: /painel');
    exit;
}

$saldonovo = $saldoatual - $valorposta;

$atualizasaldo = "UPDATE appconfig SET saldo = '$saldonovo' WHERE email = '$usuarioaposta'";
$resultadoatualizasaldo = $conn->query($atualizasaldo);

$sql = 'INSERT INTO apostas (usuario, status, aposta, resultado) VALUES (?, "jogando", ?, "aguardando")';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $usuarioaposta, $tokenaposta);
$stmt->execute();

$conn->close();

header('location: /jogar/?aposta='.$valorposta.'&tokenaposta='.$tokenaposta.'')
?>