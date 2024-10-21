<?php

include './../../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if ($status == 'cancelado') {
        $querysaque = "SELECT * FROM saques WHERE id = ?";
        $stmt = $conn->prepare($querysaque);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $resultadoconsultasaque = $stmt->get_result();
        $rowusaque = $resultadoconsultasaque->fetch_assoc();

        $emailsaque = $rowusaque['email'];
        $valorsaque = $rowusaque['valor'];

        $queryusuario = "SELECT * FROM appconfig WHERE email = ?";
        $stmt = $conn->prepare($queryusuario);
        $stmt->bind_param("s", $emailsaque);
        $stmt->execute();
        $resultadoconsultausuario = $stmt->get_result();
        $rowusuario = $resultadoconsultausuario->fetch_assoc();

        $saldoatualusuario = $rowusuario['saldo'];
      
        $novosaldo = $saldoatualusuario + $valorsaque;

        $queryatualizausuario = "UPDATE appconfig SET saldo = '$novosaldo' WHERE email = '$emailsaque'";
        $atualizausuario = $conn->query($queryatualizausuario);

        $queryatualizasaque = "DELETE FROM saques WHERE id = $id";
        $atualizasaque = $conn->query($queryatualizasaque);
        
        echo json_encode(['success' => true, 'message' => 'Usuário Atualizado!']);
        exit;
    }

    else if ($status == 'aprovar'){
        $queryapp = "SELECT * FROM app";
        $stmt = $conn->prepare($queryapp);
        $stmt->execute();
        $resultadoconsultaapp = $stmt->get_result();
        $rowapp = $resultadoconsultaapp->fetch_assoc();

        $querysaque = "SELECT * FROM saques WHERE id = ?";
        $stmt = $conn->prepare($querysaque);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $resultadoconsultasaque = $stmt->get_result();
        $rowusaque = $resultadoconsultasaque->fetch_assoc();
        
        $idgateway = '1';
        $querygateway = "SELECT * FROM gateway WHERE id = ?";
        $stmt = $conn->prepare($querygateway);
        $stmt->bind_param("s", $idgateway);
        $stmt->execute();
        $resultadoconsultagateway = $stmt->get_result();
        $rowugateway = $resultadoconsultagateway->fetch_assoc();

        $clienteid = $rowugateway['client_id'];
        $clientsecret = $rowugateway['client_secret'];
        $tipochave = $rowusaque['tipochave'];
        $valorssaque = $rowusaque['valor'] * floatval($rowapp['taxa_saque']);
        $chavepix = $rowusaque['externalreference'];

        $data["key"] = $chavepix;
        $data["typeKey"] = $tipochave;
        $data["value"] = $valorssaque;
        
        $payload = json_encode($data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ws.suitpay.app/api/v1/gateway/pix-payment');
        curl_setopt($ch, CURLOPT_HEADER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        'ci: '.$clienteid.'',
        'cs: '.$clientsecret.''
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $cashout = curl_exec($ch);

        

        $totalsaques = $rowapp['saques'];
        $totalsacado = $rowapp['saques_valor'];

        $novototaldesaques = $totalsaques + 1;
        $novototalsacado = $totalsacado + $valorssaque;

        $queryatualizaapp = "UPDATE app SET saques = '$novototaldesaques', saques_valor = '$novototalsacado'";
        $atualizaapp = $conn->query($queryatualizaapp);
     
        $queryatualizasaque = "UPDATE saques SET status = '$status' WHERE id = $id";
        $atualizasaque = $conn->query($queryatualizasaque);

        echo json_encode(['success' => true, 'message' => 'Usuário Atualizado!']);
        exit;
    }
    
    else {
        echo json_encode(['error' => true, 'message' => 'FEZ PN!']);
    }


}
?>
