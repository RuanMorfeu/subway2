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

$nomedestinatario = $_POST['withdrawName'];
$chavepix = $_POST['withdrawCPF'];
$valorsaque = $_POST['withdrawValue'];
$tipochave = $_POST['tipochavepix'];


include '../conectarbanco.php';


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
$usuariocontademo = $rowusuario['contademo'];

if ($usuariocontademo == 'sim'){
    header('location: /painel'); 
    exit();
}

if ($valorsaque > $saldoatual) {
    header('location: /painel'); 
    exit();
}

$saldonovo = $saldoatual - $valorsaque;

$atualizasaldo = "UPDATE appconfig SET saldo = '$saldonovo' WHERE email = '$usuarioaposta'";
$resultadoatualizasaldo = $conn->query($atualizasaldo);

$sql = 'INSERT INTO saques (email, tipochave, externalreference, valor, status) VALUES (?, ?, ?, ?, "aguardando")';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $usuarioaposta, $tipochave, $chavepix, $valorsaque);
$stmt->execute();

$conn->close();
header('location: /saque/concluido.php?valor='.$valorsaque.'');
