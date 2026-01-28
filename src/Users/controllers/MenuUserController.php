<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('MenuUser', 'Users');

$MenuUser = new MenuUserService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $MenuUser->getMenuUser($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $MenuUser->insertMenuUser($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $MenuUser->editMenuUser($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $MenuUser->deletMenuUser($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');