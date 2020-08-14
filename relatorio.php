<?php
//Constantes
include_once 'inc/XLSXWriter.php';
define('USER', fopen("usuarioFake.json", "r")); //Arquivo de entrada
define('INFO', fopen("transacoesFake.json", "r")); //Arquivo de entrada
define('SEPARATOR', ';'); //Separador de CSV

//Dicionários
$dictPessoa = [
    'nome' => 'NOME_COMPLETO',
    'cpf' => 'CPF',
    'data_nasc' => 'DATA_NASCIMENTO',
    'endereco' => 'ENDERECO',
    'bairro' => 'BAIRRO',
    'cep' => 'CEP',
    'cidade' => 'CIDADE',
    'email' => 'EMAIL',
    'ddd' => 'DDD',
    'telefone' => 'TELEFONE',
    'estado_civil' => 'ESTADO_CIVIL',
    'profissao' => 'PROFISSAO'
];
$tamanhosPessoa = [
    'NOME_COMPLETO' => 80,
    'CPF' => 11,
    'DATA_NASCIMENTO' => 8,
    'ENDERECO' => 80,
    'BAIRRO' => 30,
    'CEP' => 8,
    'CIDADE' => 40,
    'EMAIL' => 80,
    'DDD' => 02,
    'TELEFONE' => 9,
    'ESTADO_CIVIL' => 10,
    'PROFISSAO' => 40
];
$dictEmpresa = [
    'nome' => 'NOME_EMPRESA',
    'cnpj' => 'CNPJ',
    'administrador' => 'ADMINISTRADOR',
    'cpf_admin' => 'CPF',
    'endereco' => 'ENDERECO',
    'bairro' => 'BAIRRO',
    'cep' => 'CEP',
    'cidade' => 'CIDADE',
    'email' => 'EMAIL',
    'ddd' => 'DDD',
    'telefone' => 'TELEFONE',
    'estado_civil_admin' => 'ESTADO_CIVIL',
    'profissao_admin' => 'PROFISSAO'
];
$tamanhosEmpresa = [
    'NOME_EMPRESA' => 80,
    'CNPJ' => 14,
    'ADMINISTRADOR' => 80,
    'CPF' => 11,
    'ENDERECO' => 80,
    'BAIRRO' => 30,
    'CEP' => 8,
    'CIDADE' => 40,
    'EMAIL' => 80,
    'DDD' => 2,
    'TELEFONE' => 9,
    'ESTADO_CIVIL' => 10,
    'PROFISSAO' => 40
];
$dictCabecalho = [
    'cidade' => 'CIDADE',
    'contrato' => 'CONTRATO',
    'cliente' => 'CLIENTE',
    'cpf' => 'CPF',
    'fornecedor' => 'FORNECEDOR',
    'cnpj' => 'CNPJ',
    'banco' => 'BANCO',
    'agencia' => 'AGENCIA',
    'conta' => 'CONTA',
    'valor' => 'VALOR',
    'data_pgto' => 'DATA PGTO',
];
$tamanhosInfoBancaria = [
    'CIDADE' => 40,
    'CONTRATO' => 8,
    'CLIENTE' => 8,
    'CPF' => 11,
    'FORNECEDOR' => 60,
    'CNPJ' => 18,
    'BANCO' => 5,
    'AGENCIA' => 5,
    'CONTA' => 15,
    'VALOR' => 19,
    'DATA PGTO' => 10,
];

// Pega Input (placeholder)
$agente = json_decode(stream_get_contents(USER), true);
$info_bancaria = json_decode(stream_get_contents(INFO), true);

//Operações '/'
geraCSV($info_bancaria);
geraXLS($agente);

//Gera XLS com base na array de entrada
function geraXLS($agente)
{
    $data = formataAgenteXls($agente);
    $xlsx = new XLSXWriter();
    $xlsx->writeSheet(array(
        array_keys($data),
        $data
    ));
    $xlsx->writeToFile('teste.xlsx');
}

//Gera CSV com base na array de entrada
function geraCSV($info_bancaria)
{
    global $dictCabecalho;
    $data = formataInfoBancariaCsv($info_bancaria);
    $fp = fopen('teste.csv', 'w');
    foreach ($dictCabecalho as $key => $value) {
        fwrite($fp, $value);
        if ($key != key(array_slice($dictCabecalho, -1, 1, true))) {
            fwrite($fp, SEPARATOR);
        }
    }
    fwrite($fp, PHP_EOL);
    foreach($data as $value){
        fwrite($fp, implode(SEPARATOR, $value));
        fwrite($fp, PHP_EOL);
    }
    fclose($fp);
}

//Valida e formata entrada para formato XLS GoiásFomento
function formataAgenteXls($agente)
{
    global $dictPessoa;
    global $dictEmpresa;
    global $tamanhosPessoa;
    global $tamanhosEmpresa;
    $data = [];
    if (array_key_exists('cpf', $agente)) {
        foreach ($dictPessoa as $key => $value) {
            if (array_key_exists($key, $agente)) {
                if ($value == 'CPF' || $value == 'CEP' || $value == 'TELEFONE') {
                    $data[$value] = mb_substr(str_replace(['.', '-', '(', ')', '+', ' '], '', $agente[$key]), 0, $tamanhosPessoa[$value]);
                } else if ($value == 'DATA_NASCIMENTO') {
                    $data[$value] = mb_substr((new DateTime(strtr($agente[$key], '/', '-')))->format('Ymd'), 0, $tamanhosPessoa[$value]);
                } else {
                    $data[$value] = mb_substr($agente[$key], 0, $tamanhosPessoa[$value]);
                }
            } else {
                return 'formatoInvalido';
            }
        }
    } else if (array_key_exists('cnpj', $agente)) {
        foreach (array_keys($dictEmpresa) as $key => $value) {
            if (array_key_exists($key, $agente)) {
                if ($value == 'CNPJ' || $value == 'CEP' || $value == 'TELEFONE') {
                    $data[$value] = mb_substr(str_replace(['.', '-', '(', ')', '+', ' '], '', $agente[$key]), 0, $tamanhosEmpresa[$value]);
                } else {
                    $data[$value] = mb_substr($agente[$key], 0, $tamanhosEmpresa[$value]);
                }
            } else {
                return 'formatoInvalido';
            }
        }
    }
    return $data;
}

//Valida e formata entrada para formato XLS GoiásFomento
function formataInfoBancariaCsv($info_bancaria)
{
    global $dictCabecalho;
    global $tamanhosInfoBancaria;
    $data = [];
    foreach ($info_bancaria as $index=>$info) {
        foreach ($dictCabecalho as $key => $value) {
            if (array_key_exists($key, $info)) {
                if ($value == 'CONTRATO') {
                    $data[$index][$value] = mb_substr((new DateTime(strtr($info[$key], '/', '-')))->format('dmY'), 0, $tamanhosInfoBancaria[$value]);
                } else if ($value == 'DATA PGTO') {
                    $data[$index][$value] = mb_substr((new DateTime(strtr($info[$key], '/', '-')))->format('d.m.Y'), 0, $tamanhosInfoBancaria[$value]);
                } else if ($value == 'VALOR') {
                    $data[$index][$value] = mb_substr(strtr(number_format((float) $info[$key], 2, '.', ''), '.', ','), 0, $tamanhosInfoBancaria[$value]);
                } else {
                    $data[$index][$value] = mb_substr($info[$key], 0, $tamanhosInfoBancaria[$value]);
                }
            } else {
                return 'formatoInvalido';
            }
        }
    }
    return $data;
}
