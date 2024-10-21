<?php

function make_request($url, $payload, $method = 'POST', $propHeaders = [])
{
    $headers = $propHeaders;
    
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function validaCPF($cpf) {

  // Extrai somente os números
  $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

  // Verifica se foi informado todos os digitos corretamente
  if (strlen($cpf) != 11) {
      return false;
  }

  // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
  if (preg_match('/(\d)\1{10}/', $cpf)) {
      return false;
  }

  // Faz o calculo para validar o CPF
  for ($t = 9; $t < 11; $t++) {
      for ($d = 0, $c = 0; $c < $t; $c++) {
          $d += $cpf[$c] * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;
      if ($cpf[$c] != $d) {
          return false;
      }
  }
  return true;

}

include '../conectarbanco.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

$error = "";

$success = "";
// Verifique se a conexão foi bem-sucedida
if ($conn->connect_error) {
  die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

/// Recupere o email da sessão
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Consulta para obter o saldo associado ao email na tabela appconfig
    $consulta_saldo = "SELECT saldo FROM appconfig WHERE email = '$email'";

    // Execute a consulta
    $resultado_saldo = $conn->query($consulta_saldo);

    // Verifique se a consulta foi bem-sucedida
    if ($resultado_saldo) {
        // Verifique se há pelo menos uma linha retornada
        if ($resultado_saldo->num_rows > 0) {
            // Obtenha o saldo da primeira linha
            $row = $resultado_saldo->fetch_assoc();
            $saldo = $row['saldo'];
          
      }
  }
}

function generateuuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );

}

if (isset($_POST["acao"]) && $_POST["acao"] == "SACAR") {
  $nome = $_POST["withdrawName"];
  //Let just numbers
  $cpf = preg_replace('/[^0-9]/', '', $_POST["withdrawCPF"]);
  $valor = $_POST["withdrawValue"];
  $valor = floatval(str_replace(",", ".", $valor));
  $saldoTemp = floatval(str_replace(",", ".", $saldo));
  if ($valor <= 0) {
    $error = "O valor deve ser maior que zero.";
  }
  if ($saldoTemp < $valor) {
    $error = "Você não tem saldo suficiente para realizar este saque.";
  }
  if (!validaCPF($cpf)){
    $error = "CPF inválido.";
  }
  else {
    $saldoTemp = $saldoTemp - $valor;
    $saldo = $saldoTemp;
    $token = base64_encode($config["client_id"] . ":" . $config["client_secret"]);

    $responseAuth = make_request(
        url: "https://api.bspay.co/v1/authentication?grant_type=client_credentials",
        method: "POST",
        propHeaders: [
            'Content-Type: x-www-form-urlencoded',
            "Authorization: Basic " . $token,
        ],
        payload: []
    );

    $responseAuth = json_decode($responseAuth, true);
    $uuid = generateuuid();
    $response = make_request("https://api.bspay.co/v1/payment/pix/send", [
      "amount" => $valor,
      "payerQuestion" => "Saque de " . $_SESSION['email'],
      "external_id" => $uuid,
      "payer" => [
        "key" => $cpf,
        "keyType" => "CPF",
        "document" => $cpf
      ]
    ],
    "POST",
    [
      "Content-Type: application/json",
      "Authorization: Bearer {$responseAuth["access_token"]}" 
    ]);

    $response = json_decode($response, true);

      if (isset($response["status"]) && $response["status"] == "error") {
       $error = $response["message"];
     }
     else  {
      $sql = "UPDATE appconfig SET saldo = '$saldo' WHERE email = '$email'";
      $conn->query($sql);

      $sql = sprintf(
        "INSERT INTO saques (email, externalreference, valor, status) VALUES ('%s', '%s', '%s', '%s')",
        $email,
        $valor,
        $uuid,
        'success',
      );

      $conn->query($sql);
      $success = "Saque realizado com sucesso. Aguarde o processamento.";
    }
    
  }

  // var_dump($valor,
  // $saldoTemp);exit;
}

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT nome_unico, nome_um, nome_dois FROM app";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();


    $nomeUnico = $row['nome_unico'];
    $nomeUm = $row['nome_um'];
    $nomeDois = $row['nome_dois'];

} else {
    return false;
}

