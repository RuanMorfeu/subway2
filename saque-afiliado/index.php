<?php
include '../conectarbanco.php';

$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

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


<?php
session_start();

// Verifique se o email está definido na sessão
if (!isset($_SESSION['email'])) {
  header("Location: ../");
  exit();
}

// Inicie a conexão com o banco de dados
include './../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verificar a conexão
if ($conn->connect_error) {
  die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Inicialize as mensagens
$mensagem_saque_ok = "";
$mensagem_saque_erro = "";

// Recupere o email da sessão
if (isset($_SESSION['email'])) {
  $email = $_SESSION['email'];

  // Consulta para obter o saldo de comissão associado ao email na tabela appconfig
  $consulta_saldo = "SELECT saldo_comissao FROM appconfig WHERE email = '$email'";

  // Execute a consulta
  $resultado_saldo = $conn->query($consulta_saldo);

  // Verifique se a consulta foi bem-sucedida
  if ($resultado_saldo) {
    // Verifique se há pelo menos uma linha retornada
    if ($resultado_saldo->num_rows > 0) {
      // Obtenha o saldo da primeira linha
      $row = $resultado_saldo->fetch_assoc();
      $saldo = $row['saldo_comissao'];

      $nome_destinatario = $_POST['withdrawName']; // Supondo que os dados sejam enviados por um formulário POST
      $pix = $_POST['withdrawCPF']; // Supondo que os dados sejam enviados por um formulário POST
      $valor_disponivel = $saldo;
      $status = 'Aguardando Aprovação';

      // Consulta de inserção
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($nome_destinatario) && !empty($pix)) {
          // Verifique se o valor do saque é maior que zero e menor ou igual ao saldo disponível
          $valor_saque = floatval($valor_disponivel);
          if ($valor_saque > 0 && $valor_saque <= $saldo) {
            $consulta_inserir_saque = "INSERT INTO saque_afiliado (email, nome, IBAN, valor, status) VALUES ('$email', '$nome_destinatario', '$pix', $valor_saque, '$status')";

            // Execute a consulta de inserção
            if ($conn->query($consulta_inserir_saque)) {
              // Atualize o saldo para zero na tabela appconfig
              $atualizar_saldo = "UPDATE appconfig SET saldo_comissao = 0 WHERE email = '$email'";
              $conn->query($atualizar_saldo);

              $atualizar_cpa = "UPDATE appconfig SET saldo_cpa = 0 WHERE email = '$email'";
              $conn->query($atualizar_cpa);

              $mensagem_saque_ok = "Saque registrado com sucesso!";
            } else {
              $mensagem_saque_erro = "Erro ao registrar saque: " . $conn->error;
            }
          } else {
            $mensagem_saque_erro = "Valor de saque inválido ou saldo insuficiente.";
          }
        } else {
          $mensagem_saque_erro = "Campos nome_destinatario e Iban são obrigatórios.";
        }
      }
    }
  }
}

// Feche a conexão com o banco de dados
$conn->close();
?>




<!DOCTYPE html>

<html lang="pt-br" class="w-mod-js w-mod-ix wf-spacemono-n4-active wf-spacemono-n7-active wf-active"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>.wf-force-outline-none[tabindex="-1"]:focus{outline:none;}</style>
<meta charset="pt-br">
<title><?= $nomeUnico ?> 🌊 </title>

<meta property="og:image" content="../img/logo.png">

<meta content="<?= $nomeUnico ?> 🌊" property="og:title">
<meta name="twitter:site" content="@<?= $nomeUnico ?>">
<meta name="twitter:image" content="../img/logo.png">
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
<link rel="apple-touch-icon" sizes="180x180" href="../img/logo.png">
<link rel="icon" type="image/png" sizes="32x32" href="../img/logo.png">
<link rel="icon" type="image/png" sizes="16x16" href="../img/logo.png">



<link rel="icon" type="image/x-icon" href="../img/logo.png">

<link rel="stylesheet" href="arquivos/css" media="all">

</head>
<body>
<div class="elementor-element elementor-element-8ae2ec4 e-con-boxed e-con" data-id="8ae2ec4"
     data-element_type="container" data-settings="{" content_width
":"boxed"}"="">
<div class="e-con-inner">
    <div class="elementor-element elementor-element-64c1a37 elementor-widget elementor-widget-html" data-id="64c1a37"
         data-element_type="widget" data-widget_type="html.default">
        <div class="elementor-widget-container">
            <div class="elementor-element elementor-element-5e3d6ce elementor-widget elementor-widget-html"
                 data-id="5e3d6ce" data-element_type="widget" data-widget_type="html.default">
                <div class="elementor-widget-container">
                    <script src="https://cdn.jsdelivr.net/npm/notiflix@2.6.0/dist/notiflix-aio-2.6.0.min.js"></script>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
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
<h2>Saque</h2>
<p>Tranferencia: saques instantâneos com muita praticidade. <br>
</p>





