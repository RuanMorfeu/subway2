<?php

session_start();
if (!isset($_SESSION['email'])) {
    header('Location: ../');
    exit();
}

include '../conectarbanco.php';

$usuarioaposta = $_SESSION['email'];
$session = $_POST['session'];
$tipo = $_POST['resultado'];
$valoraposta = $_POST['valoraposta'];
$valorganho = $_POST['valorganho'];


$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

$queryconsultausuario = "SELECT * FROM appconfig WHERE email = ?";
$stmt = $conn->prepare($queryconsultausuario);
$stmt->bind_param("s", $usuarioaposta);
$stmt->execute();
$resultadoconsultausuario = $stmt->get_result();
$rowusuario = $resultadoconsultausuario->fetch_assoc();

$queryconsultabet = "SELECT * FROM apostas where aposta = '$session'";
$resultadoconsultaaposta = $conn->query($queryconsultabet);
$rowbet = $resultadoconsultaaposta->fetch_assoc();
$saldo = $rowusuario['saldo'];
$statusbet = $rowbet['status'];

if (!$rowbet) {
    header('Location: https://google.com');
    exit;
}

if ($statusbet == 'finalizado') {
    $queryconsultabanimento = "SELECT * FROM banimento where usuario = '$usuarioaposta'";
    $resultadoconsultabanimento = $conn->query($queryconsultabanimento);
    $rowbanimento = $resultadoconsultabanimento->fetch_assoc();

    $tentativasparabanimento = $rowbanimento['numerotentativas'];

    if ($tentativasparabanimento == '3') {
        $banimentousuario = "DELETE FROM appconfig WHERE email = '$usuarioaposta'";
        $deletausuario = $conn->query($banimentousuario);
        session_destroy();
        exit;
    }

    if (!$rowbanimento) {
        $querybanimento = "INSERT INTO banimento (usuario, numerotentativas) VALUES ('$usuarioaposta', '1')";
        $resultadoquerybanimento = $conn->query($querybanimento);
    }
    
    $somatentativa = $tentativasparabanimento + 1;

    $adicionatentativa = "UPDATE banimento SET numerotentativas = '$somatentativa' WHERE usuario = '$usuarioaposta'";
    $resultadoadicaobanimento = $conn->query($adicionatentativa);

}

if ($tipo == 'win' && $statusbet == 'jogando') {
    $somaentrevalores = $saldo + $valorganho;
    $somarvalores = "UPDATE appconfig SET saldo = '$somaentrevalores' WHERE email = '$usuarioaposta'";
    $resultadoquerysoma = $conn->query($somarvalores);

    $atualizarbet = "UPDATE apostas SET status = 'finalizado', resultado = '$tipo' WHERE aposta = '$session'";
    $resultadoatualizabet = $conn->query($atualizarbet);

    echo 'BET PAGANTE REALIZADA COM SUCESSO TOKENBET: '.$session.' & Valor do Ganho: '.$valorganho.'';

}

else if ($tipo == 'loss' && $statusbet == 'jogando'){
    $atualizarbet = "UPDATE apostas SET status = 'finalizado', resultado = '$tipo' WHERE aposta = '$session'";
    $resultadoatualizabet = $conn->query($atualizarbet);

    echo 'BET NAO PAGOU, TOKENBET FINALIZADO: '.$session.'';

}

else {
    header('Location: /painel');
    exit;
}

?>

