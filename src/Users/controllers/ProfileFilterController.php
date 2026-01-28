<?php

use BuildCake\Utils\Utils;

Utils::IncludeService('Jwt','Authentication');

$userData = JWTService::validateAuth();  // Valida o token JWT

Utils::IncludeService('ProfileFilter', 'Users');

$ProfileFilter = new ProfileFilterService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $ProfileFilter->getProfileFilter($_GET);
    Utils::sendResponse(
       200, 
       $response, 
       "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $ProfileFilter->insertProfileFilter($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
    Utils::sendResponse( 200,$data,"Dados inseridos com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $ProfileFilter->editProfileFilter($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}


if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $ProfileFilter->deletProfileFilter($_POST);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');