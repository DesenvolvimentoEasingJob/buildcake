<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('Menu', 'Users');

$Menu = new MenuService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Menu->getMenu($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $Menu->insertMenu($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Menu->editMenu($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Menu->deletMenu($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');