$conn->close();
?>


<!DOCTYPE html>

<html lang="pt-br" class="w-mod-js w-mod-ix wf-spacemono-n4-active wf-spacemono-n7-active wf-active"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>.wf-force-outline-none[tabindex="-1"]:focus{outline:none;}</style>
<meta charset="pt-br">
<title><?= $nomeUnico ?> 🌊 </title>

<meta property="og:image" content="../img/">

<meta content="<?= $nomeUnico ?> 🌊" property="og:title">
<meta name="twitter:site" content="@<?= $nomeUnico ?>">
<meta name="twitter:image" content="../img/">
<meta property="og:type" content="website">

<meta content="width=device-width, initial-scale=1" name="viewport">
<link href="arquivos/page.css" rel="stylesheet" type="text/css">
<script src="arquivos/webfont.js" type="text/javascript"></script>

<script type="text/javascript">
                WebFont.load({
                    google: {
                        families: ["Space Mono:regular,700"]
                    }
                });
            </script>



<script type="text/javascript">
                ! function (o, c) {
                    var n = c.documentElement,
                        t = " w-mod-";
                    n.className += t + "js", ("ontouchstart" in o || o.DocumentTouch && c instanceof DocumentTouch) && (n
                        .className += t + "touch")
                }(window, document);
            </script>
<link rel="apple-touch-icon" sizes="180x180" href="../img/">
<link rel="icon" type="image/png" sizes="32x32" href="../img/">
<link rel="icon" type="image/png" sizes="16x16" href="../img/">



<link rel="icon" type="image/x-icon" href="../img/">

<link rel="stylesheet" href="arquivos/css" media="all">

</head>
<body>
<div>
<div data-collapse="small" data-animation="default" data-duration="400" role="banner" class="navbar w-nav">
<div class="container w-container">
<a href="/painel" aria-current="page" class="brand w-nav-brand" aria-label="home">
<img src="arquivos/l2.png" loading="lazy" height="28" alt="" class="image-6">
<div class="nav-link logo"><?= $nomeUnico ?></div>
</a>
<nav role="navigation" class="nav-menu w-nav-menu">
<a href="../painel/" class="nav-link w-nav-link" style="max-width: 940px;">Jogar</a>
<a href="../saque/" class="nav-link w-nav-link w--current" style="max-width: 940px;">Saque</a>

<a href="../afiliate" class="nav-link w-nav-link" style="max-width: 940px;">Indique e Ganhe</a>

<a href="../logout.php" class="nav-link w-nav-link" style="max-width: 940px;">Sair</a>
<a href="../deposito/" class="button nav w-button">Depositar</a>
</nav>







<style>
  .nav-bar {
      display: none;
      background-color: #333; /* Cor de fundo do menu */
      padding: 20px; /* Espaçamento interno do menu */
      width: 90%; /* Largura total do menu */
    
      position: fixed; /* Fixa o menu na parte superior */
      top: 0;
      left: 0;
      z-index: 1000; /* Garante que o menu está acima de outros elementos */
  }

  .nav-bar a {
      color: white; /* Cor dos links no menu */
      text-decoration: none;
      padding: 10px; /* Espaçamento interno dos itens do menu */
      display: block;
      margin-bottom: 10px; /* Espaçamento entre os itens do menu */
  }

  .nav-bar a.login {
      color: white; /* Cor do texto para o botão Login */
  }
  
  .button.w-button {
  text-align: center;
}

</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js" integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  $(function() {
    $('#withdrawValue').maskMoney({prefix:'€ ', allowNegative: true, thousands:'', decimal:',', affixesStay: false, min: 1, max: 10 });
  })
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
      var menuButton = document.querySelector('.menu-button');
      var navBar = document.querySelector('.nav-bar');

      menuButton.addEventListener('click', function () {
          // Toggle the visibility of the navigation bar
          if (navBar.style.display === 'block') {
              navBar.style.display = 'none';
          } else {
              navBar.style.display = 'block';
          }
      });
  });
