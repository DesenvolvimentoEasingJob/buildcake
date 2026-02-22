<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('DataApi', 'DataApi');

$DataApi = new DataApiService();
if(isset($_GET['table'])){
    $DataApi->table = $_GET['table'];
}else{
    Utils::sendResponse(404, [], "Nenhum(a) {$this->table} informado, parametro table é obrigatório");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $DataApi->getDataApi($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $DataApi->insertDataApi($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $DataApi->editDataApi($_POST);
    Utils::sendResponse(
        200, 
        $_POST, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $DataApi->deletDataApi($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');