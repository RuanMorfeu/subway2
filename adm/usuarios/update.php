<?php
ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['emailadm'])) {
    header("Location: ../login");
    exit();
}

# if is not a post request, exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

include './../../conectarbanco.php';
$conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

function required($form, $field)
{
    if (!isset($form[$field]) || $form[$field] === null) {
        return "$field é requerido";
    }

    return null;
}

function validate_form($form, $fields)
{
    foreach ($fields as $field) {
        if ($error = required($form, $field)) {
            return $error;
        }
    }

    return null;
}

function get_form()
{
    return array(
        'id' => $_POST['id'],
        'email' => $_POST['email'],
        'telefone' => $_POST['telefone'],
        'saldo' => $_POST['saldo'],
        'linkafiliado' => $_POST['linkafiliado'],
        'plano' => $_POST['plano'],
        'bloc' => isset($_POST['bloc']) && $_POST['bloc'] === 'on' ? 1 : 0,
        'saldo_comissao' => $_POST['saldo_comissao'],
        'cpa' => $_POST['cpa'],
        'cpafake' => $_POST['cpafake'],
        'comissaofake' => $_POST['comissaofake'],
        'dificuldadeJogo_ind' => $_POST['dificuldadeJogo_ind'],
        'xmeta_ind' => floatval($_POST['xmeta_ind']),
        'valorMoeda_ind' => floatval($_POST['valorMoeda_ind']),
        'contademo' => $_POST['containfluenciador'],
    );
}


if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$form = get_form();

$sql = sprintf(
    "UPDATE appconfig SET email='%s', telefone='%s', saldo='%s', linkafiliado='%s', plano='%s', bloc='%s', saldo_comissao='%s', cpa='%s', cpafake='%s', comissaofake='%s', dificuldadeJogo_ind='%s', xmeta_ind='%f', valorMoeda_ind='%f', contademo='%s' WHERE id='%s'",
    $form['email'],
    $form['telefone'],
    $form['saldo'],
    $form['linkafiliado'],
    $form['plano'],
    $form['bloc'],
    $form['saldo_comissao'],
    $form['cpa'],
    $form['cpafake'],
    $form['comissaofake'],
    $form['dificuldadeJogo_ind'],
    floatval($form['xmeta_ind']),
    floatval($form['valorMoeda_ind']),
    $form['contademo'],
    $form['id']
);

try {
    if ($conn->query($sql)) {
        $msg = 'Dados Atualizados com successo';
        http_response_code(200);
    } else {
        $msg = "Erro na atualização dos dados: " . $conn->error;
        http_response_code(400);
    }
} catch (Exception $ex) {
    http_response_code(500);
}

header("Location: ../usuarios");

$conn->close();

