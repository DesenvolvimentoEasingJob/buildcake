<?php
use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');
Utils::IncludeService('Api', 'Applications');

$userData = JWTService::validateAuth(); 

$api = new ApiService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $api->getApi($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $api->postApi($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $api->putApi($_PUT);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $api->deleteApi($_DELETE);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');