</script>



<style>
  .menu-button2{
      border-radius: 15px;
      background-color: #000;
  }
</style>




<div class="w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
<div class="" style="-webkit-user-select: text;">

<a href="../deposito/" class="menu-button2 w-nav-dep nav w-button">DEPOSITAR</a>
</div>
</div>
<div class="menu-button w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
<div class="icon w-icon-nav-menu"></div>
</div>
</div>
<div class="w-nav-overlay" data-wf-ignore="" id="w-nav-overlay-0"></div></div>
<div class="nav-bar">
<a href="../painel/" class="button w-button">
<div>Jogar</div>
</a>
<a href="../saque/" class="button w-button">
<div >Saque</div>
</a>

<a href="../afiliate/" class="button w-button">
<div >Indique & ganhe</div>
</a>
<a href="../logout.php" class="button w-button">
<div >Sair</div>
</a>
<a href="../deposito/" class="button w-button">Depositar</a>
</div>


<section id="hero" class="hero-section dark wf-section">
<div class="minting-container w-container">
<img src="arquivos/with.gif" loading="lazy" width="240" data-w-id="6449f730-ebd9-23f2-b6ad-c6fbce8937f7" alt="Roboto #6340" class="mint-card-image">
<p style="color: red"><?php echo $error;?></p>
<p style="color: green"><?php echo $success;?></p>
<h2>Saque</h2>
<h4>Seu saldo atual é de: €<?php echo number_format($saldo, 2, ',', ''); ?></h4>
<p>Transferencias instantaneas: saques instantâneos com muita praticidade. <br></p>

<form data-name="" id="payment_pix" name="payment_pix" method="post" aria-label="Form">
<input type="hidden" name="acao" value="SACAR"></input/>
<div class="properties">
<h4 class="rarity-heading">Nome do destinatário:</h4>
<div class="rarity-row roboto-type2">
<input type="text" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input" maxlength="256" name="withdrawName" placeholder="Nome do Destinatario" id="withdrawName" required="">
</div>
<h4 class="rarity-heading">IBAN:</h4>
<div class="rarity-row roboto-type2">
<input type="text" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input" maxlength="256" name="withdrawCPF" placeholder="Indique o Código do País, exemplo: PT50..." id="withdrawCPF" required="">
</div>
<h4 class=" rarity-heading">Valor para saque</h4>
<div class="rarity-row roboto-type2">
<input  data-name="Valor de saque" min="0.00" max="10.00" name="withdrawValue" id="withdrawValue" placeholder="Sem pontos, virgulas ou centavos" step="1" max="<?php echo number_format($saldo, 2, '.', ''); ?>" required="" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input">
</div>
</div>
<div class="">


<input type="submit" value="Sacar via Transferencia" id="pixgenerator" class="primary-button w-button"><br><br>

</div>
</form>








</div>
</section>
<div class="intermission wf-section"></div>
<div id="rarity" class="rarity-section wf-section">
<div class="minting-container w-container">
<img src="arquivos/money-cash.gif" loading="lazy" width="240" alt="Robopet 6340" class="mint-card-image">
<h2>Histórico financeiro</h2>
<p class="paragraph">As retiradas para sua conta bancária são processadas pelo setor financeiro.
<br>
</p>
<div class="properties">
<h3 class="rarity-heading">Saques realizados</h3>
</div>
</div>
</div>
<div class="intermission wf-section"></div>
<div id="about" class="comic-book white wf-section">
<div class="minting-container left w-container">
<div class="w-layout-grid grid-2">
<img src="arquivos/money.png" loading="lazy" width="240" alt="Roboto #6340" class="mint-card-image v2">
<div>
<h2>Indique um amigo e ganhe €</h2>
<h3>Como funciona?</h3>
<p>Convide seus amigos que ainda não estão na plataforma. Você receberá €10 por cada amigo que
se
inscrever e fizer um depósito. Não há limite para quantos amigos você pode convidar. Isso
significa que também não há limite para quanto você pode ganhar!</p>
<h3>Como recebo o dinheiro?</h3>
<p>O saldo é adicionado diretamente ao seu saldo no painel abaixo, com o qual você pode sacar
via
Transferencia.</p>
</div>
</div>
</div>
</div>
<div class="footer-section wf-section">
<div class="domo-text"><?= $nomeUm ?> <br>
</div>
<div class="domo-text purple"><?= $nomeDois ?> <br>
</div>
<div class="follow-test">© Copyright xlk Limited, with registered
  offices at
  Dr. M.L. King
  Boulevard 117, accredited by license GLH-16289876512. </div>
