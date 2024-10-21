


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
$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$baseUrl .= "://".$_SERVER['HTTP_HOST'];


$staticPart = '/cadastrar/?aff=';

$callbackUrl = $baseUrl . $staticPart;



echo '<script>';
echo 'console.log("Callback URL:", ' . json_encode($callbackUrl) . ');'; // Adicione esta linha para depurar
echo 'var callbackUrl = ' . json_encode($callbackUrl) . ';';
echo '</script>';
?>



<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

session_start();

// Função para validar os dados do formulário
function validateForm($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

include './../conectarbanco.php';

$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

// Verifica se houve algum erro na conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

function getParamFromUrl($url, $paramName){
    parse_str(parse_url($url, PHP_URL_QUERY), $op);
    return array_key_exists($paramName, $op) ? $op[$paramName] : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar e obter os dados do formulário
    $email = validateForm($_POST["email"]);
    $senha = validateForm($_POST["senha"]);
    $telefone = validateForm($_POST["telefone_confirmation"]);
    $leadAff = isset($_POST['lead_aff']) ? validateForm($_POST['lead_aff']) : '';
    $pwd_hashed = password_hash($senha, PASSWORD_DEFAULT);

    // Verificar se o e-mail já existe
    if (emailExists($email, $conn)) {
        $errorMessage = "Já existe uma conta com esse e-mail.";
    } else {
        // Obter o próximo ID disponível
        $getNextIdQuery = "SELECT MAX(id) AS max_id FROM appconfig";
        $nextIdResult = $conn->query($getNextIdQuery);
        $nextIdRow = $nextIdResult->fetch_assoc();
        $nextId = $nextIdRow['max_id'] + 1;

        // Verificar se o ID já existe e, se existir, obter o próximo ID disponível
        while (idExists($nextId, $conn)) {
            $nextId++;
        }

        $saldo = 0;
        $plano = 20; // Valor fixo para a coluna plano
        $saldo_comissao = 0; // Valor fixo para a coluna saldo_comissao
        $cpa= 0; // Valor fixo para o cpa unico
        

        // Construir o link de afiliado
        $linkAfiliado = $callbackUrl . $nextId;

        // Obter a data e hora atual no fuso horário de São Paulo
        $dataCadastro = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $dataCadastroFormatada = $dataCadastro->format('d-m-Y H:i');
        
        $afiliado = isset($_GET['aff']) ? $_GET['aff'] : '';
        $contademo = "nao";
        
        // Inserir dados no banco de dados
        $insertQuery = "INSERT INTO appconfig (id,cpa, email, senha, telefone, saldo, lead_aff, linkafiliado, indicados, plano, saldo_comissao, data_cadastro, afiliado, contademo) 
                        VALUES (?,0, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssissiisss", $nextId, $email, $pwd_hashed, $telefone, $saldo, $leadAff, $linkAfiliado, $plano, $saldo_comissao, $dataCadastroFormatada, $afiliado, $contademo);

        if ($stmt->execute()) {
            // Definir o email como uma variável de sessão
            $_SESSION['email'] = $email;

            // Redirecionar para a página com o número na URL
            header("Location: /painel");
            exit();
        } else {
            $errorMessage = "Erro ao inserir dados na tabela 'appconfig': " . $stmt->error;
        }

        $stmt->close();
        $nextIdResult->close();
    }
}



// Função para verificar se um ID já existe na tabela
function idExists($id, $conn) {
    $checkIdQuery = "SELECT id FROM appconfig WHERE id = ?";
    $checkIdStmt = $conn->prepare($checkIdQuery);
    $checkIdStmt->bind_param("i", $id);
    $checkIdStmt->execute();
    $checkIdStmt->store_result();
    $exists = $checkIdStmt->num_rows > 0;
    $checkIdStmt->close();
    return $exists;
}

// Função para verificar se um e-mail já existe na tabela
function emailExists($email, $conn) {
    $checkEmailQuery = "SELECT email FROM appconfig WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();
    $exists = $checkEmailStmt->num_rows > 0;
    $checkEmailStmt->close();
    return $exists;
}
$conn->close();
?>



<!DOCTYPE html>

<html lang="pt-br" class="w-mod-js wf-spacemono-n4-active wf-spacemono-n7-active wf-active w-mod-ix"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>.wf-force-outline-none[tabindex="-1"]:focus{outline:none;}</style>
<meta charset="pt-br">
<title><?= $nomeUnico ?> 🌊</title>
<meta property="og:image" content="../img/logo.png">


<meta content="<?= $nomeUnico ?> 🌊" property="og:title">

<meta name="twitter:image" content="../img/logo.png">
<meta content="<?= $nomeUnico ?> 🌊" property="twitter:title">

<meta property="og:type" content="website">
<meta content="summary_large_image" name="twitter:card">
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




<link rel="apple-touch-icon" sizes="180x180" href="../img/logo.png">
<link rel="icon" type="image/png" sizes="32x32" href="../img/logo.png">
<link rel="icon" type="image/png" sizes="16x16" href="../img/logo.png">


<link rel="icon" type="image/x-icon" href="../img/logo.png">

<?php 
    include '../pixels.php';
?>


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



<a href="../" aria-current="page" class="brand w-nav-brand" aria-label="home">
<img src="arquivos/l2.png" loading="lazy" height="28" alt="" class="image-6">

<div class="nav-link logo"><?= $nomeUnico ?></div>
</a>
<nav role="navigation" class="nav-menu w-nav-menu">
<a href="../login/" class="nav-link w-nav-link" style="max-width: 940px;">Jogar</a>
<a href="../login/" class="nav-link w-nav-link" style="max-width: 940px;">Login</a>
<a href="../cadastrar/" class="button nav w-button w--current">Cadastrar</a>
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









<div class="w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
</div>
<div class="menu-button w-nav-button" style="-webkit-user-select: text;" aria-label="menu" role="button" tabindex="0" aria-controls="w-nav-overlay-0" aria-haspopup="menu" aria-expanded="false">
<div class="icon w-icon-nav-menu"></div>
</div>
</div>
<div class="w-nav-overlay" data-wf-ignore="" id="w-nav-overlay-0"></div></div>
<div class="nav-bar">
<a href="../login/" class="button w-button w--current">
<div>Jogar</div>
</a>
<a href="../login/" class="button w-button w--current">
<div >Login</div>
</a>
<a href="../cadastrar/" class="button w-button w--current">Cadastrar</a>
</div>
<section id="hero" class="hero-section dark wf-section">
<div class="minting-container w-container">




<img src="arquivos/Kcykfsq.png" loading="lazy" width="240" data-w-id="6449f730-ebd9-23f2-b6ad-c6fbce8937f7" alt="Roboto #6340" class="mint-card-image">
<h2>CADASTRO</h2>
<p>É rapidinho, menos de 1 minuto. <br>Vai perder a oportunidade de faturar com o jogo do surfista?
<br>
</p>



<?php
// Exibir a notificação de sucesso ou erro
if (!empty($errorMessage)) {
    echo '<div class="notification-container error-message">' . $errorMessage . '</div>';
} elseif (!empty($successMessage)) {
    echo '<div class="notification-container success-message">' . $successMessage . '</div>';
}
?>



<form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
  



  <div class="properties">
  <h4 class="rarity-heading">E-mail</h4>
  <div class="rarity-row roboto-type2">
  <input type="e-mail" class="large-input-field w-input" maxlength="256" name="email" placeholder="seuemail@gmail.com" id="email" required>
  </div>
  <h4 class="rarity-heading">Telefone</h4>
  <div class="rarity-row roboto-type2">
      <input type="tel" class="large-input-field w-input" maxlength="20" name="telefone_confirmation" placeholder="Seu telefone" id="telefone_confirmation" required>
  </div>
  <h4 class="rarity-heading">Senha</h4>
  <div class="rarity-row roboto-type2">
  <input type="password" class="large-input-field w-input" maxlength="256" name="senha" data-name="password" placeholder="Uma senha segura" id="senha" required>
  </div>
  <h4 class="rarity-heading">Confirme sua Senha</h4>
  <div class="rarity-row roboto-type2">
  <input type="password" class="large-input-field w-input" maxlength="256" name="password_confirmation" data-name="password" placeholder="Confirme sua senha" id="myInput" required>
  
   <input type="hidden" name="lead_aff" id="lead_aff" value="">
  </div>
  <br>
  
  
  
  
  
      <input type="checkbox" onclick="mostrarSenha()"> Mostrar senha
  </div>
  
  
  <script>
      function mostrarSenha() {
          var senhaInput = document.getElementById('senha');
          if (senhaInput.type === 'password') {
              senhaInput.type = 'text';
          } else {
              senhaInput.type = 'password';
          }
      }
  </script>
  
  
  <script>
      document.addEventListener('DOMContentLoaded', function () {
          // Obtenha os parâmetros da URL
          const urlParams = new URLSearchParams(window.location.search);
          const leadAff = urlParams.get('aff');
  
          // Atualize o valor do campo oculto 'lead_aff'
          document.getElementById('lead_aff').value = leadAff;
      });
  
  
  </script>
  
  
  
  <div class="">
  <button type="submit" class="primary-button w-button">
  <i class="fa fa-check fa-fw"></i>
  Criar Conta
  </button><br>
  
  
  <p class="medium-paragraph _3-2vw-margin">Ao registrar você concorda com os
  
  
  
  
  
  <a href=>termos de serviço</a> e que possui pelo menos 18 anos.
  </p>
  </div>
  </form>
  </div>
  </section>
<div class="intermission wf-section"></div>
<div id="rarity" class="rarity-section wf-section">
<div class="minting-container left w-container">
<div class="w-layout-grid grid-2">
<div>
<h2>💸 Tudo via MBway &amp; na hora. 🔥</h2>
<p>Seu dinheiro cai na hora, sem burocracia e sem taxas.</p>
</div>
</div>
</div>
</div>

<script>

  document.addEventListener('DOMContentLoaded', function () {
      var notificationContainer = document.querySelector('.notification-container');
      var loadingGif = document.querySelector('carregando.gif');
  
      // Exibir notificação após o envio do formulário
      <?php if (!empty($successMessage) || !empty($errorMessage)) { ?>
      notificationContainer.style.display = 'block';
      <?php } ?>
  
 
      <?php if (empty($successMessage) && empty($errorMessage)) { ?>
      loadingGif.style.display = 'block';
      setTimeout(function () {
          loadingGif.style.display = 'none';
          notificationContainer.style.display = 'block';
      }, 2000); 
      <?php } ?>
  });
  </script>

<div class="footer-section wf-section">
<div class="domo-text"><?= $nomeUm ?> <br>
</div>
<div class="domo-text purple"><?= $nomeDois ?> <br>
</div>
<div class="follow-test">© Copyright </div>
<div class="follow-test"></div>
<div class="follow-test">contato@<?= $nomeUnico ?>.com</div>
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

    <style>    @-webkit-keyframes ww-51fbc3b8-5830-4bee-ad15-8955338512d0-launcherOnOpen {
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
        @keyframes ww-51fbc3b8-5830-4bee-ad15-8955338512d0-launcherOnOpen {
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

        @keyframes ww-51fbc3b8-5830-4bee-ad15-8955338512d0-widgetOnLoad {
          0% {
            opacity: 0;
          }
          100% {
            opacity: 1;
          }
        }

        @-webkit-keyframes ww-51fbc3b8-5830-4bee-ad15-8955338512d0-widgetOnLoad {
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
            '<strong>Geovane</strong> Acabou de Sacar <strong>€ 120,00 </strong>',
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
