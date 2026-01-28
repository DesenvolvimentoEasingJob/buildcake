<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('Roles', 'Users');

$Roles = new RolesService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $Roles->getRoles($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $Roles->insertRoles($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $Roles->editRoles($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $Roles->deletRoles($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');