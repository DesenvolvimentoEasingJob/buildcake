<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('Status', 'Users');

$Status = new StatusService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Status->getStatus($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $Status->insertStatus($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Status->editStatus($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Status->deletStatus($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');