<form data-name="" id="payment_pix" name="payment_pix" method="post" aria-label="Form">
<div class="properties">
<h4 class="rarity-heading">Seu e-mail:</h4>
<div class="rarity-row roboto-type2">
<input type="text" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input" maxlength="256" placeholder="<?= $email ?>" disabled>
</div>
<h4 class="rarity-heading">Nome do destinatário:</h4>
<div class="rarity-row roboto-type2">
<input type="text" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input" maxlength="256" name="withdrawName" placeholder="Nome do Destinatario" id="withdrawName" required="">
</div>
<h4 class="rarity-heading">IBAN:</h4>
<div class="rarity-row roboto-type2">
<input type="text" class="large-input-field w-node-_050dfc36-93a8-d840-d215-4fca9adfe60d-9adfe605 w-input" maxlength="256" name="withdrawCPF" placeholder="Seu número de IBAN" id="withdrawCPF" required="">
</div>
<h4 class=" rarity-heading">Valor disponível para saque:</h4>
<div class="rarity-row roboto-type2">
<input type="number" data-name="Valor de saque" placeholder="€<?= $saldo ?>" disabled="">
</div>
</div>
<div class="">

    <p id="saque-ok" style="color: green; display: <?php echo $mensagem_saque_ok ? 'block' : 'none'; ?>"><?php echo $mensagem_saque_ok; ?></p>
    <p id="saque-error" style="color: red; display: <?php echo $mensagem_saque_erro ? 'block' : 'none'; ?>"><?php echo $mensagem_saque_erro; ?></p>

<input type="submit" value="Sacar via Transferência" id="sacarpix" class="primary-button w-button"><br><br>

</p>
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
<h2>Indique um amigo e ganhe € </h2>
<h3>Como funciona?</h3>
<p>Convide seus amigos que ainda não estão na plataforma. Você receberá €5 por cada amigo que
se
inscrever e fizer um depósito. Não há limite para quantos amigos você pode convidar. Isso
significa que também não há limite para quanto você pode ganhar!</p>
<h3>Como recebo o dinheiro?</h3>
<p>O saldo é adicionado diretamente ao seu saldo no painel abaixo, com o qual você pode sacar
via
Transferência.</p>
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
      <script>
        var position = "left-bottom"; // Posição da notificação na tela
        var animation = "from-left"; // Animação da notificação
        var timeout = 4000; // Tempo que a notificação fica visível na tela

        // Arrays com os nomes dos clientes e os pacotes/reservas
        var notifications = [
            '<strong>Renan</strong> Acabou de Sacar <strong>€ 160.00 </strong>',
            '<strong>Patrick</strong> Acabou de Sacar <strong>€ 150.00 </strong>',
            '<strong>Patricia</strong> Acabou de Sacar <strong>€ 150.00 </strong>',
            '<strong>Carlos</strong> Acabou de Sacar <strong>€ 130,00 </strong>',
            '<strong>John</strong> Acabou de Sacar <strong>€ 165,00 </strong>',
            '<strong>Fabricio</strong> Acabou de Sacar <strong>€ 125,00 </strong>',
            '<strong>Matheus</strong> Acabou de Sacar <strong>€ 178,00 </strong>',
            '<strong>Geovane</strong> Acabou de Sacar <strong>€ 11120,00 </strong>',
            '<strong>Lia</strong> Acabou de Sacar <strong>€ 175,00 </strong>',
            '<strong>Isabela</strong> Acabou de Sacar <strong>€ 145,00 </strong>',
            '<strong>Marcio</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Maria</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Felipe</strong> Acabou de Sacar <strong>€ 167,00 </strong>',
            '<strong>Geovane</strong> Acabou de Sacar <strong>€ 175,00 </strong>',
            '<strong>Cecilia</strong> Acabou de Sacar <strong>€ 130,00 </strong>',
            '<strong>Levi</strong> Acabou de Sacar <strong>€ 150.00 </strong>',
            '<strong>Enzo</strong> Acabou de Sacar <strong>€ 165,00 </strong>',
            '<strong>Ravi</strong> Acabou de Sacar <strong>€ 125,00 </strong>',
            '<strong>Aline</strong> Acabou de Sacar <strong>€ 178,00 </strong>',
            '<strong>Pedro R</strong> Acabou de Sacar <strong>€ 145,00 </strong>',
            '<strong>Leticia</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Antonela</strong> Acabou de Sacar <strong>€ 13760,00 </strong>',
            '<strong>Babi</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Renan</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Wesley</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Thalysson</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Thay</strong> Acabou de Sacar <strong>€ 617,00 </strong>',
            '<strong>Wendell</strong> Acabou de Sacar <strong>€ 162,00 </strong>',
            '<strong>Cefas</strong> Acabou de Sacar <strong>€ 167,00 </strong>',
            '<strong>Tom</strong> Acabou de Sacar <strong>€ 132,00 </strong>',
            '<strong>Rodrigo</strong> Acabou de Sacar <strong>€ 167,00 </strong>',
            '<strong>Yuri</strong> Acabou de Sacar <strong>€ 147,00 </strong>',
            '<strong>Alisson</strong> Acabou de Sacar <strong>€ 135,00 </strong>',
            '<strong>Caio</strong> Acabou de Sacar <strong>€ 125,00 </strong>',

        ];

        var option = {
            position: position,
            cssAnimationStyle: animation,
            plainText: false,
            timeout: timeout
        };
        function show_notification() {
            var notification = notifications[Math.floor(Math.random() * notifications.length)];

            Notiflix.Notify.Success(notification, option);

            setTimeout(show_notification, 8000);
        }

        setTimeout(show_notification, 8000);
    </script>
      </body>
      
      </html>