<div class="follow-test">
<a href="#">
<strong class="bold-white-link">Termos de uso</strong>
</a>
</div>
<div class="follow-test">contato@<?= $nomeUnico ?>.net</div>
</div>






<script type="text/javascript">
            $("#withdrawValue").keyup(function (e) {
                var value = $("[name='withdrawValue']").val();

                var final = (value / 100) * 95;

                $('#updatedValue').text('' + final.toFixed(2));
            });
        </script>
        </div>
        <div id="imageDownloaderSidebarContainer">
          <div class="image-downloader-ext-container">
            <div tabindex="-1" class="b-sidebar-outer"><!---->
              <div id="image-downloader-sidebar" tabindex="-1" role="dialog" aria-modal="false" aria-hidden="true"
                class="b-sidebar shadow b-sidebar-right bg-light text-dark" style="width: 500px; display: none;"><!---->
                <div class="b-sidebar-body">
                  <div></div>
                </div><!---->
              </div><!----><!---->
            </div>
          </div>
        </div>
        <div style="visibility: visible;">
          <div></div>
          <div>
            <div
              style="display: flex; flex-direction: column; z-index: 999999; bottom: 88px; position: fixed; right: 16px; direction: ltr; align-items: end; gap: 8px;">
              <div style="display: flex; gap: 8px;"></div>
            </div>
            <style> @-webkit-keyframes ww-1d3e1845-0974-4ce9-92ae-64548dac571e-launcherOnOpen {
          0% {
            -webkit-transform: translateY(0px) rotate(0deg);
                    transform: translateY(0px) rotate(0deg);
          }

          30% {
            -webkit-transform: translateY(-5px) rotate(2deg);
                    transform: translateY(-5px) rotate(2deg);
          }

          60% {
            -webkit-transform: translateY(0px) rotate(0deg);
                    transform: translateY(0px) rotate(0deg);
          }


          90% {
            -webkit-transform: translateY(-1px) rotate(0deg);
                    transform: translateY(-1px) rotate(0deg);

          }

          100% {
            -webkit-transform: translateY(-0px) rotate(0deg);
                    transform: translateY(-0px) rotate(0deg);
          }
        }
        @keyframes ww-1d3e1845-0974-4ce9-92ae-64548dac571e-launcherOnOpen {
          0% {
            -webkit-transform: translateY(0px) rotate(0deg);
                    transform: translateY(0px) rotate(0deg);
          }

          30% {
            -webkit-transform: translateY(-5px) rotate(2deg);
                    transform: translateY(-5px) rotate(2deg);
          }

          60% {
            -webkit-transform: translateY(0px) rotate(0deg);
                    transform: translateY(0px) rotate(0deg);
          }


          90% {
            -webkit-transform: translateY(-1px) rotate(0deg);
                    transform: translateY(-1px) rotate(0deg);

          }

          100% {
            -webkit-transform: translateY(-0px) rotate(0deg);
                    transform: translateY(-0px) rotate(0deg);
          }
        }

        @keyframes ww-1d3e1845-0974-4ce9-92ae-64548dac571e-widgetOnLoad {
          0% {
            opacity: 0;
          }
          100% {
            opacity: 1;
          }
        }

        @-webkit-keyframes ww-1d3e1845-0974-4ce9-92ae-64548dac571e-widgetOnLoad {
          0% {
            opacity: 0;
          }
          100% {
            opacity: 1;
          }
        }
      </style></div>
      </div>
      </body>
      
      </html>