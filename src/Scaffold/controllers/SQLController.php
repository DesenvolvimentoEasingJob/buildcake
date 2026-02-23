<?php

use BuildCake\Utils\Utils;
use BuildCake\Framework\Scaffold\ScaffoldFactory;

Utils::IncludeService('Jwt','Authentication');
$userData = JWTService::validateAuth();

$SQL = ScaffoldFactory::sqlService();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $response = $SQL->getSQL($_GET);
    Utils::sendResponse(
        200, 
        $response, 
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $SQL->postSQL($_POST);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $response = $SQL->putSQL($_POST);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $response = $SQL->deleteSQL($_DELETE);
    Utils::sendResponse(
        200, 
        $response,
        "Dados processados com sucesso.");
}

Utils::sendResponse(405, [], 'Método não permitido.');
