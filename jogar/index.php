<?php
    session_start();
    if (!isset($_SESSION['email'])) {
        header('Location: ../');
        exit();
    }


    $usuarioaposta = $_SESSION['email'];

    include '../conectarbanco.php';

    $conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

    if ($conn->connect_error) {
        die('Conexão falhou: ' . $conn->connect_error);
    }

    $queryconsultausuario = "SELECT * FROM appconfig WHERE email = ?";
    $stmt = $conn->prepare($queryconsultausuario);
    $stmt->bind_param("s", $usuarioaposta);
    $stmt->execute();
    $resultadoconsultausuario = $stmt->get_result();
    $rowusuario = $resultadoconsultausuario->fetch_assoc();
    $stmt->close();

    $app = "SELECT * FROM app";
    $appResult = $conn->prepare($app);
    $appResult->execute();
    $appResult = $appResult->get_result();
    $appResult = $appResult->fetch_assoc();

    $influencer = $rowusuario['contademo'];
    $valoraposta = $_GET['aposta'];
    $tokenaposta = $_GET['tokenaposta'];

    if (!$tokenaposta) {
        header('location: /painel');
        exit;
    }

    if (!$valoraposta) {
        header('location: /painel');
        exit;
    }

    if (!is_numeric($valoraposta)) {
        header('location: ');
        exit;
    }

    if ($valoraposta < 1) {
        header('location: ');
        exit;
    }

    if ($rowusuario['xmeta_ind'] != null && $rowusuario['xmeta_ind'] != "" && $rowusuario['xmeta_ind'] != 0) {
        $xmeta = floatval($rowusuario['xmeta_ind']);
    } else {
        $xmeta = floatval($appResult['xmeta']);
    }

    if ($rowusuario['valorMoeda_ind'] != null && $rowusuario['valorMoeda_ind'] != "" && $rowusuario['valorMoeda_ind'] != 0) {
        $tCoinsValue = floatval($rowusuario['valorMoeda_ind']);
    } else {
        $tCoinsValue = floatval($appResult['valor_moeda']);
    }

    if ($rowusuario['dificuldadeJogo_ind'] != null && $rowusuario['dificuldadeJogo_ind'] != "" && $rowusuario['dificuldadeJogo_ind'] != 0 && $rowusuario['dificuldadeJogo_ind'] != "global") {
        $dificuldade = $rowusuario['dificuldadeJogo_ind'];
    } else {
        $dificuldade = $appResult['dificuldade_jogo'];
    }
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <script>
        const xmeta = <?php echo $xmeta; ?>;
        const tCoinsValue = <?php echo $tCoinsValue; ?>;
        const dificuldade = "<?php echo $dificuldade; ?>";
        const aposta = <?php echo json_encode($valoraposta); ?>;
        const tokenaposta = <?php echo json_encode($tokenaposta); ?>;
        const contademo = <?php echo json_encode($influencer); ?>;
    </script>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='apple-mobile-web-app-capable' content='yes'>
    <meta name='apple-mobile-web-app-status-bar-style' content='black'>
    <meta name='viewport' content='height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui, viewport-fit=cover' />
    <link rel='manifest' href='subwaysurfers.webmanifest'>
    <link rel='icon' href='assets/images/app-icon-16.png' type='image/png'>
    <link rel='apple-touch-icon-precomposed' sizes='114x114' href='assets/images/app-icon-114.png'>
    <link rel='apple-touch-icon-precomposed' sizes='72x72' href='assets/images/app-icon-72.png'>
    <link rel='apple-touch-icon-precomposed' sizes='57x57' href='assets/images/app-icon-57.png'>
    <link rel='apple-touch-icon-precomposed' href='assets/images/app-icon-57.png'>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'></script>
    <meta name='robots' content='noindex,nofollow' />
    <title>Subway Surfers Web</title>
    <style>
        body,
        html {
            margin: 0;
            height: 100%;
            background-color: #0b316b;
            overflow: hidden;
            background-image: url('assets/preload/splash.png');
            background-repeat: no-repeat;
            background-position: center;
        }

        #message {
            text-align: center;
            font-size: 8px;
            z-index: 5;
            font-family: 'Verdana', sans-serif;
            color: #fff;
            position: fixed;
            width: 100%;
            z-index: 9999;
        }

        .dot {
            display: inline;
            margin-left: 0.2em;
            margin-right: 0.2em;
            position: relative;
            top: -1em;
            font-size: 3.5em;
            opacity: 0;
            animation: showHideDot 2.5s ease-in-out infinite;
        }

        .dot.one {
            animation-delay: 0.2s;
        }

        .dot.two {
            animation-delay: 0.4s;
        }

        .dot.three {
            animation-delay: 0.6s;
        }

        @keyframes showHideDot {
            0% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }

            60% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        button#sair {
            position: absolute;
            display: none;
            top: 100px;
            left: calc(50% - 95px);
            width: 190px;
            line-height: 32px;
            font-size: 16px;
            font-family: sans-serif;
            text-align: center;
            color: #ffc107;
            background-color: #2e1a07e8;
            box-shadow: 0 0 6px 0 #fff;
            border: .5px solid #120904;
            border-radius: 10px;
            z-index: 100000;
        }
    </style>
</head>

<body>
    <button id='sair'>ENCERRAR APOSTA</button>
    <script>
        window.NOSW = true;
        window.GAME_CONFIG = {
            'pokiSdkDebug': true,
            'leaderboard': 'mockup',
            'bundlesPath': './bundles',
        };
    </script>
    <div id='message'>
        <h1>Carregando</h1>
        <h1 class='dot one'>.</h1>
        <h1 class='dot two'>.</h1>
        <h1 class='dot three'>.</h1>
    </div>
    <script src='js/loading.js'></script>
    <script src='js/boot.js?v=<?php echo time(); ?>'></script>
    
</body>

</html>