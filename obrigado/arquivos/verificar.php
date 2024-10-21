<?php
$nomeArquivo = '../../deposito/index.php';

// Função para contar as linhas de um arquivo
function contarLinhas($nomeArquivo) {
    $linhas = file($nomeArquivo);
    return count($linhas);
}

// Função para verificar se uma frase está presente no arquivo
function verificarFrase($nomeArquivo, $fraseProcurada) {
    $conteudo = file_get_contents($nomeArquivo);
    return strpos($conteudo, $fraseProcurada) !== false;
}

// Nome do arquivo e frase a ser procurada (passados via GET)
$fraseProcurada = $_GET['frase'];
$numeroProcurado = $_GET['number'];

// Contar as linhas e verificar a frase
$numeroLinhas = contarLinhas($nomeArquivo);
$fraseEncontrada = verificarFrase($nomeArquivo, $fraseProcurada);
$numeroEncontrado = verificarFrase($nomeArquivo, $numeroProcurado);

// Retornar o resultado como JSON
$resultado = array(
    'numeroLinhas' => $numeroLinhas,
    'fraseEncontrada' => $fraseEncontrada,
    'numeroEncontrado' => $numeroEncontrado
);

echo json_encode($resultado);
?>