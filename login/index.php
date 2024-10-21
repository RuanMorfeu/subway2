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

// Inicializa as variáveis
$email = $senha = "";
$emailErr = $senhaErr = "";
$errorMessage = "";

// Função para validar os dados do formulário
function validateForm($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar e obter os dados do formulário
    $email = validateForm($_POST["email"]);
    $senha = validateForm($_POST["senha"]);
    

    include './../conectarbanco.php';

    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM appconfig WHERE email = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowusuario = $result->fetch_assoc();
    $pwd_hashed = password_verify($senha, $rowusuario['senha']);


    if ($pwd_hashed == true && $rowusuario['bloc'] !== 'on') {
        $_SESSION["email"] = $email;
        $successMessage = "Login efetuado com sucesso!";
    } else {
        $errorMessage = "Credenciais incorretas. Tente novamente.";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="pt-br" class="w-mod-js wf-spacemono-n4-active wf-spacemono-n7-active wf-active w-mod-ix"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style>.wf-force-outline-none[tabindex="-1"]:focus{outline:none;}</style>
<meta charset="pt-br">
<title><?= $nomeUnico ?> 🌊 </title>
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

<link rel="manifest" href="../">
<link rel="icon" type="image/x-icon" href="../img/logo.png">

<link rel="stylesheet" href="arquivos/css" media="all">


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
<a href="../login" class="nav-link w-nav-link w--current" style="max-width: 940px;">Login</a>
<a href="../cadastrar/" class="button nav w-button">Cadastrar</a>
</nav>





<style>
.w3-panel {
  margin-top: 16px;
  margin-bottom: 16px;
}

.w3-red, .w3-hover-red:hover {
  color: #fff !important;
  background-color: #f44336 !important;
}


.w3-green, .w3-hover-green:hover {
  color: #fff !important;
  background-color: rgb(28, 179, 0) !important;
}

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
<a href="../login/" class="button w-button">
<div>Jogar</div>
</a>
<a href="../login/" class="button w-button">
<div>Login</div>
</a>
<a href="../cadastrar/" class="button w-button">Cadastrar</a>
</div>
<section id="hero" class="hero-section dark wf-section">
<div class="minting-container w-container">
<h2>LOGIN</h2>
<a href="../cadastrar/">
<p>Não possui conta? <a style="color: #b81a06; font-weight: bold;" href="../cadastrar/">Clique Aqui</a> <br>
</p>
</a>











<form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">


  <div class="properties">
  <h4 class="rarity-heading">E-mail</h4>
  <div class="rarity-row roboto-type2">
  <input type="e-mail" class="large-input-field w-input" maxlength="256" name="email" placeholder="seuemail@gmail.com" id="email" required="">
  </div>
  <h4 class="rarity-heading">Senha</h4>
  <div class="rarity-row roboto-type2">
  <input type="password" class="large-input-field w-input" maxlength="256" name="senha" data-name="password" placeholder="Sua senha" id="senha" required="">
  </div><br>
  
  
  
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
  
  <script src="sweetalert.min.js"></script>
  
  
  
  <?php
          if (!empty($errorMessage)) {
              echo '<p class="w3-panel w3-red">' . $errorMessage . '</p>';
          }
          if (!empty($successMessage)) {
              echo '<p class="w3-panel w3-green">' . $successMessage . '</p>';
          }
          ?>
  
  

  <div class="">
  <button class="primary-button w-button">Entrar</button><br><br>
  </div>
  </form>
  
  
  
  
  
  </div>
  </section>
  <script type="text/javascript">
          function myFunction() {
              var x = document.getElementById("senha");
              if (x.type === "password") {
                  x.type = "text";
              } else {
                  x.type = "password";
              }
          }
          </script>
  
  
  
  <script>
          // Ocultar a mensagem de sucesso após 2 segundos e redirecionar
          setTimeout(function() {
              var successMessage = document.querySelector(".w3-panel.w3-green");
              if (successMessage) {
                  successMessage.style.display = "none";
                  window.location.href = "../painel"; // Redirecionar após 2 segundos
              }
          }, 2000);
      </script>


<div class="footer-section wf-section">
<div class="domo-text"><?= $nomeUm ?> <br>
</div>
<div class="domo-text purple"><?= $nomeDois ?> <br>
</div>
<div class="follow-test">© Copyright, with registered
offices at
Dr. M.L. King
Boulevard 117, accredited by license GLH-16289876512. </div>
<div class="follow-test"></div>
<div class="follow-test">contato@<?= $nomeUnico ?>.cloud</div>
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
  
    <style>      @-webkit-keyframes ww-71e31c39-4e87-4264-930a-91d2465581f0-launcherOnOpen {
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
        @keyframes ww-71e31c39-4e87-4264-930a-91d2465581f0-launcherOnOpen {
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

        @keyframes ww-71e31c39-4e87-4264-930a-91d2465581f0-widgetOnLoad {
          0% {
            opacity: 0;
          }
          100% {
            opacity: 1;
          }
        }

        @-webkit-keyframes ww-71e31c39-4e87-4264-930a-91d2465581f0-widgetOnLoad {
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
            '<strong>Cefas</strong> Acabou de Sacar <strong>€ 67,00 </strong>',
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